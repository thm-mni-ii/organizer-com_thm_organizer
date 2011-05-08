<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( dirname( __FILE__ ) . "/abstrakterBauer.php" );
error_reporting(0);

class ICSBauer extends abstrakterBauer
{
	private $JDA = null;
	private $cfg = null;
	private $workbook = null;
	private $worksheet = null;

	function __construct($JDA, $cfg)
	{
		$this->JDA = $JDA;
		$this->cfg = $cfg;
	}

	public function erstelleStundenplan( $arr, $username, $title )
	{
		$success = false;
		try
		{
			require_once JPATH_COMPONENT.'/assets/Spreadsheet/Excel/Writer.php';

			if ( $title == "Mein Stundenplan" )
				$title = $username . " - " . $title;

			// Creating a workbook
			$this->workbook = new Spreadsheet_Excel_Writer(JPATH_COMPONENT . $this->cfg[ 'pdf_downloadFolder' ] . $title . ".xls");

			$this->workbook->setVersion(8);

			// Creating a worksheet
			$this->worksheet = & $this->workbook->addWorksheet($title);

			$this->worksheet->setInputEncoding("UTF-8");

			// The actual data
			$success = $this->setHead();
			if($success)
				$success = $this->setContent( $arr );

			if($success)
				// Let's send the file
				$this->workbook->close();
		}
		catch(Exception $e)
		{
			$success = false;
		}

		if($success)
			return array("success"=>true,"data"=>"File created!");
		else
			return array("success"=>false,"data"=>"No file was created!");
	}

	private function setHead( )
	{
		$this->worksheet->write(0, 0, 'Titel der Veranstaltung');
		$this->worksheet->write(0, 1, 'Abkürzung');
		$this->worksheet->write(0, 2, 'ModulNr');
		$this->worksheet->write(0, 3, 'Typ');
		$this->worksheet->write(0, 4, 'Wochentag');
		$this->worksheet->write(0, 5, 'Block');
		$this->worksheet->write(0, 6, 'Raum');
		$this->worksheet->write(0, 7, 'Dozent');
		return true;
	}

	private function setContent( $arr )
	{
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

				$query     = 'SELECT name as oname FROM #__thm_organizer_teachers WHERE gpuntisID IN("' . implode( '", "', $dozs ) . '")';
				$res       = $this->JDA->query( $query, true );
				$item->doz = implode( ", ", $res );

				$rooms      = explode( " ", trim( $item->room ) );

				$query      = 'SELECT name as oname FROM #__thm_organizer_rooms WHERE gpuntisID IN("' . implode( '", "', $rooms ) . '")';
				$res        = $this->JDA->query( $query, true );
				$item->room = implode( ", ", $res );
			}
		}

		$row = 1;
		foreach ( $arr as $item ) {
			if ( isset( $item->clas ) && isset( $item->doz ) && isset( $item->room ) ) {
				if(!isset($item->longname))
					$item->longname = "";
				if(!isset($item->category))
					$item->category = "";

				$this->worksheet->write($row, 0, $item->longname);
				$this->worksheet->write($row, 1, $item->name);
				$this->worksheet->write($row, 2, $item->moduleID);
				$this->worksheet->write($row, 3, $item->category);
				$this->worksheet->write($row, 4, $this->daynumtoday( $item->dow ));
				$this->worksheet->write($row, 5, $item->block);
				$this->worksheet->write($row, 6, $item->room);
				$this->worksheet->write($row, 7, $item->doz);
				$row++;
			}
		}
		return true;
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