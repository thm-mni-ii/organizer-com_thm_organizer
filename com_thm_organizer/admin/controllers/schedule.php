<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        controller schedule
 * @description performs access checks and makes calls for data manipulation
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined( '_JEXEC' ) or die;
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
class thm_organizersControllerschedule extends JController
{
	/**
	 * add
	 *
	 * redirects to the semester edit view to edit an existing schedule
	 */
	public function add()
	{
		if(!thm_organizerHelper::isAdmin('schedule')) thm_organizerHelper::noAccess ();
		JRequest::setVar( 'view', 'schedule_edit' );
		JRequest::setVar( 'scheduleID', '0' );
		parent::display();
	}

	/**
	 * edit
	 *
	 * redirects to the semester edit view to edit an existing schedule
	 */
	public function edit()
	{
		if(!thm_organizerHelper::isAdmin('schedule')) thm_organizerHelper::noAccess ();
		JRequest::setVar( 'view', 'schedule_edit' );
		parent::display();
	}

	/**
	 * apply
	 *
	 * adds or updates schedule information and redirects back to the schedule
	 * edit view for that schedule
	 */
	public function apply()
	{
		if(!thm_organizerHelper::isAdmin('schedule')) thm_organizerHelper::noAccess ();
		$model = $this->getModel('schedule');
		$result = $model->update();
		$scheduleID = JRequest::getInt('scheduleID');
		$url = "index.php?option=com_thm_organizer&view=schedule_edit&scheduleID=$scheduleID";
		if($result)
		{
			$msg = JText::_("COM_THM_ORGANIZER_SCH_CHANGE_SUCCESS");
			$this->setRedirect($url, $msg);
		}
		else
		{
			$msg = JText::_("COM_THM_ORGANIZER_SCH_CHANGE_FAIL");
			$url ="JRequest::getInt('scheduleID');";
			$this->setRedirect($url, $msg, 'error');
		}
	}

	/**
	 * update
	 *
	 * adds or updates schedule information and redirects to the schedule
	 * manager view
	 */
	public function save()
	{
		if(!thm_organizerHelper::isAdmin('schedule')) thm_organizerHelper::noAccess ();
		$model = $this->getModel('schedule');
		$result = $model->update();
		$scheduleID = JRequest::getInt('scheduleID');
		$url = "index.php?option=com_thm_organizer&view=schedule_manager";
		if($result)
		{
			$msg = JText::_("COM_THM_ORGANIZER_SCH_CHANGE_SUCCESS");
			$this->setRedirect($url, $msg);
		}
		else
		{
			$msg = JText::_("COM_THM_ORGANIZER_SCH_CHANGE_FAIL");
			$url ="JRequest::getInt('scheduleID');";
			$this->setRedirect($url, $msg, 'error');
		}
	}

