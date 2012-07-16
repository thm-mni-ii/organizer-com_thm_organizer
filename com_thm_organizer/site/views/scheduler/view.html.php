<?php

/**
 *@category    Joomla component
 *
 *@package     THM_Organizer
 *
 *@subpackage  com_thm_organizer.site
 *@name		   thm_organizerViewScheduler
 *@description thm_organizerViewScheduler file from com_thm_organizer
 *@author	   Wolf Rost, wolf.rost@mni.thm.de
 *
 *@copyright   2012 TH Mittelhessen
 *
 *@license     GNU GPL v.2
 *@link		   www.mni.thm.de
 *@version	   1.0
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the THM Organizer Component
 *
 * @package  Joomla.site
 * @since    1.5
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

		$schedulearr["Grid.load"] = $model->executeTask("Grid.load");

		$schedulearr["Events.load"] = $model->executeTask("Events.load");

		$schedulearr["UserSchedule.load"] = array();

		$schedulearr["UserSchedule.load"]["respChanges"] = $model->executeTask("UserSchedule.load", array("username" => "respChanges"));

		$schedulearr["ScheduleDescription.load"] = $model->executeTask("ScheduleDescription.load");

		$schedulearr["TreeView.load"] = $model->executeTask("TreeView.load",
											array("path" => $path, "hide" => true, "publicDefault" => $publicDefaultIDArray)
										);

		$this->startup = rawurlencode(json_encode($schedulearr));

		parent::display($tpl);
	}
}
