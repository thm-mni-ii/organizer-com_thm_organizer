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

    $showSchedule = null;
    $this->searchModuleID = null;
    $semesterID = null;

    $showSchedule = JRequest::getString('showSchedule');
    $this->searchModuleID = JRequest::getString('moduleID');
    $this->LSFisAvailable = $model->getLSFStatus("com_thm_lsf");

    if(!empty($showSchedule) && !empty($this->searchModuleID)) //Aufruf ohne Menüparameter
    {
	    $showScheduleArray = explode(".", $showSchedule);
	    $semesterID = $showScheduleArray[0];
	    $treePath = $semesterID.".".$showScheduleArray[1];
    	$path[$treePath] = "intermediate";
		$publicDefaultIDArray = array($showSchedule=>"default");
    }
    else //im Menü eingebunden
    {
    	$menuparams = JFactory::getApplication()->getParams();
    	$menuparamsID = $menuparams->get("id");
    	$menuparamsPublicDefaultID = $menuparams->get("publicDefaultID");
		try{
        	$path = (array)json_decode($menuparamsID);
      	}
      	catch(Exception $e)
      	{
        	$path = array();
      	}
      	try{
        	$publicDefaultIDArray = (array)json_decode($menuparamsPublicDefaultID);
      	}
      	catch(Exception $e)
      	{
        	$publicDefaultIDArray = array();
      	}
		$firstValue = each($path);
	    $semesterID = explode(".", $firstValue["key"]);
	    $semesterID = $semesterID[0];
    }

    $this->semesterID = $semesterID;
    $this->semAuthor = $model->getSemesterAuthor($semesterID);

    $doc =& JFactory::getDocument();
    $doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/ext-all-gray.css");
    //$doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/MultiSelect.css");
    $doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/mySched/style.css");

    $schedulearr = array();

    $model = JModel::getInstance('Ajaxhandler', 'thm_organizerModel', array('ignore_request' => false));

    $schedulearr["Grid.load"] = $model->executeTask("Grid.load");

    $schedulearr["Events.load"] = $model->executeTask("Events.load");

    $schedulearr["UserSchedule.load"] = array();

    $schedulearr["UserSchedule.load"]["respChanges"] = $model->executeTask("UserSchedule.load", array("username"=>"respChanges"));

    $schedulearr["ScheduleDescription.load"] = $model->executeTask("ScheduleDescription.load");

    $schedulearr["TreeView.load"] = $model->executeTask("TreeView.load", array("path"=>$path, "hide"=>true, "publicDefault"=>$publicDefaultIDArray));

    $this->startup = rawurlencode(json_encode($schedulearr));

        parent::display($tpl);
    }
}
?>