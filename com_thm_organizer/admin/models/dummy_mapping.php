<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerModelDummy_Mapping
 * @description THM_OrganizerModelDummy_Mapping component admin model
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * Class THM_OrganizerModelDummy_Mapping for component com_thm_organizer
 *
 * Class provides methods for dummy mapping
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerModelDummy_Mapping extends JModelAdmin
{
	/**
	 * Method to get the table
	 *
	 * @param   Integer  $assetId    Asset id
	 * @param   Array    $semesters  Semesters
	 *
	 * @return  void
	 */
	public function adjustParentAssets($assetId, $semesters)
	{
		$db = JFactory::getDBO();
		$id = $_SESSION['stud_id'];

		$query = $db->getQuery(true);
		$query->select("*");
		$query->select("#__thm_organizer_assets_tree.id as asset_tree_id");
		$query->from('#__thm_organizer_assets_tree');
		$query->join('inner', '#__thm_organizer_assets_semesters ' .
				'ON #__thm_organizer_assets_tree.id = #__thm_organizer_assets_semesters.assets_tree_id');
		$query->join('inner', '#__thm_organizer_semesters_majors ' .
				'ON #__thm_organizer_semesters_majors.id = #__thm_organizer_assets_semesters.semesters_majors_id');
		$query->where("#__thm_organizer_assets_tree.parent_id = $assetId");
		$query->where("#__thm_organizer_semesters_majors.major_id= $id");
		$db->setQuery($query);
		$children = $db->loadAssocList();

		if (count($children) > 0)
		{
			foreach ($children as $row)
			{
				$asset_id = $row['asset_tree_id'];
				$semesters_majors_id = $row['semesters_majors_id'];

				if (!in_array($row->semesters_majors_id, $semesters))
				{
					// Build the query
					$query = $db->getQuery(true);
					$query->delete("#__thm_organizer_assets_semesters");
					$query->where("assets_tree_id = $asset_id");
					$query->where("semesters_majors_id = $semesters_majors_id");
					$db->setQuery($query);
					$db->query($query);
				}

				foreach ($semesters as $semester)
				{
					$semester_id = $semester['semesters_majors_id'];

					// Maps the actual asset to a additional semester
					$query = $db->getQuery(true);
					$query->insert('#__thm_organizer_assets_semesters');
					$query->set("assets_tree_id = $asset_id");
					$query->set("semesters_majors_id = $semester");
					$db->setQuery($query);
					$db->query();

				}

				self::adjustParentAssets($row['asset'], $semesters);
			}
		}
	}

	/**
	 * Method to get the max ordering
	 *
	 * @param   Integer  $parent  Parent id
	 * @param   Integer  $major   Major id
	 *
	 * @return  Object
	 */
	public function getMaxOrdering($parent, $major)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("MAX(ordering) as max_ordering");
		$query->from('#__thm_organizer_assets_tree');
		$query->join('inner', '#__thm_organizer_assets_semesters ' .
				'ON #__thm_organizer_assets_tree.id = #__thm_organizer_assets_semesters.assets_tree_id');
		$query->join('inner', '#__thm_organizer_semesters_majors ' .
				'ON #__thm_organizer_semesters_majors.id = #__thm_organizer_assets_semesters.semesters_majors_id');
		$query->where("#__thm_organizer_assets_tree.parent_id = $parent");
		$query->where("#__thm_organizer_semesters_majors.major_id= $major");
		$db->setQuery($query);
		$rows = $db->loadAssocList();

		return $rows[0]['max_ordering'];
	}

	/**
	 * Method to overwrite the save method
	 *
	 * @param   Array  $data  Data
	 *
	 * @return  Boolean
	 */
	public function save($data)
	{
		$db = JFactory::getDbo();
		$stud_id = $_SESSION['stud_id'];

		if ($data['parent_id'] == 0)
		{

			self::adjustParentAssets($data['asset'], JRequest::getVar('semesters'));
		}

		$ordering = self::getMaxOrdering($data['parent_id'], $stud_id) + 1;

		// Save the POST data to the mapping table
		if (JRequest::getVar('id'))
		{
			$asset = $data['asset'];
			$parent_id = $data['parent_id'];
			$color = $data['color_id'];
			$ecollap = $data['ecollaboration_link'];
			$menu = $data['menu_link'];

			$id = JRequest::getVar('id');

			// Wir erstellen einen neuen Query
			$sql = $db->getQuery(true);
			$sql->update("#__thm_organizer_assets_tree");
			$sql->set('color_id=' . $color);
			$sql->set("asset= $asset");
			$sql->set("parent_id= $parent_id");
			$sql->set("ecollaboration_link= '$ecollap'");
			$sql->set("menu_link= '$menu'");
			$sql->where("id= $id");

			$db->setQuery((string) $sql);
			$db->query();

		}
		else
		{
			$asset = $data['asset'];
			$parent_id = $data['parent_id'];
			$color = $data['color_id'];
			$ecollap = $data['ecollaboration_link'];
			$menu = $data['menu_link'];

			// Wir erstellen einen neuen Query
			$sql = $db->getQuery(true);
			$sql->insert("#__thm_organizer_assets_tree");
			$sql->set('color_id=' . $color);
			$sql->set("asset= $asset");
			$sql->set("parent_id= $parent_id");
			$sql->set("ordering= $ordering");
			$sql->set("ecollaboration_link= '$ecollap'");
			$sql->set("menu_link= '$menu'");

			$db->setQuery((string) $sql);
			$db->query();
		}

		// Get the last inserted id from the previous stored row
		$insertid = $db->insertid();

		// Get the post data
		$semesters = JRequest::getVar('semesters');

		// Edit of an existent row
		if (JRequest::getVar('id'))
		{
			// Get the current id of the edited asset
			$insertid = JRequest::getVar('id');

			// Determine all mapped semesters of this asset
			$query = $db->getQuery(true);
			$query->select("*");
			$query->from("#__thm_organizer_assets_semesters");
			$query->where("assets_tree_id = $insertid");
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			// Iterate over each found mapping
			foreach ($rows as $row)
			{
				// Delete the mapping if the current mapping isn't part of the actual post data
				if (!in_array($row->stud_sem_id, $semesters))
				{
					// Build the query
					$query = $db->getQuery(true);
					$query->delete("#__thm_organizer_assets_semesters");
					$query->where("assets_tree_id = $insertid");
					$query->where("semesters_majors_id = $row->semesters_majors_id");
					$db->setQuery($query);
					$db->query($query);
				}
			}
		}
		else
		{
			$db = JFactory::getDbo();
			$insertid = $db->insertid();
		}

		// Iterate over each semester of the post request
		foreach ($semesters as $semester)
		{
			// Maps the actual asset to a additional semester
			$query = $db->getQuery(true);
			$query->insert('#__thm_organizer_assets_semesters');
			$query->set("assets_tree_id = $insertid");
			$query->set("semesters_majors_id = $semester");
			$db->setQuery($query);
			$db->query();
		}

		// Refresh all lineages and depth level of the assets tree of the selected major
		self::setLineage();

		return true;
	}

	/**
	 * Method to determine the path of the given tree node
	 *
	 * @param   Integer  $node  Node
	 *
	 * @return  mixed
	 */
	public function get_path($node)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Get the selected major id
		$stud_sem_id = $_SESSION['stud_id'];

		// Determine all node by a given asset id
		$query->select("*");
		$query->from("#__thm_organizer_assets_tree");
		$query->join('inner', '#__thm_organizer_assets_semesters ' .
				'ON #__thm_organizer_assets_semesters.assets_tree_id = #__thm_organizer_assets_tree.id');
		$query->join('inner', '#__thm_organizer_semesters_majors ' .
				'ON #__thm_organizer_semesters_majors.id = #__thm_organizer_assets_semesters.semesters_majors_id');
		$query->where("asset = $node");
		$query->where("major_id = $stud_sem_id");
		$db->setQuery($query);
		$row = $db->loadAssocList();

		// This array will contain the actual path
		$path = array();

		// Builds the current paths and return it
		if ($row[0]['parent_id'] != null)
		{
			$path[] = $row[0]['parent_id'] . "/";

			// Find all parent nodes recursively
			$path = array_merge(self::get_path($row[0]['parent_id']), $path);
		}
		else
		{
			$path[] = "/";
		}
		return $path;
	}

	/**
	 * Method to set the lineage
	 *
	 * @return  void
	 */
	public function setLineage()
	{
		// Get the current major id
		$stud_sem_id = $_SESSION['stud_id'];

		// Select the tree of the current major
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select("*");
		$query->from("#__thm_organizer_assets_tree");
		$query->join('inner', '#__thm_organizer_assets_semesters ' .
				'ON #__thm_organizer_assets_semesters.assets_tree_id = #__thm_organizer_assets_tree.id');
		$query->join('inner', '#__thm_organizer_semesters_majors ' .
				'ON #__thm_organizer_semesters_majors.id = #__thm_organizer_assets_semesters.semesters_majors_id');
		$query->where("asset <> 0");
		$query->where("major_id = $stud_sem_id");
		$query->group(" asset");

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Iterate over each node of the tree
		foreach ($rows as $row)
		{
			// Determine the path and depth level of the current node
			$depth = count(self::get_path($row->asset)) - 1;
			$path = implode(self::get_path($row->asset));

			// Write it to the database
			$query = "UPDATE #__thm_organizer_assets_tree JOIN #__thm_organizer_assets_semesters ' .
					'ON #__thm_organizer_assets_semesters.assets_tree_id = #__thm_organizer_assets_tree.id" .
					" JOIN #__thm_organizer_semesters_majors ' .
					'ON #__thm_organizer_semesters_majors.id = #__thm_organizer_assets_semesters.semesters_majors_id" .
					" SET lineage = '$path', depth = $depth WHERE asset = $row->asset AND major_id = $stud_sem_id";

			echo (String) $query;

			$db->setQuery($query);
			$db->query();
		}
	}

	/**
	 * Method to get the table
	 *
	 * @param   String  $type    Type  			(default: 'mapping')
	 * @param   String  $prefix  Prefix  		(default: 'THM_OrganizerTable')
	 * @param   Array   $config  Configuration  (default: 'Array')
	 *
	 * @return  JTable object
	 */
	public function getTable($type = 'mapping', $prefix = 'THM_OrganizerTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the form
	 *
	 * @param   Array    $data      Data  	   (default: Array)
	 * @param   Boolean  $loadData  Load data  (default: true)
	 *
	 * @return  A Form object
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_thm_organizer.dummy_mapping', 'dummy_mapping', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	/**
	 * Method to get the form
	 *
	 * @return  A Form object
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_thm_organizer.edit.dummy_mapping.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
	}

	/**
	 * Method to overwrite publish method. Publishs a given tree node
	 *
	 * @return  Boolean
	 */
	public function publish()
	{
		// Get the post data
		$cid = JRequest::getVar('cid', array(), '', 'array');

		// Create the update sql statement
		$query = "UPDATE `#__thm_organizer_assets_tree` SET `published` = '1' WHERE `id` =";

		// Iterate over each tree node, if multiple node were selected
		foreach ($cid as $id)
		{
			$this->_db->setQuery($query . $id);
			if (!$this->_db->query())
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Method to overwrite unpublish method. Unpublishs a given tree node
	 *
	 * @return  Boolean
	 */
	public function unpublish()
	{
		// Get the post data
		$cid = JRequest::getVar('cid', array(), '', 'array');

		// Create the update sql statement
		$query = "UPDATE `#__thm_organizer_assets_tree` SET `published` = '0' WHERE `id` =";

		// Iterate over each tree node, if multiple node were selected
		foreach ($cid as $id)
		{
			$this->_db->setQuery($query . $id);

			if (!$this->_db->query())
			{
				return false;
			}
		}
		return true;
	}
}
