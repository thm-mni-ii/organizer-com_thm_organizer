<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelMappings
 * @description THM_OrganizerModelMappings component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Class THM_OrganizerModelMappings for component com_thm_organizer
 *
 * Class provides methods to deal with mappings
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerModelMappings extends JModelList
{
	/**
	 * Database
	 *
	 * @var    Object
	 */
	protected $dbo = null;

	/**
	 * Data
	 *
	 * @var    Object
	 */
	private $_data;

	/**
	 * Pagination
	 *
	 * @var    Object
	 */
	private $_pagination = null;

	/**
	 * Constructor to initialise the database and call the parent constructor
	 */
	public function __construct()
	{
		$this->db = JFactory::getDBO();
		parent::__construct();
	}

	/**
	 * Method to get the hex color code
	 *
	 * @param   Integer  $colorID  Color id
	 *
	 * @return  String
	 */
	public function getColorHex($colorID)
	{
		$dbo = JFactory::getDBO();

		$query = $dbo->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_colors');
		$query->where("id = $colorID");
		$dbo->setQuery($query);
		$color = $dbo->loadObjectList();

		if (isset($color[0]) && isset($color[0]->color))
		{
			return $color[0]->color;
		}
		else
		{
			return "ffffff";
		}
	}

	/**
	 * Method to select the tree of a given major
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$dbo = JFactory::getDBO();

		// Get options of the list view
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		$published = $this->state->get('filter.published');
		$level = $this->state->get('filter.level');

		// Get the current major id
		$sid = JRequest::getVar('id');

		// Create the sql statement
		$query = $dbo->getQuery(true);
		$select = "*, count(*) as count, atr.color_id as color_id_instance, ";
		$select .= "atr.menu_link as menu_link_instance, a.color_id as color_id_object, ";
		$select .= "atr.id as asset_id, s.name as semester_name, aty.name as asset_type ";
		$query->select($select);

		$query->from('#__thm_organizer_semesters_majors AS sm');
		$query->join('#__thm_organizer_semesters AS s ON sm.semester_id = s.id');
		$query->join('#__thm_organizer_assets_semesters AS asem ON sm.id = asem.semesters_majors_id');
		$query->join('#__thm_organizer_assets_tree AS atr ON at.id = asem.assets_tree_id');
		$query->join('#__thm_organizer_assets AS a ON atr.asset = a.id');
		$query->join('#__thm_organizer_colors AS c ON atr.color_id = c.id');
		$query->join('#__thm_organizer_asset_types AS aty ON aty.id = a.asset_type_id');

		$query->where("sm.major_id= $sid");

		$search = $dbo->Quote('%' . $dbo->getEscaped($this->state->get('filter.search'), true) . '%');
		$searchClause = "(title_de LIKE '$search' ";
		$searchClause .= "OR title_en LIKE '$search' ";
		$searchClause .= "OR a.short_title_de LIKE '$search' ";
		$searchClause .= "OR a.short_title_en LIKE '$search' ";
		$searchClause .= "OR abbreviation LIKE '$search') ";
		$query->where($searchClause);

		if (is_numeric($published))
		{
			$query->where('published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(published IN (0, 1))');
		}

		if (is_numeric($level))
		{
			$query->where('depth = ' . (int) $level);
		}
		elseif ($level === '')
		{
			// $query->where('(published IN (0, 1))');
		}

		// Do not show multiple assets
		$query->group(" assets_tree_id");

		if ($orderCol == "semester_name")
		{
			$orderCol = " semester_name, concat( lineage, asset )";
		}
		elseif ($orderCol == "a.lft")
		{
			$orderCol = "  parent_id ASC, ordering";
		}

		$query->order($dbo->getEscaped($orderCol . ' ' . $orderDirn));
		
		return $query;
	}

	/**
	 * Method to traverse the tree
	 *
	 * @param   Integer  $treeID     Id
	 * @param   String   $indent     Indent
	 * @param   Array    $list       List
	 * @param   Array    &$children  Children
	 * @param   Integer  $maxlevel   Max level (default: 9999)
	 * @param   Integer  $level      Level (default: 0)
	 * @param   Integer  $type       Type (default: 1)
	 *
	 * @return  Object
	 */
	public function treerecurse($treeID, $indent, $list, &$children, $maxlevel = 9999, $level = 0, $type = 1)
	{
		if (@$children[$treeID] && $level <= $maxlevel)
		{
			foreach ($children[$treeID] as $v)
			{
				$treeID = $v->asset;

				if ($type)
				{
					$pre = '<sup>|_</sup>&nbsp;';
					$spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				}
				else
				{
					$pre = '- ';
					$spacer = '&nbsp;&nbsp;';
				}

				if ($v->parent_id == 0)
				{
					$txt = $v->title_de;
				}
				else
				{
					$txt = $pre . $v->title_de;
				}

				$temp = $v;
				$temp->treename = "$indent$txt";
				$temp->children = count(@$children[$treeID]);
				array_push($list, $temp);
				$list = self::TreeRecurse($treeID, $indent . $spacer, $list, $children, $maxlevel, $level + 1, $type);
			}
		}

		return $list;
	}
	/**
	 * Method to overwrite the getItems method in order to set the correct
	 *
	 * @return  Object
	 */
	public function getItems()
	{
		$items = parent::getItems();
		
		// Establish the hierarchy of the menu
		$children = array();

		// First pass - collect children
		foreach ($items as $v)
		{
			$url = "index.php?option=com_thm_organizer&view=semester&layout=edit&id=$v->semester_id";
			$v->semester_id = '<a href="' . $url . '">' . $v->semester_name . '</a>';

			$pt = $v->parent_id;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push($list, $v);
			$children[$pt] = $list;
		}

		// Second pass - get an indent list of the items
		$items = self::treerecurse(0, '', array(), $children, max(0, 10 - 1), 1);
		$app = JFactory::getApplication();

		// Iterate over each node row
		foreach ($items as $key => $row)
		{
			if ($row->color_id_flag == 1)
			{
				$row->color = self::getColorHex($row->color_id_object);
			}
			else
			{
				$row->color = self::getColorHex($row->color_id_instance);
			}

			// Check if the current node belongs to multiple semesters
			if ($row->count > 1)
			{
				// Get the current major id
				$majorID = $_SESSION['stud_id'];

				// Determine the related semesters
				$dbo = JFactory::getDBO();
				$query = $dbo->getQuery(true);
				$query->select("semesters.id as semester_id");
				$query->from(' #__thm_organizer_assets_tree as at');
				$query->join('#__thm_organizer_assets_semesters AS asem ON asem.assets_tree_id = at.id');
				$query->join('#__thm_organizer_semesters_majors AS sm ON asem.semesters_majors_id = sm.id');
				$query->join('#__thm_organizer_semesters AS s ON s.id = sm.semester_id');
				$query->where("sm.major_id = $majorID");
				$query->where("asset = $row->asset");
				$dbo->setQuery($query);
				$assets = $dbo->loadObjectList();

				// Find the first and last element of the array
				$last_item = each(end($assets));
				reset($assets);

				// Add a additional field to the resulting array
				$row->semester_id = '';

				// Iterate over each found semester
				foreach ($assets as $key => $value)
				{
					$url = "index.php?option=com_thm_organizer&view=semester&layout=edit&id=$value->semester_id";
					$value->semester_id = '<a href="' . $url . '">' . $value->semester_id . '</a>';

					// Attach the current semester
					$row->semester_id .= $value->semester_id;

					// If the current value is not the last, attach a comma
					if ($value != $last_item['value'] && $key != $last_item['key'])
					{
						$row->semester_id .= ', ';
					}
				}
			}
		}
		
		return $items;
	}

	/**
	 * Method to populate state
	 *
	 * @return  void
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		$layout = JRequest::getVar('layout');
		if (!empty($layout))
		{
			$this->context .= '.' . $layout;
		}

		$order = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '');
		$level = $app->getUserStateFromRequest($this->context . '.filter_level', 'filter_level', '');
		$dir = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '');
		$search = $app->getUserStateFromRequest($this->context . '.filter_search', 'filter_search', '');
		$limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '');
		$published = $this->getUserStateFromRequest($this->context . '.filter_published', 'filter_published', '');

		$this->setState('filter.search', $search);
		$this->setState('filter.level', $level);
		$this->setState('list.ordering', $order);
		$this->setState('list.direction', $dir);
		$this->setState('filter.published', $published);
		$this->setState('limit', $limit);

		// Set the default ordering behaviour
		if ($order == '')
		{
			parent::populateState("semester_name", "ASC");
		}
		else
		{
			parent::populateState($order, $dir);
		}
	}

	/**
	 * Method to return the full name of the current major
	 *
	 * @return  Associative array
	 */
	public function getCurriculumName()
	{
		// Get the major id
		$pid = JRequest::getVar('id');
		$dbo = JFactory::getDBO();

		// Build the query
		$query = $dbo->getQuery(true);
		$query->select('
				#__thm_organizer_majors.subject AS fach,
				#__thm_organizer_majors.po AS po,
				#__thm_organizer_degrees.name AS abschluss
				');
		$query->from('#__thm_organizer_majors');
		$query->join('cross', '#__thm_organizer_degrees
				ON #__thm_organizer_degrees.id = #__thm_organizer_majors.degree_id');
		$query->where('#__thm_organizer_majors.id = ' . $pid);
		$dbo->setQuery((string) $query);

		return $dbo->loadAssoc();
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
	 * Method to get the reorder conditions
	 *
	 * @param   Object  $table  Table
	 *
	 * @return  Array
	 */
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'parent_id = ' . (int) $table->parent_id;
		return $condition;
	}

	/**
	 * Method to adjust the ordering of a row.
	 *
	 * Returns NULL if the user did not have edit
	 * privileges for any of the selected primary keys.
	 *
	 * @param   Integer  $pks    The ID of the primary key to move.
	 * @param   Integer  $delta  Increment, usually +1 or -1         (default: 0)
	 *
	 * @return  mixed  False on failure or error, true on success, null if the $pk is empty (no items selected).
	 */
	public function reorder($pks, $delta = 0)
	{
		// Initialise variables.
		$table = $this->getTable();
		$pks = (array) $pks;
		$result = true;
		$allowed = true;

		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk))
			{
				$where = $this->getReorderConditions($table);

				if (!$table->move($delta, $where))
				{
					$this->setError($table->getError());
					unset($pks[$i]);
					$result = false;
				}
			}
			else
			{
				$this->setError($table->getError());
				unset($pks[$i]);
				$result = false;
			}
		}

		if ($allowed === false && empty($pks))
		{
			$result = null;
		}

		// Clear the component's cache
		if ($result == true)
		{
			$this->cleanCache();
		}

		return $result;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   Array    $pks    An array of primary key ids.  (default: null)
	 * @param   Integer  $order  +1 or -1					   (default: null)
	 *
	 * @return  mixed
	 */
	public function saveorder($pks = null, $order = null)
	{
		// Initialise variables.
		$table = $this->getTable();
		$conditions = array();

		if (empty($pks))
		{
			$textConstant = $this->text_prefix . '_ERROR_NO_ITEMS_SELECTED';
			return JError::raiseWarning(500, JText::_($textConstant));
		}

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$table->load((int) $pk);

			if ($table->ordering != $order[$i])
			{
				echo "doordering";
				$table->ordering = $order[$i];

				if (!$table->store())
				{
					$this->setError($table->getError());
					return false;
				}

				// Remember to reorder within position and client_id
				$condition = $this->getReorderConditions($table);
				$found = false;

				foreach ($conditions as $cond)
				{
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				}

				if (!$found)
				{
					$key = $table->getKeyName();
					$conditions[] = array($table->$key, $condition);
				}
			}
		}

		// Execute reorder for each category.
		foreach ($conditions as $cond)
		{
			$table->load($cond[0]);
			$table->reorder($cond[1]);
		}

		// Clear the component's cache
		$this->cleanCache();
		return true;
	}
}
