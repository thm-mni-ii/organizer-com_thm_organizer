<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
class thm_organizersControllerScheduler_Application_Settings extends JController
{
	function display()
	{
		parent::display();
	}

	function __construct()
	{
		parent::__construct();
	}

	function save()
	{
		$model = $this->getModel('scheduler_application_settings');
		$result = $model->store();
		$this->setRedirect( 'index.php?option=com_thm_organizer&view=scheduler_application_settings', $result);
	}
}
?>
