<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		thm_organizerViewScheduler
 * @description thm_organizerViewScheduler file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * HTML View class for the THM Organizer Component
 *
 * @category  Joomla.Component.Site
 * @package   thm_organizer
 */

class THM_OrganizerViewScheduler extends JView
{
	/**
	 * Method to get extra
	 *
	 * @param   String  $tpl  template
	 *
	 * @return void
	 * 
	 * @see JView::display()
	 */
	public function display($tpl = null)
	{
		JHTML::_('behavior.tooltip');

		// Check wether the ExtJS 4 library is installed
		$libraryExtJS4IsInstalled = jimport('extjs4.extjs4');
		if(!$libraryExtJS4IsInstalled)
		{
		    return JError::raiseWarning(404, JText::_("COM_THM_ORGANIZER_EXTJS4_LIBRARY_NOT_INSTALLED"));
		}
		
		// Check wether the FPDF library is installed
		$libraryFPDFIsInstalled = jimport('fpdf.fpdf');
		$this->libraryFPDFIsInstalled = $libraryFPDFIsInstalled;
		
		$doc = JFactory::getDocument();
		$doc->addStyleSheet(JURI::root(true) . "/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/ext-all-gray.css");
		$doc->addStyleSheet(JURI::root(true) . "/components/com_thm_organizer/views/scheduler/tmpl/mySched/style.css");

		$schedulerModel = $this->getModel();
		$eventModel = JModel::getInstance('event_list', 'thm_organizerModel', array('ignore_request' => false, 'display_type' => 4));
		$ajaxModel = JModel::getInstance('Ajaxhandler', 'thm_organizerModel', array('ignore_request' => false));

		$menuparams = JFactory::getApplication()->getParams();
		$this->canWriteEvents = $eventModel->canWrite;
		$this->jsid = $schedulerModel->getSessionID();

		$this->searchModuleID = null;

		$this->searchModuleID = JRequest::getString('moduleID');
		$this->CurriculumisAvailable = $schedulerModel->isComAvailable("com_thm_curriculum");
		
		$deltaDisplayDays = (int) $menuparams->get("deltaDisplayDays", 14);
		if (is_int($deltaDisplayDays))
		{
			$this->deltaDisplayDays = $deltaDisplayDays;
		}
		else
		{
			$this->deltaDisplayDays = 14;
		}
		
		try
		{
			$publicDefaultIDArray = (array) json_decode($menuparams->get("publicDefaultID"));
		}
		catch (Exception $e)
		{
			$publicDefaultIDArray = array();
		}
				
		$schedule = $schedulerModel->getActiveSchedule($menuparams->get("departmentSemesterSelection"));
				
		if (is_object($schedule) AND is_string($schedule->schedule))
		{
			$scheduleData = json_decode($schedule->schedule);
		
			// To save memory unset schedule
			unset($schedule->schedule);
				
			if ($scheduleData != null)
			{
				$periods = $scheduleData->periods;
				unset($scheduleData->periods);
				$degrees = $scheduleData->degrees;
				unset($scheduleData->degrees);
				$rooms = $scheduleData->rooms;
				unset($scheduleData->rooms);
				$roomTypes = $scheduleData->roomtypes;
				unset($scheduleData->roomtypes);
				$subjects = $scheduleData->subjects;
				unset($scheduleData->subjects);
				$teachers = $scheduleData->teachers;
				unset($scheduleData->teachers);
				$modules = $scheduleData->modules;
				unset($scheduleData->modules);
				$calendar = $scheduleData->calendar;
				unset($scheduleData->calendar);
				$scheduleLessons = $scheduleData->lessons;
				unset($scheduleData->lessons);
				$scheduleFields = $scheduleData->fields;
				unset($scheduleData->fields);
		
				if (!is_object($periods) OR !is_object($degrees) OR !is_object($rooms)
				 OR !is_object($roomTypes) OR !is_object($subjects) OR !is_object($teachers)
				 OR !is_object($modules) OR !is_object($calendar) OR !is_object($scheduleLessons)
				 OR !is_object($scheduleFields))
				{
					return JError::raiseWarning(404, JText::_('COM_THM_ORGANIZER_SCHEDULER_IMPORTANT_DATA_MISSING'));
				}
			}
			else
			{
				// Cant decode json
				return JError::raiseWarning(404, JText::_('COM_THM_ORGANIZER_SCHEDULER_DATA_FLAWED')); 
			}
		}
		else
		{
			return JError::raiseWarning(404, JText::_('COM_THM_ORGANIZER_SCHEDULER_NO_ACTIVE_SCHEDULE'));
		}

		$this->semesterID = $schedule->id;
		$this->semAuthor = "";
		$this->semesterName = $menuparams->get("departmentSemesterSelection");

		$schedulearr = array();

				
		$periods->length = count((array) $periods);
		
		foreach ($periods as $period)
		{
			if (isset($period->starttime) AND is_string($period->starttime))
			{
				$period->starttime = wordwrap($period->starttime, 2, ':', 1);
			}
			if (isset($period->endtime) AND is_string($period->endtime))
			{
				$period->endtime = wordwrap($period->endtime, 2, ':', 1);
			}
		}
		
		$schedulearr['CurriculumColors'] = $schedulerModel->getCurriculumModuleColors();
		
		$schedulearr["Grid.load"] = $periods;
		
		$schedulearr["Calendar"] = $calendar;
		
		$schedulearr["Events.load"] = $ajaxModel->executeTask("Events.load");

		$schedulearr["UserSchedule.load"] = array();
		
		$this->loadLessonsOnStartUp = (bool) $menuparams->get("loadLessonsOnStartUp");		

		if ($this->loadLessonsOnStartUp == true)
		{
			$lessons = array();
			
			foreach ($calendar as $dateKey => $dateValue)
			{
				if (is_object($dateValue))
				{
					foreach ($dateValue as $blockKey => $blockValue)
					{
						foreach ($blockValue as $lessonKey => $lessonValue)
						{
							$currentDate = new DateTime($dateKey);
							$dow = strtolower($currentDate->format("l"));
							
							$lessonID = $lessonKey . $blockKey . $dow;
																	
							if (!array_key_exists($lessonID, $lessons))
							{
								$lessons[$lessonID] = clone $scheduleLessons->{$lessonKey};
								$lessons[$lessonID]->lessonKey = $lessonKey;
								$lessons[$lessonID]->block = $blockKey;
								$lessons[$lessonID]->dow = $dow;
							}
							
							if (!isset($lessons[$lessonID]->calendar))
							{
								$lessons[$lessonID]->calendar = array();
							}
			
							$lessons[$lessonID]->calendar[$dateKey][$blockKey]["lessonData"] = clone $lessonValue;
						}
					}
				}
			}
						
			$schedulearr["Lessons"] = $lessons;
		}
				
		$schedulearr["UserSchedule.load"]["respChanges"] = $ajaxModel->executeTask("UserSchedule.load", array("username" => "respChanges"));

		$schedulearr["ScheduleDescription.load"] = new stdClass;
		$schedulearr["ScheduleDescription.load"]->data = $schedule;

		$this->startup = rawurlencode(json_encode($schedulearr));

		parent::display($tpl);
	}
}
