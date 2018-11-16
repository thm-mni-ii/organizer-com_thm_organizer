<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/courses.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/departments.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/teachers.php';

/**
 * Class retrieves information for use in a schedule display form.
 */
class THM_OrganizerModelSchedule extends JModelLegacy
{
    public $grids;

    public $departments;

    public $displayName;

    public $params;

    /**
     * THM_OrganizerModelSchedule constructor.
     *
     * @param array $config options
     *
     * @throws Exception
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setParams();
        $this->grids       = $this->getGrids();
        $this->departments = THM_OrganizerHelperDepartments::getPlanDepartments();
    }

    /**
     * Getter method for all grids in database
     *
     * @return array
     * @throws Exception
     */
    public function getGrids()
    {
        $languageTag = THM_OrganizerHelperLanguage::getShortTag();
        $query       = $this->_db->getQuery(true);
        $query->select("id, name_$languageTag AS name, grid, defaultGrid")
            ->from('#__thm_organizer_grids')
            ->order("name");
        $this->_db->setQuery($query);

        try {
            $grids = $this->_db->loadObjectList();
        } catch (RuntimeException $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

            return [];
        }

        return empty($grids) ? [] : $grids;
    }

    /**
     * gets the first default grid from all grid objects in database
     *
     * @return object JSON grid
     */
    public function getDefaultGrid()
    {
        $defaultGrids = array_filter(
            $this->grids,
            function ($var) {
                return $var->defaultGrid;
            }
        );

        return current($defaultGrids);
    }

