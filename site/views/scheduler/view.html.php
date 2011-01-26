<?php

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Giessen Scheduler Component
 *
 * @package    Giessen Scheduler
 */

class GiessenSchedulerViewScheduler extends JView
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
		$jsid = $model->getSessionID();
		$this->jsid = $jsid;
		$this->semAuthor = $semAuthor;
		$this->hasBackendAccess = $hasBackendAccess;

        parent::display($tpl);
    }
}
?>