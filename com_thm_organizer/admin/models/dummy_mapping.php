<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelDummy_Mapping
 * @description THM_OrganizerModelDummy_Mapping component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Class THM_OrganizerModelDummy_Mapping for component com_thm_organizer
 * Class provides methods for dummy mapping
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
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
		$dbo = JFactory::getDBO();
		$majorID = $_SESSION['stud_id'];

		$query = $dbo->getQuery(true);
		$query->select("*");
		$query->select("#__thm_organizer_assets_tree.id as asset_tree_id");
		$query->from('#__thm_organizer_assets_tree AS at');
		$query->join('#__thm_organizer_assets_semesters AS asem ON at.id = asem.assets_tree_id');
		$query->join('#__thm_organizer_semesters_majors AS sm ON sm.id = asem.semesters_majors_id');
		$query->where("#__thm_organizer_assets_tree.parent_id = $assetId");
		$query->where("#__thm_organizer_semesters_majors.major_id= $majorID");
		$dbo->setQuery($query);
		$children = $dbo->loadAssocList();

		if (count($children) > 0)
		{
			foreach ($children as $row)
			{
				$asset_id = $row['asset_tree_id'];
				$semesters_majors_id = $row['semesters_majors_id'];

				if (!in_array($row->semesters_majors_id, $semesters))
				{
					// Build the query
					$query = $dbo->getQuery(true);
					$query->delete("#__thm_organizer_assets_semesters");
					$query->where("assets_tree_id = $asset_id");
					$query->where("semesters_majors_id = $semesters_majors_id");
					$dbo->setQuery($query);
					$dbo->query($query);
				}

				// Maps the actual asset to a additional semester
				foreach ($semesters as $semester)
				{
					$query = $dbo->getQuery(true);
					$query->insert('#__thm_organizer_assets_semesters');
					$query->set("assets_tree_id = $asset_id");
					$query->set("semesters_majors_id = $semester");
					$dbo->setQuery($query);
					$dbo->query();

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
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);
		$query->select("MAX(ordering) as max_ordering");
		$query->from('#__thm_organizer_assets_tree AS at');
		$query->join('#__thm_organizer_assets_semesters AS asem ON at.id = asem.assets_tree_id');
		$query->join('#__thm_organizer_semesters_majors AS sm ON sm.id = asem.semesters_majors_id');
		$query->where("#__thm_organizer_assets_tree.parent_id = $parent");
		$query->where("#__thm_organizer_semesters_majors.major_id= $major");
		$dbo->setQuery($query);
		$rows = $dbo->loadAssocList();

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
		$dbo = JFactory::getDbo();
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

			$assetTreeID = JRequest::getVar('id');

			// Wir erstellen einen neuen Query
			$sql = $dbo->getQuery(true);
			$sql->update("#__thm_organizer_assets_tree");
			$sql->set('color_id=' . $color);
			$sql->set("asset= $asset");
			$sql->set("parent_id= $parent_id");
			$sql->set("ecollaboration_link= '$ecollap'");
			$sql->set("menu_link= '$menu'");
			$sql->where("id= $assetTreeID");

			$dbo->setQuery((string) $sql);
			$dbo->query();

		}
		else
		{
			$asset = $data['asset'];
			$parent_id = $data['parent_id'];
			$color = $data['color_id'];
			$ecollap = $data['ecollaboration_link'];
			$menu = $data['menu_link'];

			// Wir erstellen einen neuen Query
			$sql = $dbo->getQuery(true);
			$sql->insert("#__thm_organizer_assets_tree");
			$sql->set('color_id=' . $color);
			$sql->set("asset= $asset");
			$sql->set("parent_id= $parent_id");
			$sql->set("ordering= $ordering");
			$sql->set("ecollaboration_link= '$ecollap'");
			$sql->set("menu_link= '$menu'");

			$dbo->setQuery((string) $sql);
			$dbo->query();
		}

		// Get the last inserted id from the previous stored row
		$insertid = $dbo->insertid();

		// Get the post data
		$semesters = JRequest::getVar('semesters');

		// Edit of an existent row
		if (JRequest::getVar('id'))
		{
			// Get the current id of the edited asset
			$insertid = JRequest::getVar('id');

			// Determine all mapped semesters of this asset
			$query = $dbo->getQuery(true);
			$query->select("*");
			$query->from("#__thm_organizer_assets_semesters");
			$query->where("assets_tree_id = $insertid");
			$dbo->setQuery($query);
			$rows = $dbo->loadObjectList();

			// Iterate over each found mapping
			foreach ($rows as $row)
			{
				// Delete the mapping if the current mapping isn't part of the actual post data
				if (!in_array($row->stud_sem_id, $semesters))
				{
					// Build the query
					$query = $dbo->getQuery(true);
					$query->delete("#__thm_organizer_assets_semesters");
					$query->where("assets_tree_id = $insertid");
					$query->where("semesters_majors_id = $row->semesters_majors_id");
					$dbo->setQuery($query);
					$dbo->query($query);
				}
			}
		}
		else
		{
			$dbo = JFactory::getDbo();
			$insertid = $dbo->insertid();
		}

		// Iterate over each semester of the post request
		foreach ($semesters as $semester)
		{
			// Maps the actual asset to a additional semester
			$query = $dbo->getQuery(true);
			$query->insert('#__thm_organizer_assets_semesters');
			$query->set("assets_tree_id = $insertid");
			$query->set("semesters_majors_id = $semester");
			$dbo->setQuery($query);
			$dbo->query();
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
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		// Get the selected major id
		$stud_sem_id = $_SESSION['stud_id'];

		// Determine all node by a given asset id
		$query->select("*");
		$query->from("#__thm_organizer_assets_tree AT at");
		$query->join('#__thm_organizer_assets_semesters AS asem ON asem.assets_tree_id = at.id');
		$query->join('#__thm_organizer_semesters_majors AS sm ON sm.id = asem.semesters_majors_id');
		$query->where("asset = $node");
		$query->where("major_id = $stud_sem_id");
		$dbo->setQuery($query);
		$row = $dbo->loadAssocList();

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
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		$query->select("*");
		$query->from("#__thm_organizer_assets_tree AS at");
		$query->join('#__thm_organizer_assets_semesters AS asem ON asem.assets_tree_id = at.id');
		$query->join('#__thm_organizer_semesters_majors AS sm ON sm.id = asem.semesters_majors_id');
		$query->where("asset <> 0");
		$query->where("major_id = $stud_sem_id");
		$query->group(" asset");

		$dbo->setQuery($query);
		$rows = $dbo->loadObjectList();

		// Iterate over each node of the tree
		foreach ($rows as $row)
		{
			// Determine the path and depth level of the current node
			$depth = count(self::get_path($row->asset)) - 1;
			$path = implode(self::get_path($row->asset));

			$query = $dbo->getQuery(true);
			$query->update($dbo->qn('#__thm_organizer_assets_tree'));
			$query->join("#__thm_organizer_assets_semesters ON #__thm_organizer_assets_semesters.assets_tree_id = #__thm_organizer_assets_tree.id");
			$query->join("#__thm_organizer_semesters_majors ON #__thm_organizer_semesters_majors.id = #__thm_organizer_assets_semesters.semesters_majors_id");
			$query->set("lineage = '$path'");
			$query->set("depth = $depth");
			$query->where("asset = $row->asset");
			$query->where("major_id = $stud_sem_id");
			
			echo (String) $query;
			
			$dbo->setQuery($query);
			$dbo->query();
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
		
		// Iterate over each tree node, if multiple node were selected
		foreach ($cid as $assetTreeID)
		{
			// Create the update sql statement
			$query = $this->_db->getQuery(true);
			$query->update($this->_db->qn('#__thm_organizer_assets_tree'));
			$query->set("published = '1'");
			$query->where("id = $assetTreeID");
			
			$this->_db->setQuery($query);
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

		// Iterate over each tree node, if multiple node were selected
		foreach ($cid as $assetTreeID)
		{
			// Create the update sql statement
			$query = $this->_db->getQuery(true);
			$query->update($this->_db->qn('#__thm_organizer_assets_tree'));
			$query->set("published = '0'");
			$query->where("id = $assetTreeID");
			
			$this->_db->setQuery($query);
			if (!$this->_db->query())
			{
				return false;
			}
		}
		return true;
	}
}
