<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( dirname( __FILE__ ) . "/abstrakterBauer.php" );

class ICSBauer extends abstrakterBauer
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
		$output = "";
		$output .= $this->tableHead();
		$output .= $this->tableContent( $arr );
		$output .= $this->tableFooter();
		if ( $title == "Mein Stundenplan" )
			$title = $username . " - " . $title;
		$newfile = JPATH_COMPONENT . $this->cfg[ 'pdf_downloadFolder' ] . $title . ".html";
		$file    = fopen( $newfile, "w" );
		fwrite( $file, $output );
		fclose( $file );
		if(is_file($file))
			return array("success"=>true,"data"=>"File created!");
		else
			return array("success"=>false,"data"=>"No file was created!");
	}

	private function tableHead( )
	{
		return '
        <HTML>
      <HEAD>
      <META http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <META http-equiv="Content-Script-Type" content="text/javascript">
      <table cellpadding="0" cellspacing="0" border="1">
      <tr>
       <th><b>Titel der Veranstaltung</b> </th>
       <th><b>Abkürzung</b></th>
       <th><b>ModulNr</b></th>
       <th><b>Typ</b></th>
       <th><b>Wochentag</b></th>
       <th><b>Block</b></th>
       <th><b>Raum</b></th>
       <th><b>Dozent</b></th>
      </tr>
    ';
	}

	private function tableContent( $arr )
	{
		$ret = "";
		foreach ( $arr as $item ) {
			if ( isset( $item->clas ) && isset( $item->doz ) && isset( $item->room ) ) {
				if ( isset( $item->block ) && $item->block > 0 ) {
					$times       = $this->blocktotime( $item->block );
					$item->stime = $times[ 0 ];
					$item->etime = $times[ 1 ];
				}
				$item->sdate = $arr[ count( $arr ) - 1 ]->sdate;
				$item->edate = $arr[ count( $arr ) - 1 ]->edate;

				$classes    = explode( " ", trim( $item->clas ) );
				$query      = 'SELECT name as oname FROM #__thm_organizer_classes WHERE id IN("' . implode( '", "', $classes ) . '")';
				$res        = $this->JDA->query( $query, true );
				$item->clas = implode( ", ", $res );

				$dozs      = explode( " ", trim( $item->doz ) );
				$query     = 'SELECT name as oname FROM #__thm_organizer_teachers WHERE id IN("' . implode( '", "', $dozs ) . '")';
				$res       = $this->JDA->query( $query, true );
				$item->doz = implode( ", ", $res );

				$rooms      = explode( " ", trim( $item->room ) );
				$query      = 'SELECT name as oname FROM #__thm_organizer_rooms WHERE id IN("' . implode( '", "', $rooms ) . '")';
				$res        = $this->JDA->query( $query, true );
				$item->room = implode( ", ", $res );
			}
		}
		foreach ( $arr as $item ) {
			if ( isset( $item->clas ) && isset( $item->doz ) && isset( $item->room ) ) {
				if(!isset($item->longname))
					$item->longname = "";
				if(!isset($item->category))
					$item->category = "";
				$ret .= "<tr>
		         <td>" . $item->longname . "</td>
		         <td>" . $item->name . "</td>
		         <td>" . $item->desc . "</td>
		         <td>" . $item->category . "</td>
		         <td>" . $this->daynumtoday( $item->dow ) . "</td>
		         <td>" . $item->block . "</td>
		         <td>" . $item->room . "</td>
		         <td>" . $item->doz . "</td>
		        </tr>";
			}
		}
		return $ret;
	}

	private function tableFooter( )
	{
		return '
      </table>
      </BODY>
      </HTML>
    ';
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
			 1 => "Montag",
			2 => "Dienstag",
			3 => "Mittwoch",
			4 => "Donnerstag",
			5 => "Freitag",
			6 => "Samstag",
			0 => "Sonntag"
		);
		return $days[ $daynum ];
	}
}
?>