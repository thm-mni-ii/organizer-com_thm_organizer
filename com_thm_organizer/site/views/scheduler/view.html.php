<?php

/**
 * @version	    v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		thm_organizerViewScheduler
 * @description thm_organizerViewScheduler file from com_thm_organizer
 * @author	    Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('THM.thm_extjs4.thm_extjs4');
jimport('joomla.application.component.view');


/**
 * HTML View class for the THM Organizer Component
 *
 * @category  Joomla.Component.Site
 * @package   thm_organizer
 * @since     v0.0.1
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
		$model = $this->getModel();
		$menuparams = JFactory::getApplication()->getParams();
				
		$user = JFactory::getUser();
		$eventmodel = JModel::getInstance('event_list', 'thm_organizerModel', array('ignore_request' => false, 'display_type' => 4));
		$canWriteEvents = $eventmodel->canWrite;
		$this->canWriteEvents = $canWriteEvents;
		$this->jsid = $model->getSessionID();

		$showSchedule = null;
		$this->searchModuleID = null;
		$semesterID = null;

		$showSchedule = JRequest::getString('showSchedule');
		$this->searchModuleID = JRequest::getString('moduleID');
		$this->CurriculumisAvailable = $model->getComStatus("com_thm_curriculum");
		
		$path = null;
		$menuparamsID = $menuparams->get("id");
		$menuparamsPublicDefaultID = $menuparams->get("publicDefaultID");
		$departmentSemesterSelection = $menuparams->get("departmentSemesterSelection");
		$deltaDisplayDays = (int) $menuparams->get("deltaDisplayDays", 14);
		if(is_int($deltaDisplayDays))
		{
			$this->deltaDisplayDays = $deltaDisplayDays;
		}
		else
		{
			$this->deltaDisplayDays = 14;
		}
				
		try
		{
			$path = (array) json_decode($menuparamsID);
		}
		catch (Exception $e)
		{
			$path = array();
		}
		
		try
		{
			$publicDefaultIDArray = (array) json_decode($menuparamsPublicDefaultID);
		}
		catch (Exception $e)
		{
			$publicDefaultIDArray = array();
		}
				
		$activeSchedule = $model->getActiveSchedule($departmentSemesterSelection);
				
		if (is_object($activeSchedule) && is_string($activeSchedule->schedule))
		{
			$activeScheduleData = json_decode($activeSchedule->schedule);
		
			// To save memory unset schedule
			unset($activeSchedule->schedule);
				
			if ($activeScheduleData != null)
			{
				$activeSchedulePeriods = $activeScheduleData->periods;
				unset($activeScheduleData->periods);
				$activeScheduleDegrees = $activeScheduleData->degrees;
				unset($activeScheduleData->degrees);
				$activeScheduleRooms = $activeScheduleData->rooms;
				unset($activeScheduleData->rooms);
				$activeScheduleRoomTypes = $activeScheduleData->roomtypes;
				unset($activeScheduleData->roomtypes);
				$activeScheduleSubjects = $activeScheduleData->subjects;
				unset($activeScheduleData->subjects);
				$activeScheduleTeachers = $activeScheduleData->teachers;
				unset($activeScheduleData->teachers);
				$activeScheduleModules = $activeScheduleData->modules;
				unset($activeScheduleData->modules);
				$activeScheduleCalendar = $activeScheduleData->calendar;
				unset($activeScheduleData->calendar);
				$activeScheduleLessons = $activeScheduleData->lessons;
				unset($activeScheduleData->lessons);
				$activeScheduleFields = $activeScheduleData->fields;
				unset($activeScheduleData->fields);
		
				if (is_object($activeSchedulePeriods)
						&& is_object($activeScheduleDegrees)
						&& is_object($activeScheduleRooms)
						&& is_object($activeScheduleRoomTypes)
						&& is_object($activeScheduleSubjects)
						&& is_object($activeScheduleTeachers)
						&& is_object($activeScheduleModules)
						&& is_object($activeScheduleCalendar)
						&& is_object($activeScheduleLessons)
						&& is_object($activeScheduleFields))
				{
						
				}
				else
				{
					return JError::raiseWarning(404, JText::_('Wichtige Daten fehlen'));
				}
			}
			else
			{
				// Cant decode json
				return JError::raiseWarning(404, JText::_('Fehlerhafte Daten'));
			}
		}
		else
		{
			return JError::raiseWarning(404, JText::_('Kein aktiver Stundenplan'));
		}
		
		$semesterID = $activeSchedule->id;

		$this->semesterID = $semesterID;
		$this->semAuthor = "";
		$this->semesterName = $departmentSemesterSelection;

		$doc = JFactory::getDocument();
		$doc->addStyleSheet(JURI::root(true) . "/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/ext-all-gray.css");

		// $doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/MultiSelect.css");

		$doc->addStyleSheet(JURI::root(true) . "/components/com_thm_organizer/views/scheduler/tmpl/mySched/style.css");

		$schedulearr = array();

		$model = JModel::getInstance('Ajaxhandler', 'thm_organizerModel', array('ignore_request' => false));
				
		$activeSchedulePeriods->length = count((array) $activeSchedulePeriods);
		
		foreach($activeSchedulePeriods as $period)
		{
			if(isset($period->starttime) && is_string($period->starttime))
			{
				$period->starttime = wordwrap($period->starttime, 2, ':', 1);
			}
			if(isset($period->endtime) && is_string($period->endtime))
			{
				$period->endtime = wordwrap($period->endtime, 2, ':', 1);
			}
		}
		
		$schedulearr["Grid.load"] = $activeSchedulePeriods;
		
		$schedulearr["Calendar"] = $activeScheduleCalendar;
		
		$schedulearr["Events.load"] = $model->executeTask("Events.load");

		$schedulearr["UserSchedule.load"] = array();
		
		$this->loadLessonsOnStartUp = (bool) $menuparams->get("loadLessonsOnStartUp");		

		if($this->loadLessonsOnStartUp == true)
		{
			$lessons = array();
			
			foreach($activeScheduleCalendar as $dateKey => $dateValue)
			{
				if (is_object($dateValue))
				{
					foreach($dateValue as $blockKey => $blockValue)
					{
						foreach($blockValue as $lessonKey => $lessonValue)
						{
							$currentDate = new DateTime($dateKey);
							$dow = strtolower($currentDate->format("l"));
							
							$lessonID = $lessonKey . $blockKey . $dow;
																	
							if(!array_key_exists($lessonID, $lessons))
							{
								$lessons[$lessonID] = clone $activeScheduleLessons->{$lessonKey};
								$lessons[$lessonID]->lessonKey = $lessonKey;
								$lessons[$lessonID]->block = $blockKey;
								$lessons[$lessonID]->dow = $dow;
							}
							
							if(!isset($lessons[$lessonID]->calendar))
							{
								$lessons[$lessonID]->calendar = array();
							}
			
							$lessons[$lessonID]->calendar[$dateKey][$blockKey]["lessonData"] = clone $lessonValue;
						}
					}
				}
			}
						
			$schedulearr["Lessons"] = $lessons;
						
// 			$schedulearr["Lessons"] = $activeScheduleLessons;
// 			$schedulearr["Calendar"] = $activeScheduleCalendar;
		}
				
		$schedulearr["UserSchedule.load"]["respChanges"] = $model->executeTask("UserSchedule.load", array("username" => "respChanges"));
				
		$schedulearr["ScheduleDescription.load"]->data = $activeSchedule; 

		$schedulearr["TreeView.load"] = $model->executeTask("TreeView.load",
											array("departmentSemesterSelection" => $departmentSemesterSelection, "path" => $path, "hide" => true, "publicDefault" => $publicDefaultIDArray)
										);

		$this->startup = rawurlencode(json_encode($schedulearr));

		parent::display($tpl);
	}
}
