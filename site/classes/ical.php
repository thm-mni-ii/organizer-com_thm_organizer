<?php
// Wenn die Anfragen nicht durch Ajax von MySched kommt
if ( isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) ) {
	if ( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] != 'XMLHttpRequest' )
		die( 'Permission Denied!' );
} else
	die( 'Permission Denied!' );

require_once( dirname( __FILE__ ) . "/abstrakterBauer.php" );
require_once( dirname( __FILE__ ) . "/iCalcreator.class.php" );

class ICALBauer extends abstrakterBauer
{
	private $JDA = null;
	private $cfg = null;

	function __construct($JDA, $cfg)
	{
		$this->JDA = $JDA;
		$this->cfg = $cfg;
	}

	public function erstelleStundenplan( $arr, $username, $title )
	{
		$semesterstart = $arr[ count( $arr ) - 1 ]->sdate;
		$semesterend   = $arr[ count( $arr ) - 1 ]->edate;

		unset( $arr[ count( $arr ) - 1 ] );

		if ( $title == "Mein Stundenplan" && $username != "" ) {
			$title = $username . " - " . $title;
		}

		$v = new vcalendar();
		$v->setConfig( 'unique_id', "MySched" );
		$v->setConfig( "lang", "de" );
		$v->setProperty( "x-wr-calname", $title );
		$v->setProperty( "X-WR-CALDESC", "Calendar Description" );
		$v->setProperty( "X-WR-TIMEZONE", "Europe/Berlin" );
		$v->setProperty( "PRODID", "-//212.201.14.161//NONSGML iCalcreator 2.6//" );
		$v->setProperty( "VERSION", "2.0" );
		$v->setProperty( "METHOD", "PUBLISH" );

		$t = new vtimezone();
		$t->setProperty( "TZID", "Europe/Berlin" );

		$ts = new vtimezone( 'standard' );
		$ts->setProperty( "DTSTART", 1601, 1, 1, 0, 0, 0 );
		$ts->setProperty( "TZOFFSETFROM", "+0100" );
		$ts->setProperty( "TZOFFSETTO", "+0100" );
		$ts->setProperty( "TZNAME", "Standard Time" );

		$t->setComponent( $ts );
		$v->setComponent( $t );

		$query = "SELECT startdate, enddate, starttime, endtime FROM " . $this->cfg[ 'jdb_table_events' ] . " WHERE ecatid = " . $this->cfg[ 'vacation_id' ];
		$res   = $this->JDA->query( $query );

		if ( is_array( $res ) ) {
			if ( count( $res ) > 1 ) {
				foreach ( $res as $holi ) {
					if ( $holi->enddate == "0000-00-00" || $holi->enddate == null )
						$holi->enddate = $holi->startdate;
				}

				function sortfunc( $a, $b )
				{
					if ( $a->startdate == $b->startdate ) {
						return 0;
					}
					return ( $a->startdate > $b->startdate ) ? +1 : -1;
				}

				usort( $res, "sortfunc" );

				$todelete = array( );

				for ( $i = 0; $i < count( $res ); $i++ ) {
					if ( $res[ $i ]->startdate == $res[ $i ]->enddate ) {
						for ( $y = 0; $y < $i; $y++ ) {
							if ( $res[ $y ]->startdate <= $res[ $i ]->startdate && $res[ $y ]->enddate >= $res[ $i ]->startdate ) {
								$todelete[ ] = $i;
								break;
							}
						}
					}
				}

				foreach ( $todelete as $td ) {
					unset( $res[ $td ] );
				}

				$res = array_values( $res );

				$ok  = false;
				$num = null;
				while ( $ok === false ) {
					$ok = true;
					for ( $i = 0; $i < count( $res ) - 1; $i++ ) {
						if ( $res[ $i ]->enddate >= $res[ $i + 1 ]->startdate ) {
							$res[ $i ]->enddate = $res[ $i + 1 ]->enddate;
							$ok                 = false;
							$num                = $i + 1;
							break;
						}
					}
					if ( $ok === false ) {
						unset( $res[ $num ] );
						$res = array_values( $res );
					}
				}

				if ( $res[ 0 ]->startdate <= $semesterstart ) {
					$semesterstart = $res[ 0 ]->enddate;
					unset( $res[ 0 ] );
					$res = array_values( $res );
				}

				if ( $res[ count( $res ) - 1 ]->enddate >= $semesterend ) {
					$semesterend = $res[ count( $res ) - 1 ]->startdate;
					unset( $res[ count( $res ) - 1 ] );
					$res = array_values( $res );
				}

				if ( count( $res ) > 0 ) {
					for ( $i = 0; $i <= count( $res ); $i++ ) {
						if ( $i == 0 )
							$v = $this->setEvent( $v, $arr, $semesterstart, $res[ $i ]->startdate );
						elseif ( $i == count( $res ) )
							$v = $this->setEvent( $v, $arr, date( "Y-m-d", strtotime( "+1 day", strtotime( $res[ $i - 1 ]->enddate ) ) ), $semesterend );
						else
							$v = $this->setEvent( $v, $arr, date( "Y-m-d", strtotime( "+1 day", strtotime( $res[ $i - 1 ]->enddate ) ) ), $res[ $i ]->startdate );
					}
				} else {
					$v = $this->setEvent( $v, $arr, $semesterstart, $semesterend );
				}
			} else
				$v = $this->setEvent( $v, $arr, $semesterstart, $semesterend );
		} else
			$v = $this->setEvent( $v, $arr, $semesterstart, $semesterend );

		$v->saveCalendar( JPATH_COMPONENT . $this->cfg[ 'pdf_downloadFolder' ], $title . '.ics' );
		$resparr[ 'url' ] = "false";
		return array("success"=>true,"data"=>$resparr );
	}

