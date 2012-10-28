<?php
/**
 * @version	    v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		ICSBauer
 * @description ICSBauer file from com_thm_organizer
 * @author	    Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

require_once dirname(__FILE__) . "/abstrakterBauer.php";
error_reporting(0);

/**
 * Class ICSBauer for component com_thm_organizer
 *
 * Class provides methods to create a schedule in excel format
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class ICSBauer extends abstrakterBauer
{
	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 * @since  1.0
	 */
	private $_JDA = null;

	/**
	 * Config
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_cfg = null;

	/**
	 * Excel
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_objPHPExcel = null;
	
	/**
	 * Active Schedule
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_activeSchedule = null;
	
	/**
	 * Active Schedule data
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_activeScheduleData = null;

	/**
	 * Constructor with the joomla data abstraction object and configuration object
	 *
	 * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
	 * @param   Object	 		 $cfg  A object which has configurations including
	 *
	 * @since  1.5
	 *
	 */
	public function __construct($JDA, $cfg)
	{
		$this->JDA = $JDA;
		$this->cfg = $cfg;
	}

	/**
	 * Method to create a excel schedule
	 *
	 * @param   Object  $arr 	   The event object
	 * @param   String  $username  The current logged in username
	 * @param   String  $title 	   The schedule title
	 *
	 * @return Array An array with information about the status of the creation
	 */
	public function erstelleStundenplan($arr, $username, $title)
	{
		$success = false;

		$arr = $arr[0];
				
		try
		{
			/** PHPExcel */
			require_once JPATH_COMPONENT . '/assets/ExcelClasses/PHPExcel.php';
			$this->objPHPExcel = new PHPExcel;

			if ($title == JText::_("COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE"))
			{
				$title = $username . " - " . $title;
			}

			$this->objPHPExcel->getProperties()->setCreator($username)
			->setLastModifiedBy($username)
			->setTitle($title)
			->setSubject($title);

			$this->objPHPExcel->getActiveSheet()->setTitle(JText::_("COM_THM_ORGANIZER_SCHEDULER_CYCLIC_EVENTS"));
									
			if(isset($arr->session->semesterID))
			{
				$this->_activeSchedule = $this->getActiveSchedule((int) $arr->session->semesterID);
			}
			else
			{
				return array("success" => false, "data" => JText::_("COM_THM_ORGANIZER_SCHEDULER_NO_FILE_CREATED"));
			}
						
			if($this->_activeSchedule == false)
			{
				return array("success" => false, "data" => JText::_("COM_THM_ORGANIZER_SCHEDULER_NO_FILE_CREATED"));
			}
						
			if (is_object($this->_activeSchedule) && is_string($this->_activeSchedule->schedule))
			{
				$this->_activeScheduleData = json_decode($this->_activeSchedule->schedule);
			
				// To save memory unset schedule
				unset($this->_activeSchedule->schedule);
				
				if ($this->_activeScheduleData == null)
				{
					// Cant decode json
					return JError::raiseWarning(404, JText::_('Fehlerhafte Daten'));
				}
			}
			else
			{
				return JError::raiseWarning(404, JText::_('Kein aktiver Stundenplan'));
			}
			
			$success = $this->setLessonHead();
			if ($success)
			{
				$success = $this->setLessonContent($arr);
			}

			$this->objPHPExcel->createSheet();
			$this->objPHPExcel->setActiveSheetIndex(1);
			$this->objPHPExcel->getActiveSheet()->setTitle(JText::_("COM_THM_ORGANIZER_SCHEDULER_SPORADIC_EVENTS"));

			if ($success)
			{
				$success = $this->setEventHead();
			}
			if ($success)
			{
				$success = $this->setEventContent($arr);
			}

			if ($success)
			{
				$this->objPHPExcel->setActiveSheetIndex(0);
				$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');
				$objWriter->save($this->cfg['pdf_downloadFolder'] . $title . ".xls");
			}
		}
		catch (Exception $e)
		{
			$success = false;
		}

		if ($success)
		{
			return array("success" => true, "data" => JText::_("COM_THM_ORGANIZER_SCHEDULER_FILE_CREATED"));
		}
		else
		{
			return array("success" => false, "data" => JText::_("COM_THM_ORGANIZER_SCHEDULER_NO_FILE_CREATED"));
		}
	}

	/**
	 * Method to set the excel header
	 *
	 * @return Boolean Return true on success
	 */
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

	/**
	 * Method to create a ical schedule
	 *
	 * @param   Object  $arr  The event object
	 *
	 * @return Boolean Return true on success
	 */
	private function setEventContent($arr)
	{
		$row = 2;
		foreach ($arr->events as $item)
		{
			$resources = implode('", "', (array) $item->data->objects);
			$resString = "";
			$res = array();
			$query      = 'SELECT name as oname FROM #__thm_organizer_classes WHERE id IN("' . $resources . '")';
			$res        = array_merge($res, $this->JDA->query($query, true));

			$query     = 'SELECT name as oname FROM #__thm_organizer_teachers WHERE gpuntisID IN("' . $resources . '")';
			$res       = array_merge($res, $this->JDA->query($query, true));

			$query      = 'SELECT name as oname FROM #__thm_organizer_rooms WHERE gpuntisID IN("' . $resources . '")';
			$res        = array_merge($res, $this->JDA->query($query, true));
			if (count($res) > 0)
			{
				$resString = implode(", ", $res);
			}

			$this->objPHPExcel->getActiveSheet()
			->setCellValue('A' . $row, $item->data->title)
			->setCellValue('B' . $row, $item->data->edescription)
			->setCellValue('C' . $row, $resString)
			->setCellValue('D' . $row, $item->data->category)
			->setCellValue('E' . $row, $item->data->startdate)
			->setCellValue('F' . $row, $item->data->enddate)
			->setCellValue('G' . $row, $item->data->starttime)
			->setCellValue('H' . $row, $item->data->endtime);
			$row++;
		}
		return true;
	}

	/**
	 * Method to set the lesson header
	 *
	 * @return Boolean Return true on success
	 */
	private function setLessonHead()
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

	/**
	 * Method to set the lesson content
	 *
	 * @param   Object  $arr  The event object
	 *
	 * @return Boolean Return true on success
	 */
	private function setLessonContent($arr)
	{
		foreach ($arr->lessons as $item)
		{
			if (isset($item->modules) && isset($item->teachers) && isset($item->calendar))
			{
				if (isset($item->block) && $item->block > 0)
				{
					$times       = $this->blocktotime($item->block);
					$item->stime = $times[0];
					$item->etime = $times[1];
				}
				$item->sdate = $arr->session->sdate;
				$item->edate = $arr->session->edate;
				
				$teacherNames = array();
				foreach($item->teachers->map as $teacherID => $teacherStatus)
				{
					if($teacherStatus != "removed")
					{
						$teacherNames[] = $this->getTeacherName($teacherID);
					}
				}
				$item->teachers = implode(", ", $teacherNames);
				
				$moduleNames = array();
				foreach($item->modules->map as $moduleID => $moduleStatus)
				{
					if($moduleStatus != "removed")
					{
						$moduleNames[] = $this->getModuleName($moduleID);
					}
				}
				$item->modules = implode(", ", $moduleNames);
				
				$roomNames = array();
				foreach($item->calendar as $block)
				{
					foreach($block->{$item->block}->lessonData as $roomID => $roomStatus)
					{
						if($roomStatus != "removed")
						{
							$roomNames[] = $this->getRoomName($roomID);
						}
					}
					break;
				}
				$item->rooms = implode(", ", $roomNames);
				
				$subjectNo = array();
				$subjectName = array();
				$subjectLongname = array();
				foreach($item->subjects->map as $subjectID => $subjectStatus)
				{
					if($subjectStatus != "removed")
					{
						$subjectNo[] = $this->getSubjectNo($subjectID);
						$subjectName[] = $this->getSubjectName($subjectID);
						$subjectLongname[] = $this->getSubjectLongname($subjectID);
					}
				}
				$item->subjectNo = implode(", ", $subjectNo);
				$item->name = implode(", ", $subjectName);
				$item->longname = implode(", ", $subjectLongname);	
			}
		}
		
		$row = 2;

		/**
		 * Function to custom sort the lesson array by their teachers
		 *
		 * @param   object  $a  An object in the array
		 * @param   object  $b  Another object in the array
		 *
		 * @return Integer Return 0 if the lesson teachers are the same
		 * 						  +1 if the $a lesson string is greater than the $b lesson string
		 * 						  -1 if the $a lesson string is lesser than the $b lesson string
		 */
		function sortLessonsByDoz($a, $b)
		{
			if ($a->teachers == $b->teachers)
			{
				return 0;
			}
			return ($a->teachers < $b->teachers) ? -1 : 1;
		}

		uasort($arr->lessons, $this->sortLessonsByDoz);
		
		foreach ($arr->lessons as $item)
		{
			if (isset($item->modules) && isset($item->teachers) && isset($item->rooms))
			{
				if (!isset($item->longname))
				{
					$item->longname = "";
				}
				if (!isset($item->category))
				{
					$item->category = "";
				}
				
				$this->objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A' . $row, $item->longname)
				->setCellValue('B' . $row, $item->name)
				->setCellValue('C' . $row, $item->subjectNo)
				->setCellValue('D' . $row, $item->description)
				->setCellValue('E' . $row, $this->dayENtoday($item->dow))
				->setCellValue('F' . $row, $item->block)
				->setCellValue('G' . $row, $item->rooms)
				->setCellValue('H' . $row, $item->teachers);
				$row++;
			}
		}
		return true;
	}
	
	private function getSubjectNo($id)
	{
		$subjects = $this->_activeScheduleData->subjects;
		return $subjects->{$id}->subjectNo;
	}
	
	private function getSubjectName($id)
	{
		$subjects = $this->_activeScheduleData->subjects;
		return $subjects->{$id}->name;
	}
	
	private function getSubjectLongname($id)
	{
		$subjects = $this->_activeScheduleData->subjects;
		return $subjects->{$id}->longname;
	}
	
	private function getModuleName($id)
	{
		$modules = $this->_activeScheduleData->modules;
		return $modules->{$id}->name;
	}
	
	private function getRoomName($id)
	{
		$rooms = $this->_activeScheduleData->rooms;
		return $rooms->{$id}->longname;
	}
	
	private function getTeacherName($id)
	{
		$teachers = $this->_activeScheduleData->teachers;
		$name = $teachers->{$id}->surname;
		if(strlen($teachers->{$id}->firstname) > 0)
		{
			$name .= ", " . $teachers->{$id}->firstname{0} . ".";
		} 
		return $name;
	}
	
	/**
	 * Method to get the active schedule
	 *
	 * @param   String  $departmentSemesterSelection  The department semester selection
	 *
	 * @return   mixed  The active schedule or false
	 */
	private function getActiveSchedule($semesterID)
	{	
		if(!is_int($semesterID))
		{
			return false;
		}
		
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);
		$query->select('*');
		$query->from('#__thm_organizer_schedules');
		$query->where('id = ' . $semesterID);
		$dbo->setQuery($query);
	
		if ($error = $dbo->getErrorMsg())
		{
			return false;
		}
	
		$result = $dbo->loadObject();
	
		if ($result === null)
		{
			return false;
		}
		return $result;
	}

	/**
	 * Method to transform a block number to the block time (starttime and endtime)
	 *
	 * @param   Integer  $block  The block number
	 *
	 * @return Array An array which includes the block time (starttime and endtime)
	 */
	private function blocktotime($block)
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
		return $times[$block];
	}

	/**
	 * Method to transform a day number to the day name
	 *
	 * @param   Integer  $daynum  The day number
	 *
	 * @return String The day name
	 */
	private function dayENtoday($dayEN)
	{
		$days = array(
			 'monday' => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_MONDAY"),
			 'tuesday' => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_TUESDAY"),
			 'wednesday' => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_WEDNESDAY"),
			 'thursday' => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_THURSDAY"),
			 'friday' => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_FRIDAY"),
			 'saturday' => JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_SATURDAY"),
			 'sunday'=> JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_SUNDAY")
		);
		return $days[$dayEN];
	}
}
