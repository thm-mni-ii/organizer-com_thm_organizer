<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( dirname( __FILE__ ) . "/abstrakterBauer.php" );
error_reporting(0);

class ICSBauer extends abstrakterBauer
{
	private $JDA = null;
	private $cfg = null;
	private $objPHPExcel = null;

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
			/** PHPExcel */
			require_once JPATH_COMPONENT.'/assets/ExcelClasses/PHPExcel.php';
			$this->objPHPExcel = new PHPExcel();

			if ( $title == "Mein Stundenplan" )
				$title = $username . " - " . $title;

			$this->objPHPExcel->getProperties()->setCreator($username)
							 ->setLastModifiedBy($username)
							 ->setTitle($title)
							 ->setSubject($title);

			// The actual data
			$success = $this->setHead();

			if($success)
				$success = $this->setContent( $arr );

			if($success)
			{
				$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
				$objWriter->save(JPATH_COMPONENT . $this->cfg[ 'pdf_downloadFolder' ] . $title . ".xls");
			}
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
		$this->objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Titel der Veranstaltung')
            ->setCellValue('B1', 'Abkürzung')
            ->setCellValue('C1', 'ModulNr')
            ->setCellValue('D1', 'Typ')
            ->setCellValue('E1', 'Wochentag')
            ->setCellValue('F1', 'Block')
            ->setCellValue('G1', 'Raum')
            ->setCellValue('H1', 'Dozent');
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

		$row = 2;
		foreach ( $arr as $item ) {
			if ( isset( $item->clas ) && isset( $item->doz ) && isset( $item->room ) ) {
				if(!isset($item->longname))
					$item->longname = "";
				if(!isset($item->category))
					$item->category = "";


				$this->objPHPExcel->setActiveSheetIndex(0)
		            ->setCellValue('A'.$row, $item->longname)
		            ->setCellValue('B'.$row, $item->name)
		            ->setCellValue('C'.$row, $item->moduleID)
		            ->setCellValue('D'.$row, $item->category)
		            ->setCellValue('E'.$row, $this->daynumtoday( $item->dow ))
		            ->setCellValue('F'.$row, $item->block)
		            ->setCellValue('G'.$row, $item->room)
		            ->setCellValue('H'.$row, $item->doz);
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