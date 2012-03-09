<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
class thm_organizersControllerSettings extends JController
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
		$model = $this->getModel('settings');
		$result = $model->store();
		$this->setRedirect( 'index.php?option=com_thm_organizer&view=settings', $result);
	}
}
?>
