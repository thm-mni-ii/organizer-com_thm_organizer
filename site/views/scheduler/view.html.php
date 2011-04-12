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
		$hasBackendAccess = $user->authorise("core.login.admin");
		$this->jsid = $model->getSessionID();
		$this->hasBackendAccess = $hasBackendAccess;
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
		$doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/MultiSelect.css");
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

//		foreach($path as $value)
//		{
//			$temp = $this->search($value, $schedulearr["TreeView.load"]["data"]["tree"]);
//			if($temp !== false)
//				$schedulearr["TreeView.load"]["data"]["tree"] = $temp;
//			else
//				break;
//		}

		$this->startup = rawurlencode(json_encode($schedulearr));

        parent::display($tpl);
    }

    private function search($needle, $array)
    {
    	if(!is_array($array))
    		if(is_array($array->children))
    			$array = $array->children;
    		else
    			return false;
		foreach($array as $value)
		{
			if(isset($value->id))
			{
				if($value->id === $needle)
				{
					return $value;
				}
			}
		}
		return false;
    }

}
?>