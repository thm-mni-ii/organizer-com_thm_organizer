<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerControllerDummy_Mapping
 * @description THM_OrganizerControllerDummy_Mapping component admin controller
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');

/**
 * Class THM_OrganizerControllerDummy_Mapping for component com_thm_organizer
 *
 * Class provides methods perform actions for dummy_mapping
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
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
	 * @param   String  $prefix  Prefix  		(default: 'THM_OrganizerTable')
	 * @param   Array   $config  Configuration  (default: Array)
	 *
	 * @return  Object
	 */
	public function getTable($type = 'mapping', $prefix = 'THM_OrganizerTable', $config = array())
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
		$stud_id = $_SESSION['stud_id'];
		$dbo = JFactory::getDBO();

		$deleteQuery = $dbo->getQuery(true);
		$deleteQuery->delete('#__thm_organizer_assets_tree');
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		foreach ($cid as $id)
		{
			$deleteQuery->clear('where');
			$deleteQuery->where("id = '$id'");
			$dbo->setQuery((string) $deleteQuery);
			$dbo->query();
		}

		// Get the primary keys and ordering values for the selection.
		$assetQuery = $dbo->getQuery(true);
		$assetQuery->select('asset');
		$assetQuery->from('#__thm_organizer_assets_tree AS at');
		$assetQuery->innerJoin('#__thm_organizer_assets_semesters AS asem ON asem.assets_tree_id = at.id');
		$assetQuery->innerJoin('#__thm_organizer_semesters_majors AS sm ON asem.semesters_majors_id = sm.id');
		$assetQuery->where("semesters_majors.major_id = '$stud_id'");
		$dbo->setQuery((string) $assetQuery);
		$rows = $dbo->loadColumn();

		JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_thm_organizer' . DS . 'tables');
		$table = self::getTable();

		foreach ($rows as $row)
		{
			$table->reorder("parent_id = '{$row['asset']}'");
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