    /**
     * Sets the parameters used to configure the display
     *
     * @return void
     * @throws Exception
     */
    private function setParams()
    {
        $input  = JFactory::getApplication()->input;
        $params = JFactory::getApplication()->getParams();

        $this->params = [];

        $allowedIDs = THM_OrganizerHelperAccess::getAccessibleDepartments('schedule');

        // Don't even set the variable if the action is implausible
        if (!empty($allowedIDs)) {
            $this->params['showUnpublished'] = (int)$params->get('showUnpublished', 0);
        }

        $reqDeptID     = $input->get('departmentID');
        $rawReqDeptIDs = $input->get('departmentIDs');

        if (empty($reqDeptID) and !empty($rawReqDeptIDs)) {
            $reqDeptID = (int)explode(',', $rawReqDeptIDs)[0];
        }

        $defaultDepartment = (int)$params->get('departmentID', 0);

        // No explicit resource selection was made check if departments were requested
        $departmentID                  = empty($reqDeptID) ? $defaultDepartment : $reqDeptID;
        $this->params['departmentID']  = $departmentID;
        $this->params['showPrograms']  = $input->getInt('showPrograms', (int)$params->get('showPrograms', 1));
        $this->params['showPools']     = $input->getInt('showPools', (int)$params->get('showPools', 1));
        $this->params['showRooms']     = $input->getInt('showRooms', (int)$params->get('showRooms', 1));
        $this->params['showRoomTypes'] = $input->getInt('showRoomTypes', (int)$params->get('showRoomTypes', 1));
        $this->params['showSubjects']  = $input->getInt('showRoomTypes', (int)$params->get('showSubjects', 1));

        $stMenuParam      = $input->getInt('showTeachers', (int)$params->get('showTeachers', 1));
        $departmentPlaner = THM_OrganizerHelperAccess::allowSchedulingAccess(0, $departmentID);
        $isTeacher        = (bool)THM_OrganizerHelperTeachers::getIDFromUserData();
        $showTeachers     = (($departmentPlaner or $isTeacher) and $stMenuParam);

        $this->params['showTeachers']    = $showTeachers;
        $this->params['deltaDays']       = $input->getInt('deltaDays', (int)$params->get('deltaDays', 5));
        $this->params['showDepartments'] = empty($this->params['departmentID']) ?
            $input->getInt('showDepartments', (int)$params->get('showDepartments', 1)) : 0;

        // Menu title requested
        if (!empty($params->get('show_page_heading'))) {
            $this->displayName = $params->get('page_heading');
        }

        $setTitle = empty($this->displayName);

        // Explicit setting of resources is done in the priority of resource type and is mutually exclusive
        if ($this->params['showPools']) {
            $this->setResourceArray('pool');
        }

        if (!empty($this->params['poolIDs'])) {
            $this->params['showDepartments'] = 0;
            $this->params['showPrograms']    = 0;
            $this->params['showRooms']       = 0;
            $this->params['showRoomTypes']   = 0;
            $this->params['showTeachers']    = 0;
            $this->params['showSubjects']    = 0;

            if (count($this->params['poolIDs']) === 1 and $setTitle) {
                require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/pools.php';
                $this->displayName           = THM_OrganizerHelperPools::getFullName($this->params['poolIDs'][0]);
                $this->params['displayName'] = $this->displayName;
            }

            return;
        }

        if ($this->params['showTeachers']) {
            $this->setResourceArray('teacher');
        }

        if (!empty($this->params['teacherIDs'])) {
            $this->params['showDepartments'] = 0;
            $this->params['showPools']       = 0;
            $this->params['showPrograms']    = 0;
            $this->params['showRooms']       = 0;
            $this->params['showRoomTypes']   = 0;
            $this->params['showSubjects']    = 0;

            if (count($this->params['teacherIDs']) === 1 and $setTitle) {
                require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/teachers.php';
                $this->displayName           = THM_OrganizerHelperTeachers::getDefaultName($this->params['teacherIDs'][0]);
                $this->params['displayName'] = $this->displayName;
            }

            return;
        }

        if ($this->params['showRooms']) {
            $this->setResourceArray('room');
        }

        if (!empty($this->params['roomIDs'])) {
            $this->params['showDepartments'] = 0;
            $this->params['showPools']       = 0;
            $this->params['showPrograms']    = 0;
            $this->params['showTeachers']    = 0;
            $this->params['showSubjects']    = 0;

            if (count($this->params['roomIDs']) === 1 and $setTitle) {
                require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/rooms.php';
                $this->displayName           = THM_OrganizerHelperRooms::getName($this->params['roomIDs'][0]);
                $this->params['displayName'] = $this->displayName;
            }

            return;
        }

        if ($this->params['showRoomTypes']) {
            $this->setResourceArray('roomType');
        }

        if (!empty($this->params['roomTypeIDs'])) {
            $this->params['showDepartments'] = 0;
            $this->params['showPools']       = 0;
            $this->params['showPrograms']    = 0;
            $this->params['showTeachers']    = 0;
            $this->params['showSubjects']    = 0;

            if (count($this->params['roomTypeIDs']) === 1 and $setTitle) {
                require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/room_types.php';
                $this->displayName           = THM_OrganizerHelperRoomTypes::getName($this->params['roomTypeIDs'][0]);
                $this->params['displayName'] = $this->displayName;
            }

            return;
        }

        if ($this->params['showSubjects']) {
            $this->setResourceArray('subject');
        }

        if (!empty($this->params['subjectIDs'])) {
            $this->params['showDepartments'] = 0;
            $this->params['showPools']       = 0;
            $this->params['showPrograms']    = 0;
            $this->params['showRooms']       = 0;
            $this->params['showRoomTypes']   = 0;
            $this->params['showTeachers']    = 0;

            // There can be only one.
            $singleValue                = array_shift($this->params['subjectIDs']);
            $this->params['subjectIDs'] = [$singleValue];

            require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/subjects.php';
            $this->displayName           = THM_OrganizerHelperSubjects::getName(
                $this->params['subjectIDs'][0],
                'plan'
            );
            $this->params['displayName'] = $this->displayName;

            return;
        }

        // Lessons are always visible, so only check input params
        $this->setResourceArray('lesson');
        if (!empty($this->params['lessonIDs'])) {
            $this->params['showDepartments'] = 0;
            $this->params['showPools']       = 0;
            $this->params['showPrograms']    = 0;
            $this->params['showRooms']       = 0;
            $this->params['showRoomTypes']   = 0;
            $this->params['showTeachers']    = 0;

            require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/subjects.php';
            $this->displayName           = THM_OrganizerHelperCourses::getName($this->params['lessonIDs'][0]);
            $this->params['displayName'] = $this->displayName;

            return;
        }

        // Program as the last setting, because the others lead directly to a schedule and program is just a form value
        if ($this->params['showPrograms']) {
            $this->setResourceArray('program');
        }

        if (!empty($this->params['programIDs'])) {
            $this->params['showDepartments'] = 0;
            $this->params['showRooms']       = 0;
            $this->params['showRoomTypes']   = 0;
            $this->params['showTeachers']    = 0;

            if (count($this->params['programIDs']) === 1 and $setTitle) {
                require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/programs.php';
                $this->displayName           = THM_OrganizerHelperPrograms::getName(
                    $this->params['programIDs'][0],
                    'plan'
                );
                $this->params['displayName'] = $this->displayName;
            }

            return;
        }

        // In the last instance the department name is used if nothing else was requested
        if ($setTitle) {
            require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/departments.php';
            $this->displayName = THM_OrganizerHelperDepartments::getName($this->params['departmentID']);
        }
    }

    /**
     * Checks for ids for a given resource type and sets them in the parameters
     *
     * @param string $resourceName the name of the resource type
     *
     * @return void sets object variable indexes
     * @throws Exception
     */
    private function setResourceArray($resourceName)
    {
        $rawResourceIDs = JFactory::getApplication()->input->get("{$resourceName}IDs", [], 'raw');

        if (empty($rawResourceIDs)) {
            $rawResourceIDs = JFactory::getApplication()->getParams()->get("{$resourceName}IDs");
        }

        if (!empty($rawResourceIDs)) {
            if (is_array($rawResourceIDs)) {
                $filteredArray = Joomla\Utilities\ArrayHelper::toInteger(array_filter($rawResourceIDs));

                if (!empty($filteredArray)) {
                    $this->params["{$resourceName}IDs"] = $filteredArray;
                }
            } elseif (is_int($rawResourceIDs)) {
                $this->params["{$resourceName}IDs"] = Joomla\Utilities\ArrayHelper::toInteger([$rawResourceIDs]);
            } elseif (is_string($rawResourceIDs)) {
                $this->params["{$resourceName}IDs"] = Joomla\Utilities\ArrayHelper::toInteger(explode(
                    ',',
                    $rawResourceIDs
                ));
            }

            $this->params['resourcesRequested'] = $resourceName;
        }
    }
}
