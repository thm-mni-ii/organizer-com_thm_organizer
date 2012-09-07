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
	 $plantype,
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
						$plantype,
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
					$plantype,
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
						$plantype,
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

		$semesterarray = $this->getSemester();

		$this->treeData["clas"] = array();
		$this->treeData["room"] = array();
		$this->treeData["doz"] = array();
		$this->treeData["subject"] = array();

		foreach ($semesterarray as $key => $value)
		{
			$temp = $this->createTreeNode(
					$value->id,
					$value->semesterDesc,
					'semesterjahr' . '-root',
					false,
					false,
					true,
					$value->id,
					null,
					null,
					null,
					$value->id,
					$value->id
			);
	  $children = $this->plantype($value->id, $value->id);

	  if ($temp != null && !empty($temp))
	  {
	  	$temp->setChildren($children);
	  	$semesterJahrNode[] = $temp;
	  }
	  elseif (!empty($children))
	  {
	  	$semesterJahrNode[] = $children;
	  }
		}

		$this->expandSingleNode($semesterJahrNode);

		$semesterJahrNode = $this->treeCorrect($semesterJahrNode);

		return array("success" => true,"data" => array("tree" => $semesterJahrNode, "treeData" => $this->treeData,
			   	   "treePublicDefault" => $this->publicDefaultNode)
			   );
	}

	/**
	 * Method to remove unnecessary array entries
	 *
	 * @param   Object  $node  The tree node to correct
	 *
	 * @return The tree node without unnecessary array entries
	 */
	private function treeCorrect($node)
	{
		$newNode = array();

		foreach ($node as $nodeElement)
		{
			if (is_array($nodeElement))
			{
				$newNode = $this->treeCorrect($nodeElement);
			}
			else
			{
				if (isset($nodeElement->children))
				{
					if (is_array($nodeElement->children))
					{
						$nodeElement->children = $this->treeCorrect($nodeElement->children);
						$newNode[] = $nodeElement;
					}
					else
					{
						$newNode[] = $nodeElement;
					}
					else
					{
						$newNode[] = $nodeElement;
					}
				}
			}
		}

		return $newNode;
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
	 * Method to get semester information
	 *
	 * @return Array An array with semester information
	 */
	private function getSemester()
	{
		$semesterquery = "SELECT id, organization, semesterDesc " .
				"FROM #__thm_organizer_semesters";

		$semesterarray       = $this->JDA->query($semesterquery);

		return $semesterarray;
	}

	/**
	 * Method to create a playtype node
	 *
	 * @param   Integer  $key  		  The node key
	 * @param   Integer  $semesterID  The semester id
	 *
	 * @return The plantype node
	 */
	private function plantype($key, $semesterID)
	{
		$plantypeNode = array();
		$plantypequery = "SELECT #__thm_organizer_plantypes.id ," .
				"#__thm_organizer_plantypes.plantype " .
				"FROM #__thm_organizer_plantypes";

		$plantypes       = $this->JDA->query($plantypequery);

		foreach ($plantypes as $k => $v)
		{
			$plantype = JText::_($v->plantype);
			$nodeKey = $key . "." . $v->id;
			$temp = $this->createTreeNode(
					$nodeKey,
					$plantype,
					"plantype" . '-root',
					false,
					false,
					true,
					$v->id,
					$v->id,
					null,
					null,
					$semesterID,
					$v->id
			);
			if ($v->id == 1)
			{
				$children = $this->StundenplanView($nodeKey, $v->id, $semesterID);
			}
			elseif ($v->id == 2)
			{
				$children = $this->LehrplanView($nodeKey, $v->id, $semesterID);
			}
			if ($temp != null && !empty($temp))
			{
				$temp->setChildren($children);
				$plantypeNode[] = $temp;
			}
			elseif (!empty($children))
			{
				$plantypeNode[] = $children;
			}

		}
		return $plantypeNode;
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
	private function StundenplanView($key, $planid, $semesterID)
	{
		$viewNode = array();
		$temp = $this->createTreeNode(
				$key . ".doz",
				JText::_("COM_THM_ORGANIZER_SCHEDULER_TEACHERS"),
				'view' . '-root',
				false,
				false,
				true,
				"doz",
				$planid,
				null,
				null,
				$semesterID,
				$key . ".doz"
		);
		$children = $this->getStundenplan($key . ".doz", $planid, $semesterID, "doz");
		if ($temp != null && !empty($temp))
		{
			$temp->setChildren($children);
			$viewNode[] = $temp;
		}
		elseif (!empty($children))
		$viewNode[] = $children;

		$temp = $this->createTreeNode(
				$key . ".room",
				JText::_("COM_THM_ORGANIZER_SCHEDULER_ROOMS"),
				'view' . '-root',
				false,
				false,
				true,
				"room",
				$planid,
				null,
				null,
				$semesterID,
				$key . ".room"
		);

		$children = $this->getStundenplan($key . ".room", $planid, $semesterID, "room");
		if ($temp != null && !empty($temp))
		{
			$temp->setChildren($children);
			$viewNode[] = $temp;
		}
		elseif (!empty($children))
		$viewNode[] = $children;

		$temp = $this->createTreeNode(
				$key . ".clas",
				JText::_("COM_THM_ORGANIZER_SCHEDULER_SEMESTER"),
				'view' . '-root',
				false,
				false,
				true,
				"clas",
				$planid,
				null,
				null,
				$semesterID,
				$key . ".clas"
		);

		$children = $this->getStundenplan($key . ".clas", $planid, $semesterID, "clas");
		if ($temp != null && !empty($temp))
		{
			$temp->setChildren($children);
			$viewNode[] = $temp;
		}
		elseif (!empty($children))
		$viewNode[] = $children;

		$temp = $this->createTreeNode(
				$key . ".subject",
				JText::_("COM_THM_ORGANIZER_SCHEDULER_SUBJECTS"),
				'view' . '-root',
				false,
				false,
				true,
				"subject",
				$planid,
				null,
				null,
				$semesterID,
				$key . ".subject"
		);

		$children = $this->getStundenplan($key . ".subject", $planid, $semesterID, "subject");
		if ($temp != null && !empty($temp))
		{
			$temp->setChildren($children);
			$viewNode[] = $temp;
		}
		elseif (!empty($children))
		$viewNode[] = $children;

		$temp = $this->createTreeNode(
				$key . ".delta",
				JText::_("COM_THM_ORGANIZER_SCHEDULER_DELTA_CENTRAL"),
				'delta' . '-node',
				true,
				false,
				false,
				"delta",
				$planid,
				"delta",
				null,
				$semesterID,
				$key . ".delta"
		);

		if ($temp != null && !empty($temp))
		{
			$viewNode[] = $temp;
		}

		$temp = $this->createTreeNode(
				$key . ".respChanges",
				JText::_("COM_THM_ORGANIZER_SCHEDULER_DELTA_OWN"),
				'respChanges' . '-node',
				true,
				false,
				false,
				"respChanges",
				$planid,
				"respChanges",
				null,
				$semesterID,
				$key . ".respChanges"
		);

		if ($temp != null && !empty($temp))
		{
			$viewNode[] = $temp;
		}

		return $viewNode;
	}

	/**
	 * Method to create the curriculum nodes (teacher, room, class)
	 *
	 * @param   Integer  $key  		  The node key
	 * @param   Integer  $planid  	  The plan id
	 * @param   Integer  $semesterID  The semester id
	 *
	 * @return The schedule node
	 */
	private function LehrplanView($key, $planid, $semesterID)
	{
		$viewNode = array();

		$temp = $this->createTreeNode(
				$key . ".doz",
				JText::_("COM_THM_ORGANIZER_SCHEDULER_TEACHERS"),
				'view' . '-root',
				false,
				false,
				true,
				"doz",
				$planid,
				null,
				null,
				$semesterID,
				$key . ".doz"
		);
		$children = $this->getStundenplan($key . ".doz", $planid, $semesterID, "doz");

		if ($temp != null && !empty($temp))
		{
			$temp->setChildren($children);
			$viewNode[] = $temp;
		}
		elseif (!empty($children))
		$viewNode[] = $children;

		$temp = $this->createTreeNode(
				$key . ".clas",
				JText::_("COM_THM_ORGANIZER_SCHEDULER_SEMESTER"),
				'view' . '-root',
				false,
				false,
				true,
				"clas",
				$planid,
				null,
				$this->getStundenplan($key . ".clas", $planid, $semesterID, "clas"),
				$semesterID,
				$key . ".clas"
		);

		$children = $this->getStundenplan($key . ".clas", $planid, $semesterID, "clas");

		if ($temp != null && !empty($temp))
		{
			$temp->setChildren($children);
			$viewNode[] = $temp;
		}
		elseif (!empty($children))
		$viewNode[] = $children;
		return $viewNode;
	}

	/**
	 * Method to get the schedule lessons
	 *
	 * @param   Integer  $key  		  The node key
	 * @param   Integer  $planid  	  The plan id
	 * @param   Integer  $semesterID  The semester id
	 * @param   String   $type  	  The schedule type
	 *
	 * @return A tree node
	 */
	private function getStundenplan($key, $planid, $semesterID, $type)
	{
		$treeNode = array();
		$childNodes = array();
		$datas = array();
		$dataArray = array();
		$virtualSchedules = array();

		if ($type == "doz")
		{
			$datas = $this->getStundenplanDozData($planid, $semesterID);
			$virtualSchedules = $this->getVirtualSchedules("teacher", $semesterID);
		}
		elseif ($type == "room")
		{
			$datas = $this->getStundenplanRoomData($planid, $semesterID);
			$virtualSchedules = $this->getVirtualSchedules($type, $semesterID);
		}
		elseif ($type == "clas")
		{
			$datas = $this->getStundenplanClassData($planid, $semesterID);
			$virtualSchedules = $this->getVirtualSchedules("class", $semesterID);
		}
		else
		{
			$datas = $this->getStundenplanSubjectData($planid, $semesterID);
		}

		if (is_array($datas) === true)
		{
			if (count($datas) != 0)
			{
				$this->treeData[$type] = array_merge_recursive($this->treeData[$type], $datas);
			}
		}

		for ($i = 0; $i < count($datas); $i++)
		{
			$data = $datas[$i];
			$id = trim($data->id);
			$parent = trim($data->parentID);
			if (!isset($dataArray[$parent]))
			{
				$dataArray[$parent] = array();
			}

			$dataArray[$parent][$id]                   = array();
			$dataArray[$parent][$id]["id"]           = trim($id);
			$dataArray[$parent][$id]["department"]   = trim($data->parentName);
			$dataArray[$parent][$id]["shortname"]    = trim($data->shortname);
			$dataArray[$parent][$id]["departmentID"]    = trim($data->departmentID);
			$dataArray[$parent][$id]["type"]        = trim($data->type);
			$dataArray[$parent][$id]["name"]         = trim($data->name);
			$dataArray[$parent][$id]["lessonamount"] = trim($data->lessonamount);
			$dataArray[$parent][$id]["gpuntisID"] = trim($data->gpuntisID);
			$dataArray[$parent][$id]["semesterID"] = trim($semesterID);
			$dataArray[$parent][$id]["plantypeID"] = trim($planid);

			if (!empty($virtualSchedules))
			{
				foreach ($virtualSchedules as $k => $v)
				{
					if ($v->parentName === trim($data->parentName))
					{
						$v->departmentID = $parent;
					}
				}
			}

			if (in_array($key, $this->inTree))
			{
				$dataArray[$parent][$id]["treeLoaded"] = true;
			}
			else
			{
				$dataArray[$parent][$id]["treeLoaded"] = false;
			}
		}

		if (!empty($virtualSchedules))
		{
	 		foreach ($virtualSchedules as $k => $v)
	 		{
		 		$v->elements = $this->GpuntisIDToid(trim($v->elements), $type);
		 		$v->elements = array($v->elements[0]->id);
	 		}

		 	$virtualSchedulesTemp = $virtualSchedules;
		 	foreach ($virtualSchedules as $k => $v)
		 	{
		 		foreach ($virtualSchedulesTemp as $kTemp => $vTemp)
		 		{
		 			if ($k != $kTemp && $v->id === $vTemp->id && $v->parentName === $vTemp->parentName)
		 			{
		 				if (!in_array($vTemp->elements, $v->elements))
		 				{
		 					$v->elements[] = $vTemp->elements[0];
		 				}
		 			}
		 		}
		 	}

		 	foreach ($virtualSchedules as $k => $v)
		 	{
		 		$v->elements = implode(";", $v->elements);
		 	}
		}

		if (!empty($virtualSchedules))
		{
			$this->treeData[$type] = array_merge_recursive($this->treeData[$type], $virtualSchedules);
			for ($i = 0; $i < count($virtualSchedules); $i++)
			{
				$data = $virtualSchedules[$i];
				$id = trim($data->id);
				if (!isset($data->departmentID) && $data->parentName != "none")
				{
					continue;
				}

				if ($data->parentName != "none")
				{
					$parent = trim($data->departmentID);
				}
				else
				{
					$parent = trim($data->parentName);
				}
				if (!isset($dataArray[$parent]))
				{
					$dataArray[$parent] = array();
				}

				if (!isset($dataArray[$parent][$id]))
				{
					$dataArray[$parent][$id]                   = array();
				}
				$dataArray[$parent][$id]["id"]           = trim($id);
				$dataArray[$parent][$id]["department"]   = trim($data->parentName);
				$dataArray[$parent][$id]["shortname"]    = trim($data->shortname);
				$dataArray[$parent][$id]["departmentID"]    = trim($parent);
				$dataArray[$parent][$id]["type"]        = trim($data->type);
				$dataArray[$parent][$id]["name"]         = trim($data->shortname);
				$dataArray[$parent][$id]["lessonamount"] = 1;
				$dataArray[$parent][$id]["gpuntisID"] = trim($data->gpuntisID);

				if (!isset($dataArray[$parent][$id]["elements"]))
				{
					$dataArray[$parent][$id]["elements"] = array();
				}

				$dataArray[$parent][$id]["elements"][] = $data->elements;

				$dataArray[$parent][$id]["semesterID"] = trim($semesterID);
				$dataArray[$parent][$id]["plantypeID"] = trim($planid);

				if (in_array($key, $this->inTree))
				{
					$dataArray[$parent][$id]["treeLoaded"] = true;
				}
				else
				{
					$dataArray[$parent][$id]["treeLoaded"] = false;
				}
			}
		}

		foreach ($dataArray as $dataKey => $dataValue)
		{
			$childNodes = array();

			$nodeKey = str_replace(" ", "", $dataKey);
			$nodeKey = str_replace("(", "", $nodeKey);
			$nodeKey = str_replace(")", "", $nodeKey);
			$parentName = "";
			foreach ($dataValue as $childkey => $childvalue)
			{
				if ($childvalue["lessonamount"] == "0")
				{
					continue;
				}
				if (!isset($childvalue["gpuntisID"]))
				{
					$childvalue["gpuntisID"] = $childvalue["id"];
				}

				if ($nodeKey == "none")
				{
					$nodeID = trim($key) . "." . trim($childvalue["id"]);
				}
				else
				{
					$nodeID = trim($key) . "." . trim($nodeKey) . "." . trim($childvalue["id"]);
				}

				$temp = $this->createTreeNode(
						$nodeID,
						trim($childvalue["name"]),
						"leaf" . "-node",
						true,
						true,
						false,
						$childvalue["gpuntisID"],
						$planid,
						$type,
						null,
						$semesterID,
						trim($childvalue["id"])
						);

				if (!empty($temp))
				{
					if ($nodeKey == "none")
					{
						$childNodes = $temp;
					}
				}
				else
				{
					$childNodes[] = $temp;
				}

				$parentName = $childvalue["department"];
			}

			if ($nodeKey != null && $nodeKey != "none" && !empty($childNodes))
			{
				$parentKey = str_replace(" ", "", trim($key) . "." . trim($nodeKey));
				$parentKey = str_replace("(", "", $parentKey);
				$parentKey = str_replace(")", "", $parentKey);
				$temp = $this->createTreeNode(
						$parentKey,
						trim($parentName),
						'studiengang-root',
						false,
						false,
						true,
						$dataKey,
						$planid,
						null,
						$childNodes,
						$semesterID,
						trim($nodeKey)
				);
				if ($temp != null && !empty($temp))
				{
					$treeNode[] = $temp;
				}
			}
			else
			{
				if (!empty($childNodes))
				{
					if ($dataKey == "none")
					{
						$nodeID = $key . "." . $childvalue["id"];
						$treeNode[] = $childNodes;
					}
				}
			}

		}
		return $treeNode;
	}

	/**
	 * Method to get the class schedule data
	 *
	 * @param   Integer  $planid 	  The plan id
	 * @param   Integer  $semesterID  The semester id
	 *
	 * @return Lesson data
	 */
	private function getStundenplanClassData($planid, $semesterID)
	{
		$classesquery = "SELECT DISTINCT classes.id AS id, " .
				"classes.gpuntisID AS gpuntisID, " .
				"classes.semester AS name, " .
				"classes.major AS parentName, " .
				"REPLACE(REPLACE(REPLACE(classes.major, ' ', ''), '(', ''), ')', '') AS parentID, " .
				"classes.id AS departmentID, " .
				"classes.name AS shortname, " .
				"'lesson' AS type, " .
				"count(lesson_classes.lessonID) AS lessonamount " .
				"FROM #__thm_organizer_classes AS classes " .
				"LEFT JOIN #__thm_organizer_lesson_classes AS lesson_classes " .
				"ON classes.id = lesson_classes.classID " .
				"LEFT JOIN #__thm_organizer_lessons " .
				"ON lesson_classes.lessonID = #__thm_organizer_lessons.id " .
				"WHERE #__thm_organizer_lessons.plantypeID = " . $planid . " " .
				"AND #__thm_organizer_lessons.semesterID = " . $semesterID . " " .
				"OR (#__thm_organizer_lessons.plantypeID is null " .
				"AND #__thm_organizer_lessons.semesterID is null) " .
				"GROUP BY classes.id " .
				"ORDER BY parentName, name";

		$classesarray = array();
		$res          = $this->JDA->query($classesquery);

		return $res;
	}

	/**
	 * Method to get the room schedule data
	 *
	 * @param   Integer  $planid 	  The plan id
	 * @param   Integer  $semesterID  The semester id
	 *
	 * @return Lesson data
	 */
	private function getStundenplanRoomData($planid, $semesterID)
	{
		$roomquery = "SELECT DISTINCT rooms.id AS id, " .
				"rooms.gpuntisID AS gpuntisID, " .
				"CONCAT(descriptions.category, ' (', descriptions.description, ')') as parentName, " .
				"descriptions.id AS parentID, " .
				"descriptions.id AS departmentID, " .
				"rooms.alias AS name, " .
				"rooms.name AS shortname, " .
				"'room' AS type, " .
				"count(lesson_times.lessonID) AS lessonamount " .
				"FROM #__thm_organizer_rooms AS rooms " .
				"LEFT JOIN #__thm_organizer_lesson_times AS lesson_times " .
				"ON rooms.id = lesson_times.roomID " .
				"LEFT JOIN #__thm_organizer_lessons " .
				"ON lesson_times.lessonID = #__thm_organizer_lessons.id " .
				"LEFT JOIN #__thm_organizer_descriptions AS descriptions " .
				"ON descriptions.id = rooms.descriptionID " .
				"WHERE #__thm_organizer_lessons.plantypeID = " . $planid . " " .
				"AND #__thm_organizer_lessons.semesterID = " . $semesterID . " " .
				"OR (#__thm_organizer_lessons.plantypeID is null " .
				"AND #__thm_organizer_lessons.semesterID is null) " .
				"GROUP BY rooms.id " .
				"ORDER BY parentName, name";

		$roomarray = array();
		$res       = $this->JDA->query($roomquery);

		return $res;
	}

	/**
	 * Method to get the teacher schedule data
	 *
	 * @param   Integer  $planid 	  The plan id
	 * @param   Integer  $semesterID  The semester id
	 *
	 * @return Lesson data
	 */
	private function getStundenplanDozData($planid, $semesterID)
	{
		$teacherquery = "SELECT DISTINCT teachers.id AS id, " .
				"teachers.gpuntisID AS gpuntisID, " .
				"departments.id AS parentID, " .
				"departments.id AS departmentID, " .
				"departments.department AS department, " .
				"departments.subdepartment AS subdepartment, " .
				"teachers.name AS name, " .
				"'' AS shortname, " .
				"'teacher' AS type, " .
				"count(lesson_teacher.lessonID) AS lessonamount " .
				"FROM #__thm_organizer_teachers AS teachers " .
				"LEFT JOIN #__thm_organizer_departments AS departments " .
				"ON teachers.departmentID = departments.id " .
				"LEFT JOIN #__thm_organizer_lesson_teachers AS lesson_teacher " .
				"ON teachers.id = lesson_teacher.teacherID " .
				"LEFT JOIN #__thm_organizer_lessons " .
				"ON lesson_teacher.lessonID = #__thm_organizer_lessons.id " .
				"WHERE (#__thm_organizer_lessons.plantypeID = " . $planid . " " .
				"AND #__thm_organizer_lessons.semesterID = " . $semesterID . ") " .
				"OR (#__thm_organizer_lessons.plantypeID is null " .
				"AND #__thm_organizer_lessons.semesterID is null) " .
				"GROUP BY teachers.id " .
				"ORDER BY departments.name, teachers.name";

		$teacherarray = array();
		$res          = $this->JDA->query($teacherquery);
		$return = array();

		if (is_array($res))
		{
			foreach ($res as $resKey => $resValue)
			{
				if ($resValue->subdepartment != "")
				{
					$resValue->parentName = $resValue->subdepartment;
				}
				else
				{
					$resValue->parentName = $resValue->department;
				}
				$return[$resKey] = $resValue;
			}
		}
			return $return;
	}

	/**
	 * Method to get the subject schedule data
	 *
	 * @param   Integer  $planid 	  The plan id
	 * @param   Integer  $semesterID  The semester id
	 *
	 * @return Lesson data
	 */
	private function getStundenplanSubjectData($planid, $semesterID)
	{
		$subjectQuery = "SELECT DISTINCT subjects.id AS id, " .
				"subjects.gpuntisID AS gpuntisID, " .
				"subjects.alias as name, " .
				"subjects.name AS shortname, " .
				"SUBSTRING(subjects.moduleID, 1, 2) AS parentName, " .
				"SUBSTRING(subjects.moduleID, 1, 2) AS parentID, " .
				"subjects.id AS departmentID, " .
				"'subject' AS type, " .
				"count(lessons.subjectID) AS lessonamount " .
				"FROM #__thm_organizer_subjects AS subjects " .
				"LEFT JOIN #__thm_organizer_lessons AS lessons " .
				"ON lessons.subjectID = subjects.id " .
				"WHERE (lessons.plantypeID = " . $planid . " " .
				"AND lessons.semesterID = " . $semesterID . ") " .
				"GROUP BY subjects.gpuntisID " .
				"ORDER BY moduleID, name";

		$subjectArray = array();
		$res          = $this->JDA->query($subjectQuery);

		return $res;
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
	 * Method to get the number if lessons depends on teacher
	 *
	 * @param   String   $resourcename  The resource name
	 * @param   Integer  $fachsemester  The semester id
	 *
	 * @return The number lessons
	 */
	private function getCountTeacherLessons($resourcename, $fachsemester)
	{
		$query = "SELECT * " . " FROM #__thm_organizer_lesson_teachers INNER JOIN #__thm_organizer_teachers " .
		"ON teacherID = #__thm_organizer_teachers.id INNER JOIN #__thm_organizer_lessons " .
		"ON #__thm_organizer_lesson_teachers.lessonID = #__thm_organizer_lessons.id " .
		"WHERE #__thm_organizer_teachers.gpuntisID = '" . $resourcename . "' AND semesterID = '" . $fachsemester . "' AND plantypeID = 1";
		$hits  = $this->JDA->query($query);
		return count($hits);
	}

	/**
	 * Method to get the number if lessons depends on room
	 *
	 * @param   String   $resourcename  The resource name
	 * @param   Integer  $fachsemester  The semester id
	 *
	 * @return The number lessons
	 */
	private function getCountRoomLessons($resourcename, $fachsemester)
	{
		$query = "SELECT * " . " FROM #__thm_organizer_lesson_times INNER JOIN #__thm_organizer_rooms " .
		"ON roomID = #__thm_organizer_rooms.id INNER JOIN #__thm_organizer_lessons " .
		"ON #__thm_organizer_lesson_times.lessonID = #__thm_organizer_lessons.id " .
		"WHERE #__thm_organizer_rooms.gpuntisID = '" . $resourcename . "' AND semesterID = '" . $fachsemester . "' AND plantypeID = 1";
		$hits  = $this->JDA->query($query);
		return count($hits);
	}

	/**
	 * Method to get the number if lessons depends on class
	 *
	 * @param   String   $resourcename  The resource name
	 * @param   Integer  $fachsemester  The semester id
	 *
	 * @return The number lessons
	 */
	private function getCountClassLessons($resourcename, $fachsemester)
	{
		$query = "SELECT * " . " FROM #__thm_organizer_lesson_classes INNER JOIN #__thm_organizer_classes " .
		"ON classID = #__thm_organizer_classes.id INNER JOIN #__thm_organizer_lessons " .
		"ON #__thm_organizer_lesson_classes.lessonID = #__thm_organizer_lessons.id " .
		"WHERE #__thm_organizer_classes.gpuntisID = '" . $resourcename . "' AND semesterID = '" . $fachsemester . "' AND plantypeID = 1";
		$hits  = $this->JDA->query($query);
		return count($hits);
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
