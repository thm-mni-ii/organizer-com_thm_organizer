<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        controller category
 * @description performs user access checks and redirects for categories
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
class thm_organizersControllerCategory extends JController
{
	public function display(){
		parent::display();
	}

	public function __construct()
	{
		parent::__construct();
		$this->registerTask( 'add', 'edit' );
	}

	/**
	 * edit
	 *
	 * redirects to the category_edit view
	 */
	public function edit()
	{
		if(!thm_organizerHelper::isAdmin('category')) thm_organizerHelper::noAccess ();
		JRequest::setVar( 'view', 'category_edit' );
		parent::display();
	}

	/**
	 * save
	 *
	 * saves changes made to the category and redirects to the category_manager
	 * view
	 */
	public function save()
	{
		if(!thm_organizerHelper::isAdmin('category')) thm_organizerHelper::noAccess ();
		$model = $this->getModel('category');
		$result = $model->save();
		if($result)
		{
			$msg = JText::_("COM_THM_ORGANIZER_CAT_SAVE_SUCCESS");
			$this->setRedirect( 'index.php?option=com_thm_organizer&view=category_manager', $msg);
		}
		else
		{
			$msg = JText::_("COM_THM_ORGANIZER_CAT_SAVE_FAIL");
			$this->setRedirect( 'index.php?option=com_thm_organizer&view=category_manager', $msg, 'error');
		}
	}

	/**
	 * save2new
	 *
	 * saves changes made to the category and redirects to a new category
	 * creation form
	 */
	public function save2new()
	{
		if(!thm_organizerHelper::isAdmin('category')) thm_organizerHelper::noAccess ();
		$model = $this->getModel('category');
		$result = $model->save();
		if($result)
		{
			$msg = JText::_("COM_THM_ORGANIZER_CAT_SAVE_SUCCESS");
			$this->setRedirect( 'index.php?option=com_thm_organizer&view=category_edit', $msg);
		}
		else
		{
			$msg = JText::_("COM_THM_ORGANIZER_CAT_SAVE_FAIL");
			$this->setRedirect( 'index.php?option=com_thm_organizer&view=category_edit', $msg, 'error');
		}
	}


	public function delete()
	{
		if(!thm_organizerHelper::isAdmin('category')) thm_organizerHelper::noAccess ();
		$model = $this->getModel('category');
		$result = $model->delete();
		if($result)
		{
			$msg = JText::_("COM_THM_ORGANIZER_CAT_DELETE_SUCCESS");
			$this->setRedirect( 'index.php?option=com_thm_organizer&view=category_manager', $msg);
		}
		else
		{
			$msg = JText::_("COM_THM_ORGANIZER_CAT_DELETE_FAIL");
			$this->setRedirect( 'index.php?option=com_thm_organizer&view=category_manager', $msg, 'error');
		}
	}

	public function cancel(){
		$this->setRedirect( 'index.php?option=com_thm_organizer&view=category_manager' );
	}
}