	private function setEvent( $v, $arr, $semesterstart, $semesterend )
	{
		$endarr = explode( "-", $semesterend );
		if ( is_array( $arr ) )
			foreach ( $arr as $event ) {
				if ( isset( $event->dow ) && isset( $event->block ) ) {
					$tempdate = $semesterstart;

					while ( date( "N", strtotime( $tempdate ) ) != 1 ) {
						$tempdate = date( "Y-m-d", strtotime( "-1 day", strtotime( $tempdate ) ) );
					}

					$tempdate = date( "Y-m-d", strtotime( "+" . ( ( (int) $event->dow ) - 1 ) . " day", strtotime( $tempdate ) ) );

					while ( $tempdate < $semesterstart ) {
						$tempdate = date( "Y-m-d", strtotime( "next monday", strtotime( $tempdate ) ) );
						$tempdate = date( "Y-m-d", strtotime( "+" . ( ( (int) $event->dow ) - 1 ) . " day", strtotime( $tempdate ) ) );
					}
					if ( $tempdate > $semesterend ) {
						return $v;
					}

					$beginarr = explode( "-", $tempdate );

					$times     = $this->blocktotime( $event->block );
					$begintime = explode( ":", $times[ 0 ] );
					$endtime   = explode( ":", $times[ 1 ] );

					$startdate  = array(
						 "year" => $beginarr[ 0 ],
						"month" => $beginarr[ 1 ],
						"day" => $beginarr[ 2 ],
						"hour" => $begintime[ 0 ],
						"min" => $begintime[ 1 ],
						"sec" => 0,
						"tz" => "Europe/Berlin"
					);
					$enddate    = array(
						 "year" => $beginarr[ 0 ],
						"month" => $beginarr[ 1 ],
						"day" => $beginarr[ 2 ],
						"hour" => $endtime[ 0 ],
						"min" => $endtime[ 1 ],
						"sec" => 0,
						"tz" => "Europe/Berlin"
					);
					$endarrdate = array(
						 "year" => $endarr[ 0 ],
						"month" => $endarr[ 1 ],
						"day" => $endarr[ 2 ],
						"hour" => 0,
						"min" => 0,
						"sec" => 0
					);

					$e = new vevent();

					$dozarr  = explode( " ", $event->doz );
					$doztemp = "";
					foreach ( $dozarr as $dozitem ) {
						$res = $this->getResource( $dozitem );
						if ( count( $res ) == 0 )
							$res[ 0 ]->oname = $dozitem;
						if ( $doztemp == "" )
							$doztemp .= "" . $res[ 0 ]->oname;
						else
							$doztemp .= ", " . $res[ 0 ]->oname;
					}

					$roomarr  = explode( " ", $event->room );
					$roomtemp = "";
					foreach ( $roomarr as $roomitem ) {
						$res = $this->getResource( $roomitem );
						if ( count( $res ) == 0 )
							$res[ 0 ]->oname = $roomitem;
						if ( $roomtemp == "" )
							$roomtemp .= "" . $res[ 0 ]->oname;
						else
							$roomtemp .= ", " . $res[ 0 ]->oname;
					}

					$desc = $event->name . " bei " . $doztemp . " im " . $roomtemp . "\n" . $this->nummerzutag( $event->dow ) . " " . $event->block . ".Block\nModulnummer: " . $event->desc . "\n";

					$e->setProperty( "ORGANIZER", $doztemp );
					$e->setProperty( "DTSTART", $startdate );
					$e->setProperty( "DTEND", $enddate );
					$e->setProperty( "RRULE", array(
						 "FREQ" => "WEEKLY",
						"UNTIL" => $endarrdate,
						"BYDAY" => array(
							 "DAY" => $this->daynumtoday( $event->dow )
						),
						"WKST" => "MO"
					) );
					$e->setProperty( "LOCATION", $roomtemp );
					$e->setProperty( "TRANSP", "OPAQUE" );
					$e->setProperty( "SEQUENCE", "0" );
					$e->setProperty( "SUMMARY", $event->name . " bei " . $doztemp . " im " . $roomtemp );
					$e->setProperty( "PRIORITY", "5" );
					$e->setProperty( "DESCRIPTION", $desc );
					//Doesnt work in Thunderbird and Outlook 2003
					$e->setProperty( "EXDATE", array(
						 array(
							 "year" => $endarr[ 0 ],
							"month" => $endarr[ 1 ],
							"day" => $endarr[ 2 ]
						)
					), array(
						 'VALUE' => 'DATE'
					) );

					$v->setComponent( $e );
				}
			}
		return $v;
	}