	/**
	 * upload
	 *
	 * performs access checks and uses the model's upload function to save the
	 * file to the database and perform consistency checks
	 */
	public function upload()
	{
		if(!thm_organizerHelper::isAdmin('schedule')) thm_organizerHelper::noAccess ();
		$url = "index.php?option=com_thm_organizer&view=schedule_edit";
		$fileType = $_FILES['file']['type'];
		if($fileType == "text/xml")
		{
			$model = $this->getModel('schedulexml');
			$problems = $model->validate();
			if(isset($problems['errors']))//critical inconsistancies -> no upload
			{
				$errorText = "<h3>".JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_ERRORS").":</h3>";
				$msg = $errorText.$problems['errors'];
				if(isset($problems['warnings']))//minor inconsistancies
				{
					$warningText = "<br /><h4>".JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_ERRORS_WARNINGS").":</h4>";
					$msg .= $warningText.$problems['warnings'];
				}
				$this->setRedirect($url, $msg, 'error');
			}
			$result = $model->upload();
			if($result)//upload successful
			{
				$url .= "&scheduleID=$result";
				if(isset($problems['warnings']))//minor inconsistancies
				{
					$warningText =  "<h4>".JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_WARNINGS").":</h4>";
					$msg = $warningText.$problems['warnings'];
					$this->setRedirect($url, $msg, 'notice');
				}
				else
					$this->setRedirect($url, JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_SUCCESS"));
			}
			else//db error
				$this->setRedirect($url, JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_FAIL"), 'error');
		}
		else
		{
			$msg = JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_TYPE_FAIL");
			$this->setRedirect($url, $msg, 'error');
		}
	}

	/**
	 * delete
	 *
	 * performs access checks, removes schedules from the database, and
	 * redirects to the schedule manager view optionally to filtered to a
	 * specific semester
	 */
	public function delete()
	{
		if(!thm_organizerHelper::isAdmin('schedule')) thm_organizerHelper::noAccess ();

		$semesterID = JRequest::getVar('semesterID');
		$url = "index.php?option=com_thm_organizer&view=schedule_manager";
		$url .= ($semesterID)? "&semesterID=$semesterID" : "";

		$dbo = JFactory::getDbo();
		$dbo->transactionStart();
		$scheduleIDs = JRequest::getVar('cid', array(), 'post', 'array');
		JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');
		$schedule = JTable::getInstance('schedules', 'thm_organizerTable');
		foreach($scheduleIDs as $scheduleID)
		{
			$schedule->load($scheduleID);
			switch ($schedule->plantypeID) {
				case '1':
					$model = $this->getModel("schedulexml"); break;
				case '2':
					$model = $this->getModel("schedulecsv"); break;
			}
			if($schedule->active)$model->deactivate($schedule->sid);
			$model->delete($scheduleID);
		}
		if($dbo->getErrorNum())
		{
			$dbo->transactionRollback();
			$msg = JText::_("COM_THM_ORGANIZER_SCH_DELETE_FAIL");
			$this->setRedirect($url, $msg, 'error');
		}
		else
		{
			$dbo->transactionCommit();
			$msg = JText::_("COM_THM_ORGANIZER_SCH_DELETE_SUCCESS");
			$this->setRedirect($url, $msg);
		}
	}

	/**
	 * setDefault
	 *
	 * performs access checks, activates/deactivates the chosen schedule in the
	 * context of its planning period, and redirects to the schedule manager
	 * view optionally to filtered to a specific semester
	 */
	public function setDefault()
	{
		if(!thm_organizerHelper::isAdmin('schedule')) thm_organizerHelper::noAccess ();
		$semesterID = JRequest::getVar('semesterID');
		$url = "index.php?option=com_thm_organizer&view=schedule_manager";
		$url .= ($semesterID)? "&semesterID=$semesterID" : "";

		if(JRequest::getInt("boxchecked") > 1)
		{
			$model = $this->getModel('schedule');
			$activation_conflicts = $model->checkForActivationConflicts();
			if($activation_conflicts)
				$this->setRedirect($url, JText::_("COM_THM_ORGANIZER_SCH_ACTIVATE_COUNT_FAIL"), 'error');
		}
		$this->activate();
	}

	private function activate()
	{
		$semesterID = JRequest::getVar('semesterID');
		$url = "index.php?option=com_thm_organizer&view=schedule_manager";
		$url .= ($semesterID)? "&semesterID=$semesterID" : "";
		$scheduleIDs = JRequest::getVar('cid', array(), 'post', 'array');

		$return = array();
		$return['errors'] = array();
		$return['messsages'] = array();
		JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');
		$schedule = JTable::getInstance('schedules', 'thm_organizerTable');
		foreach($scheduleIDs as $scheduleID)
		{
			$dbo = JFactory::getDbo();
			$dbo->transactionStart();
			$success = $schedule->load($scheduleID);
			if($success)
			{
				switch ($schedule->plantypeID)
				{
					case '1':
						$model = $this->getModel('schedulexml');
						($schedule->active)?
						$model->deactivate($schedule->sid, $return, true): $model->activate($schedule, $return);
						break;
					case '2':
						$model = $this->getModel('schedulecsv');
						($schedule->active)?
						$model->deactivate($schedule->sid, $return, true) : $model->activate($schedule, $return);
						break;
					default:
						break;
				}
			}
			else
				$return['errors'][] = JText::_("COM_THM_ORGANIZER_SCH_ACTIVATE_FIND_FAIL");
			if($dbo->getErrorNum())
			{
				$dbo->transactionRollback();
				$return['errors'][]= JText::_('COM_THM_ORGANIZER_SCH_ACTIVATE_DB_FAIL');
				break;
			}
			else $dbo->transactionCommit();
		}
		$msg = "";
		if(count($return['errors']))
			$msg .= "<br />".implode("<br />", $return['errors']);
		if(count($return['messages']))
			$msg .= "<br />".implode("<br />", $return['messages']);
		if(count($return['errors'])) $this->setRedirect($url, $msg, 'error');
		else $this->setRedirect($url, $msg);
	}

	/**
	 * cancel
	 *
	 * performs access checks and redirects to the schedule manager view
	 * optionally to filtered to a specific semester
	 */
	public function cancel()
	{
		if(!thm_organizerHelper::isAdmin('schedule')) thm_organizerHelper::noAccess ();
		$semesterID = JRequest::getVar('semesterID');
		$url = "index.php?option=com_thm_organizer&view=schedule_manager";
		$url .= ($semesterID)? "&semesterID=$semesterID" : "";
		$this->setRedirect($url);
	}


}
