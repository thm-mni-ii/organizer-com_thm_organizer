<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerMappings
 * @description THM_OrganizerControllerMappings component admin controller
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controlleradmin');

/**
 * Class THM_OrganizerControllerMappings for component com_thm_organizer
 * Class provides methods perform actions for mappings
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerControllerMappings extends JControllerAdmin
{
	/**
	 * Method to get the model
	 *
	 * @param   String  $name    Name	 (default: 'Mappings')
	 * @param   String  $prefix  Prefix  (default: 'THM_OrganizerModel')
	 *
	 * @return  Object
	 */
	public function getModel($name = 'Mappings', $prefix = 'THM_OrganizerModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * AJAX-callback which returns all related semester-ids from a given asset-id
	 *
	 * @return  void
	 */
	public function getSemester()
	{
		$mainframe = JFactory::getApplication();
		$assetID = JRequest::getVar('id');
		$majorId = $_SESSION['stud_id'];

		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);
		$query->select("semesters_majors_id");
		$query->from('#__thm_organizer_assets_tree AS at');
		$query->join('#__thm_organizer_assets_semesters AS asem ON at.id = asem.assets_tree_id');
		$query->join('#__thm_organizer_semesters_majors AS sm ON asem.semesters_majors_id = sm.id');
		$query->where("asset = '$assetID'");
		$query->where("major_id = '$majorId'");
		$dbo->setQuery((string) $query);
		$rows = $dbo->loadColumn();

		echo implode(', ', $rows);

		$mainframe->close();
	}

	/**
	 * Method to get the asset record
	 *
	 * @return  void
	 */
	public function getAssetRecord()
	{
		$mainframe = JFactory::getApplication();

		$assetID = JRequest::getVar('id');
		$dbo = JFactory::getDBO();

		$query = $dbo->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_assets');
		$query->where("id = $assetID");
		$dbo->setQuery($query);
		$rows = $dbo->loadObjectList();

		// Return the string which includes the determined semester ids
		echo json_encode($rows[0]);

		$mainframe->close();
	}

	/**
	 * Method to perform cancel
	 *
	 * @return  void
	 */
	public function cancel()
	{
		$this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=majors", false));
	}

	/**
	 * Method to perform saveorder
	 *
	 * @return  void
	 */
	public function saveorder()
	{
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$majorId = $_SESSION['stud_id'];

		parent::saveorder();
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&id=' . $majorId . '&view=' . $this->view_list, false));
	}

	/**
	 * Method to perform reorder
	 *
	 * @return  Boolean
	 */
	public function reorder()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$majorId = $_SESSION['stud_id'];

		// Initialise variables
		$ids = JRequest::getVar('cid', null, 'post', 'array');
		$inc = ($this->getTask() == 'orderup') ? -1 : + 1;

		$model = $this->getModel();
		$return = $model->reorder($ids, $inc);

		if ($return === false)
		{
			// Reorder failed
			$message = JText::sprintf('JLIB_APPLICATION_ERROR_REORDER_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false), $message, 'error');
			return false;
		}
		else
		{
			// Reorder succeeded
			$message = JText::_('JLIB_APPLICATION_SUCCESS_ITEM_REORDERED');
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&id=' . $majorId . '&view=' . $this->view_list, false), $message);
			return true;
		}
	}
}
