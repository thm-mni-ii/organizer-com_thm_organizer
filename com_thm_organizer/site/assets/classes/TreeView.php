<?php

/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        TreeView
 * @description TreeView file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . "/components/com_thm_organizer/assets/classes/TreeNode.php";
/**
 * Class TreeView for component com_thm_organizer
 * Class provides methods to create the tree view for mysched
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THMTreeView
{
    /**
     * Joomla data abstraction
     *
     * @var    DataAbstraction
     */
    private $_JDA = null;

    /**
     * Config
     *
     * @var    Object
     */
    private $_cfg = null;

    /**
     * Checked
     *
     * @var    String
     */
    private $_checked = null;

    /**
     * Public default node
     *
     * @var    Array
     */
    private $_publicDefault = null;

    /**
     * Hide the checkboxes
     *
     * @var    Boolean
     */
    private $_hideCheckBox = null;

    /**
     * Which schedules are in the tree
     *
     * @var    Object
     */
    private $_inTree = array();

    /**
     * The tree data
     *
     * @var    Array
     */
    private $_treeData = array();

    /**
     * The pubic default node
     *
     * @var    Object
     */
    private $_publicDefaultNode = null;

    /**
     * Active schedule data
     *
     * @var    Object
     */
    private $_activeScheduleData = null;

    /**
     * Constructor with the joomla data abstraction object and configuration object
     *
     * @param   DataAbstraction  $JDA      A object to abstract the joomla methods
     * @param   MySchedConfig    $CFG      A object which has configurations including
     * @param   Array            $options  An Array with some options
     */
    public function __construct($JDA, $CFG, $options = array())
    {
        $this->_JDA = $JDA;
        $this->_cfg = $CFG->getCFG();

        $menuid = JRequest::getInt("menuID", 0);

        $site = new JSite;
        $menu = $site->getMenu();
        
        if ($menuid == 0 && is_null($menu->getActive()))
        {
        	$options["hide"] = false;
        	$this->_checked = array();
        	$this->_publicDefaultNode = array();
        	$this->_publicDefaultNode = array();
        	$this->departmentSemesterSelection = JRequest::getString('departmentSemesterSelection');
        }
        else
        {
	        if ($menuid != 0)
	        {
	            $menuparams = $menu->getParams($menuid);
	        }
	        else
	        {
	            $menuparams = $menu->getParams($menu->getActive()->id);
	            $options["hide"] = true;
	        }
	        
	        if (isset($options["path"]))
	        {
	            $this->_checked = (array) $options["path"];
	        }
	        else
	        {
	            $treeIDs = JRequest::getString('treeIDs');
	            $treeIDsData = json_decode($treeIDs);
	            if ($treeIDsData != null)
	            {
	                $this->_checked = (array) $treeIDsData;
	            }
	            else
	            {
	                $this->_checked = (array) json_decode($menuparams->get("id"));
	            }
	        }
	
	        if (isset($options["publicDefault"]))
	        {
	            $this->_publicDefault = (array) $options["publicDefault"];
	        }
	        else
	        {
	            $publicDefaultID = json_decode(JRequest::getString('publicDefaultID'));
	            if ($publicDefaultID != null)
	            {
	                $this->_publicDefault = (array) $publicDefaultID;
	            }
	            else
	            {
	                $this->_publicDefault = (array) json_decode($menuparams->get("publicDefaultID"));
	            }
	        }
	
	        if (isset($options["hide"]))
	        {
	            $this->_hideCheckBox = $options["hide"];
	        }
	        else
	        {
	            $this->_hideCheckBox = false;
	        }
	
	        if (JRequest::getString('departmentSemesterSelection') == "")
	        {
	            if (isset($options["departmentSemesterSelection"]))
	            {
	                $this->departmentSemesterSelection = $options["departmentSemesterSelection"];
	            }
	            else
	            {
	                $this->departmentSemesterSelection = $menuparams->get("departmentSemesterSelection");
	            }
	        }
	        else
	        {
	            $this->departmentSemesterSelection = JRequest::getString('departmentSemesterSelection');
	        }
        }
    }

    /**
     * Method to create a tree node
     *
     * @param   Object  $nodeData  Contains the node id, node text, nodes icon class, leaf, dragable, single click, gpuntis id,
     *                             type (room, teacher, class), children, semester, node key
     *
     * @return Tree nodes
     */
    private function createTreeNode($nodeData)
    {
        $nodeID = $nodeData["nodeID"];
        $leaf = $nodeData["leaf"];
        $nodeKey = $nodeData["nodeKey"];
        $children = $nodeData["children"];
        $gpuntisID = $nodeData["gpuntisID"];
 
 
        $checked = null;
        $publicDefault = null;
        $treeNode = null;

        if ($this->_hideCheckBox == true)
        {
            $checked = null;
        }
        else
        {
            if ($this->_checked != null)
            {
                if (isset($this->_checked[$nodeID]))
                {
                    $checked = $this->_checked[$nodeID];
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

        if ($this->_publicDefault != null)
        {
            $publicDefaultArray = $this->_publicDefault;
            $firstValue = each($publicDefaultArray);

            if (strpos($firstValue["key"], $nodeID) === 0)
            {
                $expanded = true;
            }
            if ($leaf === true)
            {
                if (isset($this->_publicDefault[$nodeID]))
                {
                    $publicDefault = $this->_publicDefault[$nodeID];
                }
                else
                {
                    $publicDefault = "notdefault";
                }
            }
        }
        elseif ($leaf === true)
        {
            $publicDefault = "notdefault";
        }

        if ($this->_hideCheckBox == true)
        {
            if ($this->nodeStatus($nodeID))
            {
                $treeNode = new THMTreeNode($nodeData, $checked, $publicDefault, $nodeKey, $expanded);
                $this->_inTree[] = $gpuntisID;
            }
        }
        else
        {
            $treeNode = new THMTreeNode($nodeData, $checked, $publicDefault, $nodeKey, $expanded);
        }

        if ($publicDefault === "default")
        {
            if ($treeNode != null)
            {
                $this->_publicDefaultNode = $treeNode;
            }
            else
            {
                $this->_publicDefaultNode = new THMTreeNode($nodeData, $checked, $publicDefault, $nodeKey, $expanded);
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
     * @param   Integer  $nodeID  The node id
     *
     * @return Boolean true if the node is checked unless false
     */
    private function nodeStatus($nodeID)
    {
        if (isset($this->_checked[$nodeID]))
        {
            if ($this->_checked[$nodeID] === "checked" || $this->_checked[$nodeID] === "intermediate")
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
            foreach ($this->_checked as $checkedKey => $checkedValue)
            {
                if (strpos($nodeID, $checkedKey) !== false)
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

        $activeSchedule = $this->getActiveSchedule();

        if (is_object($activeSchedule) && is_string($activeSchedule->schedule))
        {
            $activeScheduleData = json_decode($activeSchedule->schedule);

            // To save memory unset schedule
            unset($activeSchedule->schedule);

            if ($activeScheduleData != null)
            {
                $this->_activeScheduleData = $activeScheduleData;
                $this->_treeData["module"] = $activeScheduleData->modules;
                $this->_treeData["room"] = $activeScheduleData->rooms;
                $this->_treeData["teacher"] = $activeScheduleData->teachers;
                $this->_treeData["subject"] = $activeScheduleData->subjects;
                $this->_treeData["roomtype"] = $activeScheduleData->roomtypes;
                $this->_treeData["degree"] = $activeScheduleData->degrees;
                $this->_treeData["field"] = $activeScheduleData->fields;
            }
            else
            {
                // Cant decode json
                return JError::raiseWarning(404, JText::_('COM_THM_ORGANIZER_SCHEDULER_DATA_FLAWED'));
            }
 
            // Get ids for teachers and rooms
            $schedulerModel = JModel::getInstance('scheduler', 'thm_organizerModel', array('ignore_request' => false, 'display_type' => 4));
            $rooms = $schedulerModel->getRooms();
            $teachers = $schedulerModel->getTeachers();

            foreach ($this->_treeData["room"] as $roomValue)
            {
                foreach ($rooms as $databaseRooms)
                {
                    if ($roomValue->gpuntisID === $databaseRooms->gpuntisID)
                    {
                        $roomValue->dbID = $databaseRooms->id;
                    }
                }
            }

            foreach ($this->_treeData["teacher"] as $teacherValue)
            {
                foreach ($teachers as $databaseTeachers)
                {
                    if ($teacherValue->gpuntisID === $databaseTeachers->gpuntisID)
                    {
                        $teacherValue->dbID = $databaseTeachers->id;
                    }
                }
            }
        }
        else
        {
            return array("success" => false, "data" => array("tree" => array(), "treeData" => array(), "treePublicDefault" => ""));
        }
 
        $createTreeNodeData = array();
        $createTreeNodeData["nodeID"] = $this->departmentSemesterSelection;
        $createTreeNodeData["text"] = $activeSchedule->semestername;
        $createTreeNodeData["iconCls"] = 'semesterjahr' . '-root';
        $createTreeNodeData["leaf"] = false;
        $createTreeNodeData["draggable"] = false;
        $createTreeNodeData["singleClickExpand"] = true;
        $createTreeNodeData["gpuntisID"] = $activeSchedule->id;
        $createTreeNodeData["type"] = null;
        $createTreeNodeData["children"] = null;
        $createTreeNodeData["semesterID"] = $activeSchedule->id;
        $createTreeNodeData["nodeKey"] = $activeSchedule->id;

        $temp = $this->createTreeNode($createTreeNodeData);
        $children = $this->StundenplanView($this->departmentSemesterSelection, $activeSchedule->id);

        if ($temp != null && !empty($temp))
        {
            $temp->setChildren($children);

            if (count($temp) == 1)
            {
                $semesterJahrNode = $temp;
            }
            else
            {
                $semesterJahrNode[] = $temp;
            }
        }
        elseif (!empty($children))
        {
            $semesterJahrNode = $children;
        }

        $this->expandSingleNode($semesterJahrNode);

        if (!isset($this->_publicDefaultNode))
        {
            $this->_publicDefaultNode = null;
        }

        return array("success" => true, "data" => array("tree" => $semesterJahrNode, "treeData" => $this->_treeData,
                "treePublicDefault" => $this->_publicDefaultNode)
        );

    }

    /**
     * Method to create the schedule nodes (teacher, room, class)
     *
     * @param   Integer  $key         The node key
     * @param   Integer  $semesterID  The semester id
     *
     * @return The schedule node
     */
    private function StundenplanView($key, $semesterID)
    {
        $scheduleTypes = array("teacher", "room", "module", "subject");
        $viewNode = array();

        foreach ($scheduleTypes as $scheduleType)
        {
            $nodeKey = $key . ";" . $scheduleType;
            $textConstant = 'COM_THM_ORGANIZER_SCHEDULER_' . $scheduleType . 'PLAN';
 
            $createTreeNodeData = array();
            $createTreeNodeData["nodeID"] = $nodeKey;
            $createTreeNodeData["text"] = JText::_($textConstant);
            $createTreeNodeData["iconCls"] = 'view' . '-root';
            $createTreeNodeData["leaf"] = false;
            $createTreeNodeData["draggable"] = false;
            $createTreeNodeData["singleClickExpand"] = true;
            $createTreeNodeData["gpuntisID"] = $scheduleType;
            $createTreeNodeData["type"] = null;
            $createTreeNodeData["children"] = null;
            $createTreeNodeData["semesterID"] = $semesterID;
            $createTreeNodeData["nodeKey"] = $nodeKey;
 
            $temp = $this->createTreeNode($createTreeNodeData);
            $children = $this->getStundenplan($nodeKey, $scheduleType, $semesterID);

            if ($temp != null && !empty($temp))
            {
                $temp->setChildren($children);
                $viewNode[] = $temp;
            }
            elseif (!empty($children))
            {
                if (count($children) == 1)
                {
                    $viewNode = $children;
                }
                else
                {
                    $viewNode[] = $children;
                }
            }
        }

        return $viewNode;

    }

    /**
     * Method to get the schedule lessons
     *
     * @param   Integer  $key           The node key
     * @param   String   $scheduleType  The schedule type
     * @param   Integer  $semesterID    The semester id
     *
     * @return A tree node
     */
    private function getStundenplan($key, $scheduleType, $semesterID)
    {
        $treeNode = array();
        $descriptions = array();
        $data = $this->_treeData[$scheduleType];

        foreach ($data as $item)
        {
            if ($scheduleType === "teacher")
            {
                if (isset($item->description))
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
                if (isset($item->description))
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
                if (isset($item->degree))
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
                if (isset($item->description))
                {
                    $itemField = $item->description;
                    $itemFieldType = $this->_activeScheduleData->fields;
                }
                else
                {
                    continue;
                }
            }

            if (!empty($itemField) && !in_array($itemField, $descriptions))
            {
                $descriptions[$itemField] = $itemFieldType->{$itemField};
            }
        }

        foreach ($descriptions as $descriptionKey => $descriptionValue)
        {
            $descType = $descriptionKey;

            // Get data for the current description
            $filteredData = array_filter(
                (array) $data, function ($item) use (&$descType, &$scheduleType) {
                    $itemField = null;
                    if ($scheduleType === "teacher")
                    {
                        if (isset($item->description))
                        {
                            $itemField = $item->description;
                        }
                    }
                    elseif ($scheduleType === "room")
                    {
                        if (isset($item->description))
                        {
                            $itemField = $item->description;
                        }
                    }
                    elseif ($scheduleType === "module")
                    {
                        if (isset($item->degree))
                        {
                            $itemField = $item->degree;
                        }
                    }
                    elseif ($scheduleType === "subject")
                    {
                        if (isset($item->description))
                        {
                            $itemField = $item->description;
                        }
                    }

                    if ($itemField === $descType)
                    {
                        return true;
                    }

                    return false;
                }
            );

            $childNodes = array();
            $descriptionID = $key . ";" . $descriptionKey;

            foreach ($filteredData as $childKey => $childValue)
            {
                $nodeID = $descriptionID . ";" . $childKey;
                if ($scheduleType === "teacher")
                {
                    if (strlen($childValue->surname) > 0)
                    {
                        $nodeName = $childValue->surname;
                    }
                    else
                    {
                        $nodeName = $childKey;
                    }
 
                    if (isset($childValue->firstname) && strlen($childValue->firstname) > 0)
                    {
                        $nodeName .= ", " . $childValue->firstname{0} . ".";
                    }
                    elseif (isset($childValue->forename) && strlen($childValue->forename) > 0)
                    {
                        $nodeName .= ", " . $childValue->forename{0} . ".";
                    }
                }
                elseif ($scheduleType === "room")
                {
                    if (strlen($childValue->longname) > 0)
                    {
                        $nodeName = $childValue->longname;
                    }
                    else
                    {
                        $nodeName = $childKey;
                    }
                }
                elseif ($scheduleType === "module")
                {
                    if (strlen($childValue->restriction) > 0)
                    {
                        $nodeName = $childValue->restriction;
                    }
                    else
                    {
                        $nodeName = $childKey;
                    }
                }
                elseif ($scheduleType === "subject")
                {
                    if (strlen($childValue->longname) > 0)
                    {
                        $nodeName = $childValue->longname;
                    }
                    else
                    {
                        $nodeName = $childKey;
                    }
                }
                else
                {
                    $nodeName = $childValue->gpuntisID;
                }

                // Überprüfung ob der Plan Veranstaltungen hat
                if ($this->_hideCheckBox == false)
                {
                    $hasLessons = true;
                }
                else
                {
                    $hasLessons = $this->treeNodeHasLessons($childKey, $scheduleType);

                    // Erstmal immer true!
//                     $hasLessons = true;
                }

                $childNode = null;
                if ($hasLessons)
                {
                    $createTreeChildNodeData = array();
                    $createTreeChildNodeData["nodeID"] = $nodeID;
                    $createTreeChildNodeData["text"] = $nodeName;
                    $createTreeChildNodeData["iconCls"] = "leaf" . "-node";
                    $createTreeChildNodeData["leaf"] = true;
                    $createTreeChildNodeData["draggable"] = true;
                    $createTreeChildNodeData["singleClickExpand"] = false;
                    $createTreeChildNodeData["gpuntisID"] = $childValue->gpuntisID;
                    $createTreeChildNodeData["type"] = $scheduleType;
                    $createTreeChildNodeData["children"] = null;
                    $createTreeChildNodeData["semesterID"] = $semesterID;
                    $createTreeChildNodeData["nodeKey"] = $childKey;
 
                    $childNode = $this->createTreeNode($createTreeChildNodeData);
                }
                if (is_object($childNode))
                {
                    $childNodes[] = $childNode;
                }
            }

            if (empty($childNodes))
            {
                $childNodes = null;
            }
            $descriptionNode = null;
            if ($childNodes != null)
            {
                $createTreeDescriptionNodeData = array();
                $createTreeDescriptionNodeData["nodeID"] = $descriptionID;
                $createTreeDescriptionNodeData["text"] = $descriptionValue->name;
                $createTreeDescriptionNodeData["iconCls"] = "studiengang-root";
                $createTreeDescriptionNodeData["leaf"] = false;
                $createTreeDescriptionNodeData["draggable"] = true;
                $createTreeDescriptionNodeData["singleClickExpand"] = false;
                $createTreeDescriptionNodeData["gpuntisID"] = $descriptionValue->gpuntisID;
                $createTreeDescriptionNodeData["type"] = $scheduleType;
                $createTreeDescriptionNodeData["children"] = $childNodes;
                $createTreeDescriptionNodeData["semesterID"] = $semesterID;
                $createTreeDescriptionNodeData["nodeKey"] = $descriptionKey;
 
                $descriptionNode = $this->createTreeNode($createTreeDescriptionNodeData);
            }

            if (!is_null($descriptionNode) && is_object($descriptionNode))
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

        foreach ($arr as $v)
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
        $departmentSemester = explode(";", $this->departmentSemesterSelection);
        if (count($departmentSemester) == 2)
        {
            $department = $departmentSemester[0];
            $semester = $departmentSemester[1];
        }
        else
        {
            return false;
        }

        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_schedules');
        $query->where('departmentname = ' . $dbo->quote($department));
        $query->where('semestername = ' . $dbo->quote($semester));
        $query->where('active = 1');
        $dbo->setQuery($query);

        if ($dbo->getErrorMsg())
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
     * Method to check if an tree node has lessons
     *
     * @param   Object  $nodeID  The tree node id
     * @param   String  $type    The tree node type
     *
     * @return  boolean
     */
    private function treeNodeHasLessons($nodeID, $type)
    {
        /*
         * We use two strategies to determine if the given tree node has lessons
         */
        $calendar = $this->_activeScheduleData->calendar;
        if ($type == "room")
        {
            /*
             * If the given $type is room we search directly in the calendar list
             * because the rooms are assigned to the lesson in the calendar.
             */
            foreach ($calendar as $calendarValue)
            {
                if (is_object($calendarValue))
                {
                    foreach ($calendarValue as $blockValue)
                    {
                        foreach ($blockValue as $lessonValue)
                        {
                            if (isset($lessonValue->{$nodeID}) == true)
                            {
                                return true;
                            }
                        }
                    }
                }
            }
        }
        else
        {
            /*
             * If the given $type is subject, module or teacher we search for all lesson that has
             * the $nodeID as a subject/module/teacher.
             * And then we search the lessonID in the calendar.
             */
 
            $fieldType = $type . "s";
 
            $filterFunction = function($obj) use ($fieldType, $nodeID)
            {
                return isset($obj->{$fieldType}->{$nodeID});
            };

            $lessons = array_filter((array) $this->_activeScheduleData->lessons, $filterFunction);
 
            $lessonKeys = array_keys($lessons);
 
            foreach ($calendar as $calendarValue)
            {
                if (is_object($calendarValue))
                {
                    foreach ($calendarValue as $blockValue)
                    {
                        foreach ($lessonKeys as $lessonKeyValue)
                        {
                            if (isset($blockValue->{$lessonKeyValue}) == true)
                            {
                                return true;
                            }
                        }
                    }
                }
            }
        }
 
        return false;
    }
}
