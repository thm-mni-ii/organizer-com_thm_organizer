<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerDummy_Mapping
 * @description THM_OrganizerControllerDummy_Mapping component admin controller
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * Class THM_OrganizerControllerDummy_Mapping for component com_thm_organizer
 *
 * Class provides methods perform actions for dummy_mapping
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerControllerDummy_Mapping extends JControllerForm
{
	/**
	 * Method to perform save
	 *
	 * @param   Object  $key     Key		   (default: null)
	 * @param   Object  $urlVar  Url variable  (default: null)
	 *
	 * @return  void
	 */
	public function save($key = null, $urlVar = null)
	{
		// Get Major-ID from the current session
		$stud_id = $_SESSION['stud_id'];

		$retVal = parent::save($key, $urlVar);
		if ($retVal)
		{
			$this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=dummy_mappings&id=$stud_id", false));
		}
	}

	/**
	 * Method to perform cancel
	 *
	 * @return  void
	 */
	public function cancel()
	{
		// Get Major-ID from the current session
		$stud_id = $_SESSION['stud_id'];

		$retVal = parent::cancel();
		if ($retVal)
		{
			$this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=mappings&id=$stud_id", false));
		}
	}

	/**
	 * Method to get the table
	 *
	 * @param   String  $type    Type		    (default: 'mapping')
	 * @param   String  $prefix  Prefix  		(default: 'THM_curriculumTable')
	 * @param   Array   $config  Configuration  (default: Array)
	 *
	 * @return  Object
	 */
	public function getTable($type = 'mapping', $prefix = 'THM_curriculumTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to perform delete
	 *
	 * @return  void
	 */
	public function delete()
	{
		$db =& JFactory::getDBO();
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		$stud_id = $_SESSION['stud_id'];

		foreach ($cid as $id)
		{
			$query = 'DELETE FROM #__thm_curriculum_assets_tree'
			. ' WHERE id = ' . $id . ';';
			$db->setQuery($query);
			$db->query();
		}

		// Get the primary keys and ordering values for the selection.
		$query = $db->getQuery(true);

		// Select the primary key and ordering values from the table.
		$query->select('*');
		$query->from(' #__thm_curriculum_assets_tree as assets_tree');
		$query->join('inner', '#__thm_curriculum_assets_semesters as assets_semesters ON assets_semesters.assets_tree_id = assets_tree.id');
		$query->join('inner', '#__thm_curriculum_semesters_majors as semesters_majors ON assets_semesters.semesters_majors_id = semesters_majors.id');
		$query->where("semesters_majors.major_id =" . $stud_id);
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_thm_organizer' . DS . 'tables');
		$table = self::getTable();

		foreach ($rows as $row)
		{
			$table->reorder("parent_id=" . $row->asset);
		}

		$this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=dummy_mappings&id=$stud_id", false));
	}

	/**
	 * Method to publish selected Asset tree items
	 *
	 * @return  void
	 */
	public function publish()
	{
		// Get major id from the current session
		$stud_id = $_SESSION['stud_id'];

		$model = $this->getModel('mapping');
		$model->publish();
		$this->setRedirect("index.php?option=com_thm_organizer&view=mappings&id=$stud_id");
	}

	/**
	 * Method to unpublish selected asset tree items
	 *
	 * @return  void
	 */
	public function unpublish()
	{
		// Get Major-ID from the current session
		$stud_id = $_SESSION['stud_id'];

		$model = $this->getModel('mapping');
		$model->unpublish();
		$this->setRedirect("index.php?option=com_thm_organizer&view=mappings&id=$stud_id");
	}
}
