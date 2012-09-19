<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerMappings
 * @description THM_OrganizerControllerMappings component admin controller
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla controllerform library
jimport('joomla.application.component.controlleradmin');

/**
 * Class THM_OrganizerControllerMappings for component com_thm_organizer
 *
 * Class provides methods perform actions for mappings
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
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
		$id = JRequest::getVar('id');
		$db = JFactory::getDBO();

		// Get the current selected major-id
		$majorId = $_SESSION['stud_id'];

		// Get the semester-ids from the database
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_assets_tree');
		$query->join('inner', '#__thm_organizer_assets_semesters ' .
				'ON #__thm_organizer_assets_tree.id = #__thm_organizer_assets_semesters.assets_tree_id');
		$query->join('inner', '#__thm_organizer_semesters_majors ' .
				'ON #__thm_organizer_assets_semesters.semesters_majors_id = #__thm_organizer_semesters_majors.id');
		$query->where("asset = $id");
		$query->where("major_id = $majorId");

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Determine the first and last row, in order to attach the correct label strings
		$foundSemesters = null;
		$last_item = end($rows);
		$last_item = each($rows);
		reset($rows);

		// Iterate over the found semesters
		foreach ($rows as $key => $value)
		{
			$foundSemesters .= $value->semesters_majors_id;

			// Seperate the values with commas
			if ($value != $last_item['value'] && $key != $last_item['key'])
			{
				$foundSemesters .= ', ';
			}
		}

		// Return the string which includes the determined semester ids
		echo $foundSemesters;

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

		$id = JRequest::getVar('id');
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_assets');
		$query->where("id = $id");
		$db->setQuery($query);
		$rows = $db->loadObjectList();

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

		// Get the arrays from the Request
		$order = JRequest::getVar('order', null, 'post', 'array');
		$originalOrder = explode(',', JRequest::getString('original_order_values'));

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
		$user = JFactory::getUser();
		$ids = JRequest::getVar('cid', null, 'post', 'array');
		$inc = ($this->getTask() == 'orderup') ? -1 : +1;

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
