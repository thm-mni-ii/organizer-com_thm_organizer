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

		$arr = $arr[0];

		try
		{
			/** PHPExcel */
			require_once JPATH_COMPONENT.'/assets/ExcelClasses/PHPExcel.php';
			$this->objPHPExcel = new PHPExcel();

			if ( $title == JText::_("COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE") )
				$title = $username . " - " . $title;

			$this->objPHPExcel->getProperties()->setCreator($username)
							 ->setLastModifiedBy($username)
							 ->setTitle($title)
							 ->setSubject($title);


			$this->objPHPExcel->getActiveSheet()->setTitle(JText::_("COM_THM_ORGANIZER_SCHEDULER_CYCLIC_EVENTS"));

			$success = $this->setLessonHead();
			if($success)
				$success = $this->setLessonContent( $arr );

				$this->objPHPExcel->createSheet();
				$this->objPHPExcel->setActiveSheetIndex(1);
				$this->objPHPExcel->getActiveSheet()->setTitle(JText::_("COM_THM_ORGANIZER_SCHEDULER_SPORADIC_EVENTS"));

				if($success)
					$success = $this->setEventHead();
					if($success)
						$success = $this->setEventContent( $arr );

				if($success)
				{
					$this->objPHPExcel->setActiveSheetIndex(0);
					$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
					$objWriter->save($this->cfg[ 'pdf_downloadFolder' ] . $title . ".xls");
				}
		}
		catch(Exception $e)
		{
			$success = false;
		}

		if($success)
			return array("success"=>true,"data"=>JText::_("COM_THM_ORGANIZER_SCHEDULER_FILE_CREATED"));
		else
			return array("success"=>false,"data"=>JText::_("COM_THM_ORGANIZER_SCHEDULER_NO_FILE_CREATED"));
	}

	private function setEventHead()
	{
		$this->objPHPExcel->getActiveSheet()
            ->setCellValue('A1', JText::_("COM_THM_ORGANIZER_SCHEDULER_TITLE"))
            ->setCellValue('B1', JText::_("COM_THM_ORGANIZER_SCHEDULER_DESCRIPTION"))
            ->setCellValue('C1', JText::_("COM_THM_ORGANIZER_SCHEDULER_AFFECTED_RESOURCE"))
            ->setCellValue('D1', JText::_("COM_THM_ORGANIZER_SCHEDULER_CATEGORY"))
            ->setCellValue('E1', JText::_("COM_THM_ORGANIZER_SCHEDULER_DATE_OF"))
            ->setCellValue('F1', JText::_("COM_THM_ORGANIZER_SCHEDULER_TO_DATE"))
            ->setCellValue('G1', JText::_("COM_THM_ORGANIZER_SCHEDULER_TIME_OF"))
            ->setCellValue('H1', JText::_("COM_THM_ORGANIZER_SCHEDULER_TO_TIME"));

		$this->objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);

		return true;
	}

	private function setEventContent( $arr )
	{
		$row = 2;
		foreach($arr->events as $item)
		{
			$resources = implode( '", "', (array)$item->data->objects );
			$resString = "";
			$res = array();
			$query      = 'SELECT name as oname FROM #__thm_organizer_classes WHERE id IN("' . $resources . '")';
			$res        = array_merge($res, $this->JDA->query( $query, true ));

			$query     = 'SELECT name as oname FROM #__thm_organizer_teachers WHERE gpuntisID IN("' . $resources . '")';
			$res       = array_merge($res, $this->JDA->query( $query, true ));

			$query      = 'SELECT name as oname FROM #__thm_organizer_rooms WHERE gpuntisID IN("' . $resources . '")';
			$res        = array_merge($res, $this->JDA->query( $query, true ));
			if(count($res) > 0)
				$resString = implode( ", ", $res );

			$this->objPHPExcel->getActiveSheet()
	            ->setCellValue('A'.$row, $item->data->title)
	            ->setCellValue('B'.$row, $item->data->edescription)
	            ->setCellValue('C'.$row, $resString)
	            ->setCellValue('D'.$row, $item->data->category)
	            ->setCellValue('E'.$row, $item->data->startdate)
	            ->setCellValue('F'.$row, $item->data->enddate)
	            ->setCellValue('G'.$row, $item->data->starttime)
	            ->setCellValue('H'.$row, $item->data->endtime);
			$row++;
		}
		return true;
	}

	private function setLessonHead( )
	{
		$this->objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', JText::_("COM_THM_ORGANIZER_SCHEDULER_LESSON_TITLE"))
            ->setCellValue('B1', JText::_("COM_THM_ORGANIZER_SCHEDULER_ABBREVIATION"))
            ->setCellValue('C1', JText::_("COM_THM_ORGANIZER_SCHEDULER_MODULE_NUMBER"))
            ->setCellValue('D1', JText::_("COM_THM_ORGANIZER_SCHEDULER_TYPE"))
            ->setCellValue('E1', JText::_("COM_THM_ORGANIZER_SCHEDULER_WEEKDAY"))
            ->setCellValue('F1', JText::_("COM_THM_ORGANIZER_SCHEDULER_BLOCK"))
            ->setCellValue('G1', JText::_("COM_THM_ORGANIZER_SCHEDULER_ROOM"))
            ->setCellValue('H1', JText::_("COM_THM_ORGANIZER_SCHEDULER_TEACHER"));

		$this->objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('G1')->getFont()->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyle('H1')->getFont()->setBold(true);

		return true;
	}

	private function setLessonContent( $arr )
	{
		foreach ( $arr->lessons as $item ) {
			if ( isset( $item->clas ) && isset( $item->doz ) && isset( $item->room ) ) {
				if ( isset( $item->block ) && $item->block > 0 ) {
					$times       = $this->blocktotime( $item->block );
					$item->stime = $times[ 0 ];
					$item->etime = $times[ 1 ];
				}
				$item->sdate = $arr->session->sdate;
				$item->edate = $arr->session->edate;

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

		$row = 2;
				
		function sortLessonsByDoz($a, $b)
		{
			if ($a->doz == $b->doz) {
				return 0;
			}
			return ($a->doz < $b->doz) ? -1 : 1;
		}
		
		uasort($arr->lessons, sortLessonsByDoz);
		
		foreach ( $arr->lessons as $item ) {
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
			 1 => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_MONDAY"),
			2 => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_TUESDAY"),
			3 => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_WEDNESDAY"),
			4 => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_THURSDAY"),
			5 => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_FRIDAY"),
			6 => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_SATURDAY"),
			0 => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_SUNDAY")
		);
		return $days[ $daynum ];
	}
}
?>