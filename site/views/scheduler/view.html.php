<?php

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

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
		$hasBackendAccess = $user->authorise("core.login.admin");
		$this->semesterID = $model->getSemesterID();
		$session =& JFactory::getSession();
		$session->set('scheduler_semID', $this->semesterID);
		$semAuthor = $model->getSemesterAuthor();
		$this->jsid = $model->getSessionID();
		$this->semAuthor = $semAuthor;
		$this->hasBackendAccess = $hasBackendAccess;

		$doc =& JFactory::getDocument();
		$doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/ext-all.css");
		$doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/MultiSelect.css");
		$doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/mySched/style.css");

		$schedulearr = array();

		$model = JModel::getInstance('Ajaxhandler', 'thm_organizerModel', array('ignore_request' => false));

		$schedulearr["Grid.load"] = $model->executeTask("Grid.load");

		$schedulearr["Events.load"] = $model->executeTask("Events.load");

		$schedulearr["UserSchedule.load"] = array();
		$schedulearr["UserSchedule.load"]["respChanges"] = $model->executeTask("UserSchedule.load", array("username"=>"respChanges"));

		$schedulearr["ScheduleDescription.load"] = $model->executeTask("ScheduleDescription.load");

		$schedulearr["UserSchedule.load"]["delta"] = $model->executeTask("UserSchedule.load", array("username"=>"delta"));

		$schedulearr["TreeView.load"] = array();
		$schedulearr["TreeView.load"]["doz"] = $model->executeTask("TreeView.load", array("type"=>"doz"));
		$schedulearr["TreeView.load"]["room"] = $model->executeTask("TreeView.load", array("type"=>"room"));
		$schedulearr["TreeView.load"]["clas"] = $model->executeTask("TreeView.load", array("type"=>"clas"));

		$schedulearr["TreeView.curiculumTeachers"] = $model->executeTask("TreeView.curiculumTeachers", array("type"=>"curtea"));

		$this->startup = rawurlencode(json_encode($schedulearr));

        parent::display($tpl);
    }
}
?>