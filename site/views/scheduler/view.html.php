<?php

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

/**
 * HTML View class for the Giessen Scheduler Component
 *
 * @package    Giessen Scheduler
 */

class thm_organizerViewScheduler extends JView
{
    function display($tpl = null)
    {
		JHTML::_('behavior.tooltip');
		$model = & $this->getModel();
		$user = & JFactory::getUser();
		$eventmodel = JModel::getInstance('event_list', 'thm_organizerModel', array('ignore_request' => false, 'display_type'=>4));
		$canWriteEvents = $eventmodel->canWrite;
		$this->canWriteEvents = $canWriteEvents;
		$this->jsid = $model->getSessionID();
		$menuparams = JFactory::getApplication()->getParams();

		$menuparamsID = $menuparams->get("id");

		$path = explode("/", $menuparamsID);

		$sid = explode(".", $path[0]);

		$sid = str_replace("semesterjahr", "", $sid[0]);

		$session =& JFactory::getSession();
		$session->set('scheduler_semID', $sid);

		$this->semesterID = $model->getSemesterID();
		$semAuthor = $model->getSemesterAuthor();
		$this->semAuthor = $semAuthor;

		$doc =& JFactory::getDocument();
		$doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/ext-all.css");
		//$doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/MultiSelect.css");
		$doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/mySched/style.css");

		$schedulearr = array();

		$model = JModel::getInstance('Ajaxhandler', 'thm_organizerModel', array('ignore_request' => false));

		$schedulearr["Grid.load"] = $model->executeTask("Grid.load");

		$schedulearr["Events.load"] = $model->executeTask("Events.load");

		$schedulearr["UserSchedule.load"] = array();

		$schedulearr["UserSchedule.load"]["respChanges"] = $model->executeTask("UserSchedule.load", array("username"=>"respChanges"));

		$schedulearr["ScheduleDescription.load"] = $model->executeTask("ScheduleDescription.load");

		$schedulearr["TreeView.load"] = $model->executeTask("TreeView.load", array("path"=>$path, "hide"=>true));

		if($user->id !== null && $user->id !== 0)
			$schedulearr["UserSchedule.load"] = $model->executeTask("UserSchedule.load", array("username"=>$user->name, "sid"=>$sid));

		$schedulearr["UserSchedule.load"]["delta"] = $model->executeTask("UserSchedule.load", array("username"=>"delta".$sid));

		$this->startup = rawurlencode(json_encode($schedulearr));

        parent::display($tpl);
    }
}
?>