	private function blocktotime( $block )
	{
		$times = array(
			 1 => array(
				 0 => "8:00",
				1 => "9:30"
			),
			2 => array(
				 0 => "9:50",
				1 => "11:20"
			),
			3 => array(
				 0 => "11:30",
				1 => "13:00"
			),
			4 => array(
				 0 => "14:00",
				1 => "15:30"
			),
			5 => array(
				 0 => "15:45",
				1 => "17:15"
			),
			6 => array(
				 0 => "17:30",
				1 => "19:00"
			)
		);
		return $times[ $block ];
	}

	private function daynumtoday( $daynum )
	{
		$days = array(
			 1 => "MO",
			2 => "TU",
			3 => "WE",
			4 => "TH",
			5 => "FR"
		);
		return $days[ $daynum ];
	}

	private function nummerzutag( $daynum )
	{
		$days = array(
			 1 => "Montag",
			2 => "Dienstag",
			3 => "Mittwoch",
			4 => "Donnerstag",
			5 => "Freitag"
		);
		return $days[ $daynum ];
	}

	private function getResource( $resourcename )
	{
		$query = "SELECT oname FROM #__giessen_scheduler_objects WHERE oid ='" . $resourcename . "'";
		$hits  = $this->JDA->query( $query );
		return $hits;
	}
}
?>