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
     * Checkboxes for children, maybe
     *
     * @var    Object
     */
    private $_checkBoxForChildrenOnly = null;

    /**
     * Contains the current languageTag
     *
     * @var    Object
     */
    private $_languageTag = "DE_de";

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

        $menuID = JFactory::getApplication()->input->getInt("menuID", 0);
        if (!empty($menuID))
        {
            $isBackend = true;
        }
        else
        {
            $menuID = JFactory::getApplication()->input->getInt("Itemid", 0);
            $isBackend = false;
        }

        $this->_checkBoxForChildrenOnly = JFactory::getApplication()->input->getBool("childrenCheckbox", false);

        $menuItem = JFactory::getApplication()->getMenu()->getItem($menuID);

        if (empty($menuItem))
        {
            $options["hide"] = false;
            $this->_checked = array();
            $publicDefaultID = array();
            $this->_publicDefaultNode = array();
            $this->departmentSemesterSelection = JFactory::getApplication()->input->getString('departmentSemesterSelection');
        }
        else
        {
            $activeItemLanguage = $menuItem->language;
            
            /* Set your tag */
            $this->_languageTag = $activeItemLanguage;
            /* Set your extension (component or module) */
            $extension = "com_thm_organizer";
            /* Get the Joomla core language object */
            $language = JFactory::getLanguage();
            /* Set the base directory for the language */
            $base_dir = JPATH_SITE;
            /* Load the language */
            if ($this->_languageTag === "en-GB")
            {
                $language->load($extension, $base_dir, $this->_languageTag, true);
            }
            
            if ($isBackend)
            {
                $this->_hideCheckBox = false;
            }
            else
            {
                $this->_hideCheckBox = true;
            }

            $menuparams = $menuItem->params;

            if (isset($options["path"]))
            {
                $this->_checked = (array) $options["path"];
            }
            else
            {
                $treeIDs = JFactory::getApplication()->input->getString('treeIDs');
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

            uksort($this->_checked, array($this, "checkedSortFunction"));

            if (isset($options["publicDefault"]))
            {
                $this->_publicDefault = (array) $options["publicDefault"];
            }
            else
            {
                $publicDefaultID = json_decode(JFactory::getApplication()->input->getString('publicDefaultID'));
                if ($publicDefaultID != null)
                {
                    $this->_publicDefault = (array) $publicDefaultID;
                }
                else
                {
                    $this->_publicDefault = (array) json_decode($menuparams->get("publicDefaultID"));
                }
            }

            if (JFactory::getApplication()->input->getString('departmentSemesterSelection') == "")
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
                $this->departmentSemesterSelection = JFactory::getApplication()->input->getString('departmentSemesterSelection');
            }
        }
    }
    
    /**
     * Method to sort the checked array !_DONT DELETE_ this function, it is used for uksort()!
     * 
     * @param   String  $firstElement  First argument
     * @param   String  $secondElement  Second argument
     * 
     * @return  integer
     */
    private function checkedSortFunction ($firstElement, $secondElement)
    {                
        $countA = substr_count($firstElement, ";");
        $countB = substr_count($secondElement, ";");

        return $countB - $countA;
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

        if (($this->_hideCheckBox == true && !$this->_checkBoxForChildrenOnly) || (!$nodeData["leaf"] && $this->_checkBoxForChildrenOnly))
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
                $stringPosition = strpos($nodeID, $checkedKey . ";");
                if ($stringPosition !== false)
                {
                    if ($checkedValue === "selected" || $checkedValue === "intermediate")
                    {
                        return true;
                    }
                    elseif($checkedValue === "hidden")
                    {
                        return false;
                    }
                    else
                    {
                        
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

        // Get ids for teachers and rooms
        $schedulerModel = JModelLegacy::getInstance('scheduler', 'thm_organizerModel', array('ignore_request' => false, 'display_type' => 4));
        $rooms = $schedulerModel->getRooms();
        $teachers = $schedulerModel->getTeachers();

        $activeSchedule = $schedulerModel->getActiveSchedule($this->departmentSemesterSelection);

        if (is_object($activeSchedule) && is_string($activeSchedule->schedule))
        {
            $activeScheduleData = json_decode($activeSchedule->schedule);
            
            // To save memory unset schedule
            unset($activeSchedule->schedule);

            if ($activeScheduleData != null)
            {
                $this->_activeScheduleData = $activeScheduleData;
                if (isset($activeScheduleData->pools))
                {
                    $this->_treeData["module"] = $activeScheduleData->pools;
                }
                else
                {
                    $this->_treeData["module"] = $activeScheduleData->modules;
                }
                $this->_treeData["room"] = $activeScheduleData->rooms;
                $this->_treeData["teacher"] = $activeScheduleData->teachers;
                $this->_treeData["subject"] = $activeScheduleData->subjects;                
                $this->_treeData["roomtype"] = $activeScheduleData->roomtypes;
                $this->_treeData["degree"] = $activeScheduleData->degrees;
                $this->_treeData["field"] = $activeScheduleData->fields;
                
                $siteLanguage = JFactory::getLanguage()->getTag();
                $tag = ($this->_languageTag === "en-GB" || $siteLanguage === "en-GB")?
                        'en' : 'de';
                $subjectsData = $schedulerModel->getDBData($tag);
                $this->setDBData($subjectsData);
                
            }
            else
            {
                // Cant decode json
                return JError::raiseWarning(404, JText::_('COM_THM_ORGANIZER_SCHEDULER_DATA_FLAWED'));
            }

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
     * Method to get the english subject names from the db
     * 
     * @param   Array  $dbSubjects  An array with the subjects from the database
     * 
     * @return  void
     */
    private function setDBData($dbSubjects)
    {
        $subjects = $this->_treeData["subject"];
        foreach ($subjects as $subject)
        {
            if (isset($dbSubjects[$subject->subjectNo]))
            {
                $subject->longname = $dbSubjects[$subject->subjectNo]->name;
                $subject->shortname = $dbSubjects[$subject->subjectNo]->shortname;
                $subject->abbreviation = $dbSubjects[$subject->subjectNo]->abbreviation;
                $subject->link = JRoute::_($dbSubjects[$subject->subjectNo]->link);
            }
        }
        $this->_treeData["subject"] = $subjects;
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
                if (count($viewNode) === 0)
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

            if (!empty($itemField) && !empty($itemFieldType->{$itemField}) && !in_array($itemField, $descriptions))
            {
                $descriptions[$itemField] = $itemFieldType->{$itemField};
            }
        }
        
        // Special node that contains all nodes
        $descriptionALLKey = "ALL";
        
        $allNodeData = array();
        $allNodeData["nodeID"] = $key . ";" . $descriptionALLKey;
        $allNodeData["text"] = JText::_('COM_THM_ORGANIZER_SCHEDULER_DATA_MYSCHED_ALL');
        $allNodeData["iconCls"] = "studiengang-root";
        $allNodeData["leaf"] = false;
        $allNodeData["draggable"] = true;
        $allNodeData["singleClickExpand"] = false;
        $allNodeData["gpuntisID"] = "mySched_ALL";
        $allNodeData["type"] = $scheduleType;
        $allNodeData["children"] = array();
        $allNodeData["semesterID"] = $semesterID;
        $allNodeData["nodeKey"] = $descriptionALLKey;
        
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
                    if (!empty($childValue->shortname))
                    {
                        $nodeName = $childValue->shortname;
                    }
                    elseif (!empty($childValue->longname))
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
                }

                $childNode = null;
                $childAllNode = null;
                if ($hasLessons)
                {
                    $childNodeData = array();
                    $childNodeData["nodeID"] = $nodeID;
                    $childNodeData["text"] = $nodeName;
                    $childNodeData["iconCls"] = "leaf" . "-node";
                    $childNodeData["leaf"] = true;
                    $childNodeData["draggable"] = true;
                    $childNodeData["singleClickExpand"] = false;
                    $childNodeData["gpuntisID"] = $childValue->gpuntisID;
                    $childNodeData["type"] = $scheduleType;
                    $childNodeData["children"] = null;
                    $childNodeData["semesterID"] = $semesterID;
                    $childNodeData["nodeKey"] = $childKey;

                    $childNode = $this->createTreeNode($childNodeData);

                    $childNodeData["nodeID"] = str_replace(";" . $descriptionKey . ";", ";" . $descriptionALLKey . ";", $childNodeData["nodeID"]);
                    
                    $childAllNode = $this->createTreeNode($childNodeData);
                }
                if (is_object($childNode))
                {
                    $childNodes[] = $childNode;
                }
                
                if (is_object($childAllNode))
                {
                    array_push($allNodeData["children"], $childAllNode);
                }
            }

            if (empty($childNodes))
            {
                $childNodes = null;
            }
            $descriptionNode = null;

            $descriptionNodeData = array();
            $descriptionNodeData["nodeID"] = $descriptionID;
            $descriptionNodeData["text"] = $descriptionValue->name;
            $descriptionNodeData["iconCls"] = "studiengang-root";
            $descriptionNodeData["leaf"] = false;
            $descriptionNodeData["draggable"] = true;
            $descriptionNodeData["singleClickExpand"] = false;
            $descriptionNodeData["gpuntisID"] = $descriptionValue->gpuntisID;
            $descriptionNodeData["type"] = $scheduleType;
            $descriptionNodeData["children"] = $childNodes;
            $descriptionNodeData["semesterID"] = $semesterID;
            $descriptionNodeData["nodeKey"] = $descriptionKey;

            $descriptionNode = $this->createTreeNode($descriptionNodeData);
            
            $treeNode = $this->checkTreeNode($treeNode, $descriptionNode, $childNodes);
        }
        
        $descriptionNode = $this->createTreeNode($allNodeData);
        $treeNode = $this->checkTreeNode($treeNode, $descriptionNode, null);
        
        return $treeNode;

    }

    /**
     * Method to check the treeNode structure.
     * 
     * @param   Object  $treeNode         The overall tree node
     * @param   Object  $descriptionNode  A subnode to add
     * @param   Object  $childNodes       Child nodes
     * 
     * @return  Object
     */
    private function checkTreeNode ($treeNode, $descriptionNode, $childNodes)
    {
        if (is_object($descriptionNode) AND !empty($descriptionNode->children) OR is_array($descriptionNode))
        {
            if ($childNodes === $descriptionNode && count($treeNode) === 0)
            {
                $treeNode = $descriptionNode;
            }
            else
            {
                if (is_array($descriptionNode))
                {
                    $treeNode = array_merge($treeNode, $descriptionNode);
                }
                else
                {
                    $treeNode[] = $descriptionNode;
                }
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
                if (!isset($obj->{$fieldType}))
                {
                    if ($fieldType === "modules")
                    {
                        $fieldType = "pools";
                    }
                }
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
