<?php
/**
 * @version	    v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		TreeView
 * @description TreeView file from com_thm_organizer
 * @author	    Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

require_once JPATH_ROOT . "/components/com_thm_organizer/assets/classes/TreeNode.php";

/**
 * Class TreeView for component com_thm_organizer
 *
 * Class provides methods to create the tree view for mysched
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class TreeView
{
	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 * @since  1.0
	 */
	private $_JDA = null;

	/**
	 * Config
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_cfg = null;

	/**
	 * Type
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_type = null;

	/**
	 * Checked
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_checked = null;

	/**
	 * Public default node
	 *
	 * @var    Array
	 * @since  1.0
	 */
	private $_publicDefault = null;

	/**
	 * Hide the checkboxes
	 *
	 * @var    Boolean
	 * @since  1.0
	 */
	private $_hideCheckBox = null;

	/**
	 * Which schedules are in the tree
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_inTree = array();

	/**
	 * The tree data
	 *
	 * @var    Array
	 * @since  1.0
	 */
	private $_treeData = array();

	/**
	 * The pubic default node
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_publicDefaultNode = null;

	/**
	 * Active schedule data
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_activeScheduleData = null;

	/**
	 * Constructor with the joomla data abstraction object and configuration object
	 *
	 * @param   DataAbstraction  $JDA      A object to abstract the joomla methods
	 * @param   MySchedConfig	 $CFG      A object which has configurations including
	 * @param   Array	 		 $options  An Array with some options
	 *
	 * @since  1.5
	 *
	 */
	public function __construct($JDA, $CFG, $options = array())
	{
		$this->JDA = $JDA;
		$this->cfg = $CFG->getCFG();
		if (isset($options["path"]))
		{
			$this->checked = (array) $options["path"];
		}
		else
		{
			$this->checked = null;
		}
		if (isset($options["publicDefault"]))
		{
			$this->publicDefault = (array) $options["publicDefault"];
		}
		else
		{
			$this->publicDefault = null;
		}
		if (isset($options["hide"]))
		{
			$this->hideCheckBox = $options["hide"];
		}
		else
		{
			$this->hideCheckBox = false;
		}
	}

	/**
	 * Method to create a tree node
	 *
	 * @param   Integer  $id  				 The node id
	 * @param   String	 $text  			 The node text
	 * @param   String	 $iconCls  			 The nodes icon class
	 * @param   Boolean	 $leaf  			 Is the node leaf
	 * @param   Boolean	 $draggable  		 Is the node dragable
	 * @param   Boolean	 $singleClickExpand  Should the node expand on single click
	 * @param   String	 $gpuntisID  		 The gpuntis id for this node
	 * @param   Integer	 $plantype  		 The nodes plantype
	 * @param   String	 $type  			 The nodes type (room, teacher, class)
	 * @param   Object	 $children  		 The nodes children
	 * @param   Integer	 $semesterID  		 In which semester is this node
	 * @param   String	 $nodeKey  			 The node key
	 *
	 * @return Tree nodes
	 */
	private function createTreeNode($id,
			$text,
			$iconCls,
			$leaf,
			$draggable,
			$singleClickExpand,
			$gpuntisID,
			$type,
			$children,
			$semesterID,
			$nodeKey)
	{

		$checked = null;
		$publicDefault = null;
		$treeNode = null;

		if ($this->hideCheckBox == true)
		{
			$checked = null;
		}
		else
		{
			if ($this->checked != null)
			{

				if (isset($this->checked[$id]))
				{
					$checked = $this->checked[$id];
				}
				else
				{
					$checked = "unchecked";
				}
			}
			else
			{
				$checked = "unchecked";
			}
		}

		$expanded = false;

		if ($this->publicDefault != null)
		{
			$publicDefaultArray = $this->publicDefault;
			$firstValue = each($publicDefaultArray);

			if (strpos($firstValue["key"], $id) === 0)
			{
				$expanded = true;
			}
			if ($leaf === true)
			{
				if (isset($this->publicDefault[$id]))
				{
					$publicDefault = $this->publicDefault[$id];
				}
				else
				{
					$publicDefault = "notdefault";
				}
			}
		}
		elseif ($leaf === true)
		$publicDefault = "notdefault";

		if ($this->hideCheckBox == true)
		{
			if ($this->nodeStatus($id))
			{
				$treeNode = new TreeNode(
						$id,
						$text,
						$iconCls,
						$leaf,
						$draggable,
						$singleClickExpand,
						$gpuntisID,
						$type,
						$children,
						$semesterID,
						$checked,
						$publicDefault,
						$nodeKey,
						$expanded
				);
				$this->inTree[] = $gpuntisID;
			}
		}
		else
		{
			$treeNode = new TreeNode(
					$id,
					$text,
					$iconCls,
					$leaf,
					$draggable,
					$singleClickExpand,
					$gpuntisID,
					$type,
					$children,
					$semesterID,
					$checked,
					$publicDefault,
					$nodeKey,
					$expanded
			);
		}

		if ($publicDefault === "default")
		{
			if ($treeNode != null)
			{
				$this->publicDefaultNode = $treeNode;
			}
			else
			{
				$this->publicDefaultNode = new TreeNode(
						$id,
						$text,
						$iconCls,
						$leaf,
						$draggable,
						$singleClickExpand,
						$gpuntisID,
						$type,
						$children,
						$semesterID,
						$checked,
						$publicDefault,
						$nodeKey,
						$expanded
				);
			}
		}

		if ($treeNode == null)
		{
			return $children;
		}
		return $treeNode;
	}

	/**
	 * Method to check if the node is checked
	 *
	 * @param   Integer  $id  The node id
	 *
	 * @return Boolean true if the node is checked unless false
	 */
	private function nodeStatus($id)
	{
		if (isset($this->checked[$id]))
		{
			if ($this->checked[$id] === "checked" || $this->checked[$id] === "intermediate")
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			foreach ($this->checked as $checkedKey => $checkedValue)
			{
				if (strpos($id, $checkedKey) !== false)
				{
					if ($checkedValue === "selected" || $checkedValue === "intermediate")
					{
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * Method to create the tree
	 *
	 * @return Array An array with the tree data
	 */
	public function load()
	{
		$semesterJahrNode = array();
		$semesterarray = array();

		$activeSchedule = $this->getActiveSchedule();

		if (is_object($activeSchedule) && is_string($activeSchedule->schedule))
		{
			$activeScheduleData = json_decode($activeSchedule->schedule);
				
			// To save memory unset schedule
			unset($activeSchedule->schedule);

			if ($activeScheduleData != null)
			{
				$this->_activeScheduleData = $activeScheduleData;
				$activeScheduleRooms = $activeScheduleData->rooms;
				unset($activeScheduleData->rooms);
				$activeScheduleSubjects = $activeScheduleData->subjects;
				unset($activeScheduleData->subjects);
				$activeScheduleTeachers = $activeScheduleData->teachers;
				unset($activeScheduleData->teachers);
				$activeScheduleModules = $activeScheduleData->modules;
				unset($activeScheduleData->modules);
				$this->treeData["module"] = $activeScheduleModules;
				$this->treeData["room"] = $activeScheduleRooms;
				$this->treeData["teacher"] = $activeScheduleTeachers;
				$this->treeData["subject"] = $activeScheduleSubjects;
				$this->treeData["roomtype"] = $activeScheduleData->roomtypes;;
				$this->treeData["degree"] = $activeScheduleData->degrees;
				$this->treeData["field"] = $activeScheduleData->fields;
			}
			else
			{
				// Cant decode json
				return JError::raiseWarning(404, JText::_('Fehlerhafte Daten'));
			}
		}
		else
		{
			return JError::raiseWarning(404, JText::_('Kein aktiver Stundenplan'));
		}

		$temp = $this->createTreeNode(
				$activeSchedule->id,
				$activeSchedule->semestername,
				'semesterjahr' . '-root',
				false,
				false,
				true,
				$activeSchedule->id,
				null,
				null,
				$activeSchedule->id,
				$activeSchedule->id
		);
		$children = $this->StundenplanView($activeSchedule->id, $activeSchedule->id);
		
		if ($temp != null && !empty($temp))
		{
			$temp->setChildren($children);
			$semesterJahrNode[] = $temp;
		}
		elseif (!empty($children))
		{
			$semesterJahrNode[] = $children;
		}

		$this->expandSingleNode($semesterJahrNode);

		if(!isset($this->publicDefaultNode))
		{
			$this->publicDefaultNode = null;
		}
		
		return array("success" => true,"data" => array("tree" => $semesterJahrNode, "treeData" => $this->treeData,
				"treePublicDefault" => $this->publicDefaultNode)
		);
	}

	/**
	 * Method to create the schedule nodes (teacher, room, class)
	 *
	 * @param   Integer  $key  		  The node key
	 * @param   Integer  $planid  	  The plan id
	 * @param   Integer  $semesterID  The semester id
	 *
	 * @return The schedule node
	 */
	private function StundenplanView($key, $semesterID)
	{
		$scheduleTypes = array("teacher", "room", "module", "subject");
		$viewNode = array();

		foreach($scheduleTypes as $scheduleType)
		{
			$nodeKey = $key . "." . $scheduleType;
			$temp = $this->createTreeNode(
					$nodeKey,
					JText::_("COM_THM_ORGANIZER_SCHEDULER_" . $scheduleType . "PLAN"),
					'view' . '-root',
					false,
					false,
					true,
					$scheduleType,
					null,
					null,
					$semesterID,
					$nodeKey
			);
			$children = $this->getStundenplan($nodeKey, $scheduleType, $semesterID);
							
			if ($temp != null && !empty($temp))
			{
				$temp->setChildren($children);
				$viewNode[] = $temp;
			}
			elseif (!empty($children))
			{
				$viewNode[] = $children;
			}
		}

		return $viewNode;
	}

	/**
	 * Method to get the schedule lessons
	 *
	 * @param   Integer  $key  		  The node key
	 * @param   String   $type  	  The schedule type
	 *
	 * @return A tree node
	 */
	private function getStundenplan($key, $scheduleType, $semesterID)
	{
		$treeNode = array();
		$data = array();
		$descriptions = array();

		$data = $this->treeData[$scheduleType];

		foreach($data as $item)
		{
			if($scheduleType === "teacher")
			{
				if(isset($item->description))
				{
					$itemField = $item->description;
					$itemFieldType = $this->_activeScheduleData->fields;
				}
				else
				{
					continue;
				}
			}
			elseif ($scheduleType === "room")
			{
				if(isset($item->description))
				{
					$itemField = $item->description;
					$itemFieldType = $this->_activeScheduleData->roomtypes;
				}
				else
				{
					continue;
				}
			}
			elseif ($scheduleType === "module")
			{
				if(isset($item->degree))
				{
					$itemField = $item->degree;
					$itemFieldType = $this->_activeScheduleData->degrees;
				}
				else
				{
					continue;
				}
			}
			elseif ($scheduleType === "subject")
			{
				if(isset($item->description))
				{
					$itemField = $item->description;
					$itemFieldType = $this->_activeScheduleData->fields;
				}
				else
				{
					continue;
				}
			}
				
			if(!empty($itemField) && !in_array($itemField, $descriptions))
			{
				$descriptions[$itemField] = $itemFieldType->{$itemField};
			}
		}

		foreach($descriptions as $descriptionKey => $descriptionValue)
		{
			$descType = $descriptionKey;
			// Get data for the current description
			$filteredData = array_filter((array) $data, function ($item) use (&$descType, &$scheduleType) {
				$itemField = null;
				if($scheduleType === "teacher")
				{
					if(isset($item->description))
					{
						$itemField = $item->description;
					}
				}
				elseif ($scheduleType === "room")
				{
					if(isset($item->description))
					{
						$itemField = $item->description;
					}
				}
				elseif ($scheduleType === "module")
				{
					if(isset($item->degree))
					{
						$itemField = $item->degree;
					}
				}
				elseif ($scheduleType === "subject")
				{
					if(isset($item->description))
					{
						$itemField = $item->description;
					}
				}

				if($itemField === $descType)
				{
					return true;
				}

				return false;
			});

			$childNodes = array();
			$descriptionID = $key . "." . $descriptionValue->gpuntisID;
				
			foreach($filteredData as $childKey => $childValue)
			{
				$nodeID = $descriptionID . "." .$childValue->gpuntisID;
				if($scheduleType === "teacher")
				{
					$nodeName = $childValue->surname;
				}
				elseif ($scheduleType === "room")
				{
					$nodeName = $childValue->longname;
				}
				elseif ($scheduleType === "module")
				{
					$nodeName = $childValue->restriction;
				}
				elseif ($scheduleType === "subject")
				{
					$nodeName = $childValue->longname;
				}
				else
				{
					$nodeName = $childValue->gpuntisID;
				}

				$childNode = $this->createTreeNode(
						$nodeID,
						$nodeName,
						"leaf" . "-node",
						true,
						true,
						false,
						$childValue->gpuntisID,
						$scheduleType,
						null,
						$semesterID,
						$childKey
				);
				if(is_object($childNode))
				{
					$childNodes[] = $childNode;
				}
			}
			
			if(empty($childNodes))
			{
				$childNodes = null;
			}
			
			$descriptionNode = $this->createTreeNode(
					$descriptionID,
					$descriptionValue->name,
					"studiengang-root",
					false,
					true,
					false,
					$descriptionValue->gpuntisID,
					$scheduleType,
					$childNodes,
					$semesterID,
					$descriptionKey
			);
			
			if(is_object($descriptionNode))
			{
				$treeNode[] = $descriptionNode;
			}
		}

		return $treeNode;
	}

	/**
	 * Method to mark a single node as expanded
	 *
	 * @param   Array  &$arr  An reference to a node child
	 *
	 * @return void
	 */
	private function expandSingleNode(& $arr)
	{
		if (gettype($arr) !== "object" && gettype($arr) !== "array")
		{
			return;
		}

		foreach ($arr as $k => $v)
		{
			if (!isset($v->children))
			{
				$this->expandSingleNode($v);
			}
			elseif (is_array($v->children))
			{
				if (count($arr) > 1)
				{
					return;
				}

				$v->expanded = true;

				$this->expandSingleNode($v->children);
			}
		}
	}

	/**
	 * Method to get the active schedule
	 *
	 * @return mixed  The active schedule as object or false
	 */
	public function getActiveSchedule()
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);
		$query->select('*');
		$query->from('#__thm_organizer_schedules');
		$query->where('active = 1');
		$dbo->setQuery($query);

		if ($error = $dbo->getErrorMsg())
		{
			return false;
		}

		$result = $dbo->loadObject();

		if ($result === null)
		{
			return false;
		}
		return $result;
	}

	/**
	 * Method to get the virtual schedule data
	 *
	 * @param   String   $type  	  The virtual schedule type
	 * @param   Integer  $semesterID  The semester id
	 *
	 * @return The virtual schedule data
	 */
	private function getVirtualSchedules($type, $semesterID)
	{
		$vsquery = "SELECT DISTINCT vs.id AS id,
		vs.vid AS gpuntisID,
		name AS shortname,
		name AS name,
		type AS type,
		department AS parentName,
		responsible AS responsible,
		eid AS elements
		FROM #__thm_organizer_virtual_schedules as vs
		INNER JOIN #__thm_organizer_virtual_schedules_elements as vse
		ON vs.id = vse.vid
		WHERE type = '" . $type . "' AND vs.semesterID = " . $semesterID;

		$res     = $this->JDA->query($vsquery);

		return $res;
	}

	/**
	 * Method to transform a gpuntis id to an id
	 *
	 * @param   String  $gpuntisID  The gpuntis id to transform
	 * @param   String  $type  		The type
	 *
	 * @return The id for the given gpuntis id
	 */
	private function GpuntisIDToid($gpuntisID, $type)
	{
		$query = "SELECT id ";
		if ($type == "room")
		{
			$query .= "FROM #__thm_organizer_rooms ";
		}
		elseif ($type == "clas")
		{
			$query .= "FROM #__thm_organizer_classes ";
		}
		elseif ($type == "doz")
		{
			$query .= "FROM #__thm_organizer_teachers ";
		}
		$query .= "WHERE gpuntisID = '" . $gpuntisID . "'";
		$ret   = $this->JDA->query($query);

		return $ret;
	}
}
