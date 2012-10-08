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
				
		$activeSchedule = $model->getActiveSchedule();
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
				return JError::raiseWarning(404, JText::_('Fehlerhfte Daten'));
			}
		}
		else
		{
			return JError::raiseWarning(404, JText::_('Kein aktiver Stundenplan'));
		}
		
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
		$this->CurriculumisAvailable = $model->getComStatus("com_thm_organizer");

		if (!empty($showSchedule) && !empty($this->searchModuleID)) // Aufruf ohne Menüparameter
		{
			$showScheduleArray = explode(".", $showSchedule);
			$semesterID = $showScheduleArray[0];
			$treePath = $semesterID . "." . $showScheduleArray[1];
			$path[$treePath] = "intermediate";
			$publicDefaultIDArray = array($showSchedule => "default");
		}
		else // Im Menü eingebunden
		{
			$path = null;
			$menuparams = JFactory::getApplication()->getParams();
			$menuparamsID = $menuparams->get("id");
			$menuparamsPublicDefaultID = $menuparams->get("publicDefaultID");
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
			$firstValue = each($path);
			$semesterID = explode(".", $firstValue["key"]);
			$semesterID = $semesterID[0];
		}

		$this->semesterID = $semesterID;
		$this->semAuthor = $model->getSemesterAuthor($semesterID);

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
		
// 		var_dump($activeScheduleLessons);

		$schedulearr["Calendar"] = $activeScheduleCalendar;
		
		$schedulearr["Events.load"] = $model->executeTask("Events.load");

		$schedulearr["UserSchedule.load"] = array();

		$schedulearr["UserSchedule.load"]["respChanges"] = $model->executeTask("UserSchedule.load", array("username" => "respChanges"));
				
		$schedulearr["ScheduleDescription.load"]->data = $activeSchedule; 

		$schedulearr["TreeView.load"] = $model->executeTask("TreeView.load",
											array("path" => $path, "hide" => true, "publicDefault" => $publicDefaultIDArray)
										);

		$this->startup = rawurlencode(json_encode($schedulearr));

		parent::display($tpl);
	}
}
