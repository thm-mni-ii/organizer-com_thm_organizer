<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        TreeView
 * @description TreeView file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_COMPONENT_SITE . "/assets/classes/node.php";
require_once JPATH_COMPONENT_SITE . "/assets/classes/leaf.php";
/**
 * Class TreeView for component com_thm_organizer
 * Class provides methods to create the tree view for mysched
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSchedule_Navigation
{
    /**
     * Checked
     *
     * @var    string
     */
    private $_checked = array();

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
    private $_frontend = null;

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
    private $_publicDefaultNode = array();

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
    private $_languageTag = "de-DE";

    /**
     * Constructor with the configuration object
     */
    public function __construct()
    {
        $this->_checkBoxForChildrenOnly = JFactory::getApplication()->input->getBool("childrenCheckbox", false);

        $app = JFactory::getApplication();
        $menuID = $this->processMenuLocation();
        $menuItem = $app->getMenu()->getItem($menuID);

        if (empty($menuItem))
        {
            $this->schedule = $app->input->getString('departmentSemesterSelection', '');
        }
        else
        {
            $this->displayRoom = $menuItem->params->get('displayRoomSchedule', '');
            $this->displayTeacher = $menuItem->params->get('displayTeacherSchedule', '');
            $this->setMenuProperties($menuItem);
        }
    }

    /**
     * Processes the menu id and location
     * 
     * @return  int  the menu id
     */
    private function processMenuLocation()
    {
        $input = JFactory::getApplication()->input;
        $backendID = $input->getInt("menuID", -1);
        $frontendID = $input->get("Itemid", 0);
        if ($backendID < 0)
        {
            $this->_frontend = true;
            return $frontendID;
        }
        $this->_frontend = false;
        return $backendID;
    }

    /**
     * Sets properties which depend on the menu settings
     * 
     * @param   object  &$menuItem  the menu item
     * 
     * @return  void  sets object variables
     */
    private function setMenuProperties(&$menuItem)
    {
        $menuLanguage = $menuItem->language;
        $siteLanguage = JFactory::getLanguage()->getTag();
        $this->_languageTag = ($menuLanguage == '*')? $siteLanguage : $menuLanguage;

        $language = JFactory::getLanguage();
        $language->load('com_thm_organizer', JPATH_SITE, $this->_languageTag, true);

        $params = $menuItem->params;

        $this->_checked = (array) json_decode($params->get("id"));

        uksort($this->_checked, array($this, "checkedSortFunction"));

        $this->_publicDefault = (array) json_decode($params->get("publicDefaultID"));

        $requestSchedule = JFactory::getApplication()->input->getString('departmentSemesterSelection', '');
        $paramsSchedule = $params->get('departmentSemesterSelection', '');
        $this->schedule = empty($requestSchedule)?
                $paramsSchedule : $requestSchedule;
    }

    /**
     * Function to sort the checked array. Used in uksort($array, $callback) in the
     * __construct function.
     * 
     * @param   string  $firstElement   First argument
     * @param   string  $secondElement  Second argument
     * 
     * @return  integer
     * 
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function checkedSortFunction ($firstElement, $secondElement)
    {                
        $countA = substr_count($firstElement, ";");
        $countB = substr_count($secondElement, ";");
        return $countB - $countA;
    }

    /**
     * Method to create the tree
     *
     * @return Array An array with the tree data
     */
    public function load()
    {
        $modelConfig = array('ignore_request' => false, 'display_type' => 4);
        $schedulerModel = JModelLegacy::getInstance('scheduler', 'thm_organizerModel', $modelConfig);
        $activeSchedule = $schedulerModel->getActiveSchedule($this->schedule);

        $validSchedule = (!empty($activeSchedule) AND !empty($activeSchedule->schedule));
        if (!$validSchedule)
        {
            return $this->failure();
        }
        $this->_activeScheduleData = json_decode($activeSchedule->schedule);

        unset($activeSchedule->schedule);

        $this->_treeData["pool"] = $this->_activeScheduleData->pools;
        $this->_treeData["room"] = $this->_activeScheduleData->rooms;
        $this->_treeData["teacher"] = $this->_activeScheduleData->teachers;
        $this->_treeData["subject"] = $this->_activeScheduleData->subjects;
        $this->_treeData["roomtype"] = $this->_activeScheduleData->roomtypes;
        $this->_treeData["degree"] = $this->_activeScheduleData->degrees;
        $this->_treeData["field"] = $this->_activeScheduleData->fields;

        $this->setSubjectTexts();
        $this->resolveRooms($schedulerModel->getRooms());
        $this->resolveTeachers($schedulerModel->getTeachers());

        $isDisplayed = $this->displayNode($this->schedule);

        if ($isDisplayed)
        {
            $rootNodeData = array(
                'id' => $this->schedule,
                'nodeKey' => $activeSchedule->id,
                'text' => $activeSchedule->semestername,
                'gpuntisID' => $activeSchedule->id,
                'type' => null,
                'semesterID' => $activeSchedule->id,
                'iconCls' => 'semesterjahr-root',
                'checked' => $this->_checked,
                'publicDefault' => $this->_publicDefault,
            );
            $root = new THM_OrganizerNode($rootNodeData);
        }

        $children = $this->getCategoryNodes($this->schedule, $activeSchedule->id);
        if (empty($children))
        {
            return $this->failure();
        }

        if (empty($root))
        {
            $rootCollection = $children;
        }
        else
        {
            $root->children = $children;
            $rootCollection[] = $root;
        }

        $this->expandNodeWithOnlyChild($rootCollection);

        $return = array();
        $return['success'] = true;
        $return['data'] = array(
            "tree" => $rootCollection,
            "treeData" => $this->_treeData,
            "treePublicDefault" => $this->_publicDefaultNode,
            "lessons" => $this->_activeScheduleData->lessons,
            "calendar" => $this->_activeScheduleData->calendar,
            "periods" => $this->_activeScheduleData->periods
        );
        return $return;
    }

    /**
     * Retrieves a standardized array on failure
     * 
     * @return  array
     */
    private function failure()
    {
        $failure = array();
        $failure['success'] = false;
        $failure['data'] = array("tree" => array(), "treeData" => array(), "treePublicDefault" => "");
        return $failure;
    }

    /**
     * Function to get the subject information available in the database
     * 
     * @return  void
     */
    private function setSubjectTexts()
    {
        $subjectData = $this->getSubjectData();
        if (empty($subjectData))
        {
            return;
        }
        foreach ($this->_treeData["subject"] as $subject)
        {
            if (isset($subjectData[$subject->subjectNo]))
            {
                $subject->longname = $subjectData[$subject->subjectNo]->name;
                $subject->shortname = $subjectData[$subject->subjectNo]->shortname;
                $subject->abbreviation = $subjectData[$subject->subjectNo]->abbreviation;
                $subject->link = JRoute::_($subjectData[$subject->subjectNo]->link);
                continue;
            }
            $subject->shortname = $subject->longname;
        }
    }

    /**
     * Method to get subject information from the database
     *
     * @return   Array  An Array with the subjects
     */
    public function getSubjectData()
    {
        $tag = substr($this->_languageTag, 0, 2);
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        /**
         * the menu item should be in the url as well, but i can't invest the
         * effort right now because of differentiating between calls from the
         * scheduler itself and the menu settings interface in the backend
         */
        $link = JURI::root() . 'index.php?option=com_thm_organizer&view=subject_details';
        $link .= "&languageTag=$tag&id=";
        $linkItems = array("'$link'", "id");

        $select = "externalID, name_$tag AS name, short_name_$tag AS shortname, ";
        $select .= "abbreviation_$tag AS abbreviation, ";
        $select .= $query->concatenate($linkItems) . " AS link";
        $query->select($select);
        $query->from('#__thm_organizer_subjects');
        $query->where('externalID IS NOT NULL');
        $query->where('externalID <> ""');
        $dbo->setQuery((string) $query);

        return $dbo->loadObjectList("externalID");
    }

    /**
     * Resolves the Untis rooms with the rooms stored in the database
     * 
     * @param   array  $rooms  an array of room objects
     * 
     * @return  void
     */
    private function resolveRooms($rooms)
    {
        foreach ($this->_treeData["room"] as $roomValue)
        {
            foreach ($rooms as $room)
            {
                if ($roomValue->gpuntisID === $room->gpuntisID)
                {
                    $roomValue->dbID = $room->id;
                }
            }
        }
    }

    /**
     * Resolves the Untis teachers with the teachers stored in the database
     * 
     * @param   array  $teachers  an array of teacher objects
     * 
     * @return  void
     */
    private function resolveTeachers($teachers)
    {
        foreach ($this->_treeData["teacher"] as $teacherValue)
        {
            foreach ($teachers as $teacher)
            {
                if ($teacherValue->gpuntisID === $teacher->gpuntisID)
                {
                    $teacherValue->dbID = $teacher->id;
                }
            }
        }
    }

    /**
     * Method to check if the node is checked
     *
     * @param   Integer  $nodeID  The node id
     *
     * @return Boolean true if the node is checked unless false
     */
    private function displayNode($nodeID)
    {
        // The nodes should always be displayed in the configuration
        if (!$this->_frontend)
        {
            return true;
        }

        $nodeParts = explode(";", $nodeID);

        // Do not show root node
        if(count($nodeParts) === 4)
        {
            return false;
        }
        if (count($nodeParts) >= 5)
        {
            // 'subject' should not shown any more
            if ($nodeParts[4] === 'subject')
            {
                return false;
            }
            if ($nodeParts[4] === 'room' && $this->displayRoom == 0)
            {
                return false;
            }
            if ($nodeParts[4] === 'teacher' && $this->displayTeacher == 0)
            {
                return false;
            }
        }

        foreach ($this->_checked as $element)
        {
            if (count($nodeParts) >= 5 && stristr($nodeID, $element) !== false)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Method to create the schedule nodes (teacher, room, class)
     *
     * @param   Integer  $key         The node key
     * @param   Integer  $scheduleID  The semester id
     *
     * @return The schedule node
     */
    private function getCategoryNodes($key, $scheduleID)
    {
        $categoryNodes = array();
        $categories = array("teacher", "room", "pool");

        foreach ($categories as $category)
        {
            $nodeKey = $key . ";" . $category;
            $textConstant = 'COM_THM_ORGANIZER_SCHEDULER_' . $category . 'PLAN';

            $isDisplayed = $this->displayNode($nodeKey);
            if ($isDisplayed)
            {
                $categoryNodeData = array(
                    'id' => $nodeKey,
                    'nodeKey' => $nodeKey,
                    'text' => JText::_($textConstant),
                    'gpuntisID' => $category,
                    'type' => null,
                    'semesterID' => $scheduleID,
                    'iconCls' => 'view-root',
                    'checked' => $this->_checked,
                    'publicDefault' => $this->_publicDefault
                );
                $categoryNode = new THM_OrganizerNode($categoryNodeData);
            }

            $allDisplayed = ($category != 'pool')? $this->displayNode($nodeKey . ";ALL") : false;
            $subcategories = $this->getSubcategoryNodes($nodeKey, $category, $scheduleID, $allDisplayed);

            if (empty($subcategories))
            {
                continue;
            }

            if (!empty($categoryNode))
            {
                $categoryNode->children = $subcategories;
                $categoryNodes[] = $categoryNode;
            }
            else
            {
                $categoryNodes[] = array_merge($categoryNodes, $subcategories);
            }
        }

        return $categoryNodes;

    }

    /**
     * Method to get the schedule lessons
     *
     * @param   integer  $key           the node key
     * @param   string   $category      the resource category
     * @param   integer  $scheduleID    the schedule id
     * @param   boolean  $allDisplayed  whether the all node should be displayed
     *
     * @return  array  an array of  subcategory nodes
     */
    private function getSubcategoryNodes($key, $category, $scheduleID, $allDisplayed)
    {
        $subcategoryNodes = array();
        $allNodes = array();
        $subcategories = array();
        $subcategoriesData = $this->_treeData[$category];

        foreach ($subcategoriesData as $subcategoryData)
        {
            $this->setSubCategories($category, $subcategories, $subcategoryData);
        }

        foreach ($subcategories as $subcategoryKey => $subcategory)
        {
            $subcategoryID = "$key;$subcategoryKey";

            $resources = array_filter(
                (array) $subcategoriesData, function ($resource) use ($category, $subcategoryKey)
                {
                    if ($category === "pool" AND isset($resource->degree))
                    {
                        return $subcategoryKey === $resource->degree;
                    }
                    if (isset($resource->description))
                    {
                        return $subcategoryKey === $resource->description;
                    }
                    return false;
                }
            );

            $resourceNodes = array();
            $resourceParameters = array(
                'key' => $key,
                'resourceNodes' => &$resourceNodes,
                'allDisplayed' => $allDisplayed,
                'allNodes' => &$allNodes,
                'resources' => &$resources,
                'scheduleID' => $scheduleID,
                'category' => $category,
                'subcategoryID' => $subcategoryID,
                'checked' => $this->_checked,
                'publicDefault' => $this->_publicDefault
            );
            $this->setResourceNodes($resourceParameters);

            if (empty($resourceNodes))
            {
                continue;
            }

            $subcatagoryDisplayed = $this->displayNode($subcategoryID);
            if ($subcatagoryDisplayed)
            {
                $subCategoryData = array(
                    'id' => $subcategoryID,
                    'nodeKey' => $subcategoryKey,
                    'text' => $subcategory->name,
                    'gpuntisID' => $subcategory->gpuntisID,
                    'type' => $category,
                    'semesterID' => $scheduleID,
                    'iconCls' => 'studiengang-root',
                    'checked' => &$this->_checked,
                    'publicDefault' => &$this->_publicDefault
                );
                $subCategoryNode = new THM_OrganizerNode($subCategoryData);
                $subCategoryNode->children = $resourceNodes;
                $subcategoryNodes[] = $subCategoryNode;
            }
            else
            {
                $subcategoryNodes = array_merge($subcategoryNodes, $resourceNodes);
            }
        }

        if ($allDisplayed)
        {
            $allNode = $this->getAllNode($key, $category, $scheduleID);
            $allNode->children = $allNodes;
            $subcategoryNodes[] = $allNode;
        }

        return $subcategoryNodes;
    }

    /**
     * Gets the resource nodes
     * 
     * @param   array  $parameters  the parameters for the resource node creation
     * 
     * @return  void  variables are saved to the referenced arrays in $parameters
     */
    private function setResourceNodes($parameters)
    {
        foreach ($parameters['resources'] as $resourceKey => $resource)
        {
            $nodeID = "{$parameters['subcategoryID']};$resourceKey";

            $hasLessons = ($this->_frontend == false)?
                true : $this->resourceIsPlanned($resourceKey, $parameters['category']);

            $addNode = ($hasLessons AND $this->displayNode($nodeID));
            if ($addNode)
            {
                $parameters['id'] = $nodeID;
                $parameters['nodeKey'] = $resourceKey;
                $parameters['resource'] = &$resource;
                $leafNode = new THM_OrganizerLeaf($parameters);
                if ($leafNode->publicDefault === "default")
                {
                    $this->_publicDefaultNode = $leafNode;
                }
                $parameters['resourceNodes'][] = $leafNode;

                if ($parameters['allDisplayed'])
                {
                    $parameters['id'] = $parameters['key'] . ";ALL;$resourceKey";
                    $allNodeLeaf = new THM_OrganizerLeaf($parameters);
                    $parameters['allNodes'][] = $allNodeLeaf;
                }
            }
        }
    }

    /**
     * Sets the categories (nodes) for schedule navigation
     * 
     * @param   string  $type            the resource category
     * @param   array   &$subcategories  the array in which the categories are stored
     * @param   object  &$resource       the object modeling the resource
     * 
     * @return  void  sets values in the
     */
    private function setSubCategories($type, &$subcategories, &$resource)
    {
        $subcategorySet = false;
        switch ($type)
        {
            case "room":
                if (isset($resource->description))
                {
                    $subcategory = $resource->description;
                    $itemFieldType = $this->_activeScheduleData->roomtypes;
                    $subcategorySet = true;
                }
                else
                {
                    return;
                }
                break;
            case "pool":
                if (isset($resource->degree))
                {
                    $subcategory = $resource->degree;
                    $itemFieldType = $this->_activeScheduleData->degrees;
                    $subcategorySet = true;
                }
                else
                {
                    return;
                }
                break;
            case "teacher":
            case "subject":
                if (isset($resource->description))
                {
                    $subcategory = $resource->description;
                    $itemFieldType = $this->_activeScheduleData->fields;
                    $subcategorySet = true;
                }
                else
                {
                    return;
                }
                break;
        }

        $subcategoryExists = !empty($itemFieldType->$subcategory);
        $subcategoryIndexed = in_array($subcategory, $subcategories);
        $setSubCategory = ($subcategorySet AND $subcategoryExists AND !$subcategoryIndexed);
        if ($setSubCategory)
        {
            $subcategories[$subcategory] = $itemFieldType->{$subcategory};
        }
    }

    /**
     * Method to mark a node as expanded that has only one child element
     *
     * @param   array  &$children  An reference to a node child
     *
     * @return void
     */
    private function expandNodeWithOnlyChild(&$children)
    {
        foreach ($children as $child)
        {
            if (!empty($child->children) AND count($child->children) === 1)
            {
                $child->expanded = true;
                $this->expandNodeWithOnlyChild($child->children);
            }
        }
        return;
    }

    /**
     * Method to check if an tree node has lessons
     *
     * @param   object  $resourceID  the resource id
     * @param   string  $category    the resource type
     *
     * @return  boolean
     */
    private function resourceIsPlanned($resourceID, $category)
    {
        if ($category == "room")
        {
            return $this->roomIsPlanned($resourceID);
        }
        return $this->resourceInPlannedLesson($resourceID, $category);
    }

    /**
     * Checks if a room is used in a lesson
     *
     * @param   string  $roomID  the room id
     * 
     * @return  boolean  true if the room is used in a lesson
     */
    private function roomIsPlanned($roomID)
    {
        foreach ($this->_activeScheduleData->calendar as $day)
        {
            if (!is_object($day))
            {
                continue;
            }
            foreach ($day as $block)
            {
                foreach ($block as $lesson)
                {
                    if (isset($lesson->{$roomID}) == true)
                    {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Checks whether a generic resource is associated with a planned lesson
     * 
     * @param   string  $resourceID  the id of the resource
     * @param   string  $category    the resource's type
     * 
     * @return  boolean  true if the resource is associated with a planned
     *                   lesson, otherwise false
     */
    private function resourceInPlannedLesson($resourceID, $category)
    {
        $category = "{$category}s";
        $filterFunction = function($lessons) use ($resourceID, $category)
        {
            return isset($lessons->$category->$resourceID);
        };

        $lessons = array_filter((array) $this->_activeScheduleData->lessons, $filterFunction);
        $lessonKeys = array_keys($lessons);

        return $this->lessonIsPlanned($lessonKeys);
    }

    /**
     * Checks if a lesson associated with a resource is has planned instances
     * 
     * @param   object  &$lessonKeys  the lesson keys with which a resource is
     *                                associated
     * 
     * @return  boolean  true if the associated lesson is planned, otherwise
     *                   false
     */
    private function lessonIsPlanned(&$lessonKeys)
    {
        foreach ($this->_activeScheduleData->calendar as $day)
        {
            if (!is_object($day))
            {
                continue;
            }
            $lessonPlanned = $this->lessonIsPlannedInDay($day, $lessonKeys);
            if ($lessonPlanned)
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if a lesson associated with a resource is has planned instances on
     * a particular day
     * 
     * @param   object  $day          the planned lessons for a single day
     * @param   object  &$lessonKeys  the lesson keys with which a resource is
     *                                associated
     * 
     * @return  boolean  true if the associated lesson is planned, otherwise
     *                   false
     */
    private function lessonIsPlannedInDay($day, &$lessonKeys)
    {
        foreach ($day as $block)
        {
            foreach ($lessonKeys as $lessonKey)
            {
                if (isset($block->{$lessonKey}) == true)
                {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Gets a node to hold all the resources of one category
     *
     * @param   string  $categoryKey  the key of the parent category
     * @param   string  $category     the name of the parent category
     * @param   string  $scheduleID   the id of the schedule which it came from
     *
     * @return  THM_OrganizerNode
     */
    private function getAllNode($categoryKey, $category, $scheduleID)
    {
        $allNodeData = array(
            'id' => "$categoryKey;ALL",
            'nodeKey' => 'ALL',
            'text' => JText::_('COM_THM_ORGANIZER_RESOURCE_SELECTION_ALL'),
            'gpuntisID' => "mySched_ALL",
            'type' => $category,
            'semesterID' => $scheduleID,
            'iconCls' => 'studiengang-root',
            'checked' => $this->_checked,
            'publicDefault' => $this->_publicDefault
        );
        return new THM_OrganizerNode($allNodeData);

    }
}
