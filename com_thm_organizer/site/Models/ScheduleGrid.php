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

namespace Organizer\Models;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Organizer\Helpers\Access;
use Organizer\Helpers\Categories;
use Organizer\Helpers\Courses;
use Organizer\Helpers\Departments;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Groups;
use Organizer\Helpers\Rooms;
use Organizer\Helpers\Teachers;

/**
 * Class retrieves information for use in a schedule display form.
 */
class ScheduleGrid extends BaseModel
{
    public $grids;

    public $departments;

    public $displayName;

    public $params;

    /**
     * Schedule constructor.
     *
     * @param array $config options
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setParams();
        $this->grids       = $this->getGrids();
        $this->departments = Departments::getOptions();
    }

    /**
     * Getter method for all grids in database
     *
     * @return array
     */
    public function getGrids()
    {
        $tag   = Languages::getTag();
        $query = $this->_db->getQuery(true);
        $query->select("id, name_$tag AS name, grid, defaultGrid")->from('#__thm_organizer_grids')->order('name');
        $this->_db->setQuery($query);

        $grids = OrganizerHelper::executeQuery('loadObjectList');

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
     */
    private function setParams()
    {
        $params = Input::getParams();

        $departmentID = Input::getFilterID('department');

        $this->params                   = [];
        $this->params['departmentID']   = $departmentID;
        $this->params['showCategories'] = Input::getInt('showCategories', $params->get('showCategories', 1));
        $this->params['showGroups']     = Input::getInt('showGroups', $params->get('showGroups', 1));
        $this->params['showRooms']      = Input::getInt('showRooms', $params->get('showRooms', 1));
        $this->params['showRoomTypes']  = Input::getInt('showRoomTypes', $params->get('showRoomTypes', 1));
        $this->params['showSubjects']   = Input::getInt('showSubjects', $params->get('showSubjects', 1));

        $stMenuParam                  = Input::getInt('showTeachers', $params->get('showTeachers', 1));
        $privilegedAccess             = Access::allowViewAccess($departmentID);
        $teacherID                    = Teachers::getIDByUserID();
        $showTeachers                 = (($privilegedAccess or !empty($teacherID)) and $stMenuParam);
        $this->params['showTeachers'] = $showTeachers;

        $deltaDays             = Input::getInt('deltaDays', $params->get('deltaDays', 5));
        $this->params['delta'] = empty($deltaDays) ? false : date('Y-m-d', strtotime('-' . $deltaDays . ' days'));

        $defaultEnabled                  = Input::getInt('showDepartments', $params->get('showDepartments', 1));
        $this->params['showDepartments'] = empty($departmentID) ? $defaultEnabled : 0;

        // Menu title requested
        if (!empty($params->get('show_page_heading'))) {
            $this->displayName = $params->get('page_heading');
        }

        $setTitle = empty($this->displayName);

        // Explicit setting of resources is done in the priority of resource type and is mutually exclusive
        if ($this->params['showGroups']) {
            $this->setResourceArray('group');
        }

        if (!empty($this->params['groupIDs'])) {
            $this->params['showCategories']  = 0;
            $this->params['showDepartments'] = 0;
            $this->params['showRooms']       = 0;
            $this->params['showRoomTypes']   = 0;
            $this->params['showSubjects']    = 0;
            $this->params['showTeachers']    = 0;

            if (count($this->params['groupIDs']) === 1 and $setTitle) {
                $this->displayName           = Groups::getFullName($this->params['groupIDs'][0]);
                $this->params['displayName'] = $this->displayName;
            }

            return;
        }

        if ($this->params['showTeachers']) {
            $this->setResourceArray('teacher');
        }

        if (!empty($this->params['teacherIDs'])) {
            $this->params['showCategories']  = 0;
            $this->params['showDepartments'] = 0;
            $this->params['showGroups']      = 0;
            $this->params['showRooms']       = 0;
            $this->params['showRoomTypes']   = 0;
            $this->params['showSubjects']    = 0;

            if (count($this->params['teacherIDs']) === 1 and $setTitle) {
                $this->displayName           = Teachers::getDefaultName($this->params['teacherIDs'][0]);
                $this->params['displayName'] = $this->displayName;
            }

            return;
        }

        if ($this->params['showRooms']) {
            $this->setResourceArray('room');
        }

        if (!empty($this->params['roomIDs'])) {
            $this->params['showCategories']  = 0;
            $this->params['showDepartments'] = 0;
            $this->params['showGroups']      = 0;
            $this->params['showSubjects']    = 0;
            $this->params['showTeachers']    = 0;

            if (count($this->params['roomIDs']) === 1 and $setTitle) {
                $this->displayName           = Rooms::getName($this->params['roomIDs'][0]);
                $this->params['displayName'] = $this->displayName;
            }

            return;
        }

        if ($this->params['showRoomTypes']) {
            $this->setResourceArray('roomType');
        }

        if (!empty($this->params['roomTypeIDs'])) {
            $this->params['showCategories']  = 0;
            $this->params['showDepartments'] = 0;
            $this->params['showGroups']      = 0;
            $this->params['showSubjects']    = 0;
            $this->params['showTeachers']    = 0;;

            if (count($this->params['roomTypeIDs']) === 1 and $setTitle) {
                $this->displayName           = RoomTypes::getName($this->params['roomTypeIDs'][0]);
                $this->params['displayName'] = $this->displayName;
            }

            return;
        }

        if ($this->params['showSubjects']) {
            $this->setResourceArray('subject');
        }

        if (!empty($this->params['subjectIDs'])) {
            $this->params['showCategories']  = 0;
            $this->params['showDepartments'] = 0;
            $this->params['showGroups']      = 0;
            $this->params['showRooms']       = 0;
            $this->params['showRoomTypes']   = 0;
            $this->params['showTeachers']    = 0;
            $this->params['showTypes']       = 0;

            // There can be only one.
            $singleValue                = array_shift($this->params['subjectIDs']);
            $this->params['subjectIDs'] = [$singleValue];

            $this->displayName           = Courses::getName($this->params['subjectIDs'][0]);
            $this->params['displayName'] = $this->displayName;

            return;
        }

        // Lessons are always visible, so only check input params
        $this->setResourceArray('lesson');
        if (!empty($this->params['lessonIDs'])) {
            $this->params['showDepartments'] = 0;
            $this->params['showGroups']      = 0;
            $this->params['showCategories']  = 0;
            $this->params['showRooms']       = 0;
            $this->params['showRoomTypes']   = 0;
            $this->params['showTeachers']    = 0;

            $this->displayName           = Courses::getNameByLessonID($this->params['lessonIDs'][0]);
            $this->params['displayName'] = $this->displayName;

            return;
        }

        // Program as the last setting, because the others lead directly to a schedule and category is just a form value
        if ($this->params['showCategories']) {
            $this->setResourceArray('category');
        }

        if (!empty($this->params['categoryIDs'])) {
            $this->params['showDepartments'] = 0;
            $this->params['showRooms']       = 0;
            $this->params['showRoomTypes']   = 0;
            $this->params['showTeachers']    = 0;

            if (count($this->params['categoryIDs']) === 1 and $setTitle) {
                $this->displayName           = Categories::getName(
                    $this->params['categoryIDs'][0],
                    'plan'
                );
                $this->params['displayName'] = $this->displayName;
            }

            return;
        }

        // In the last instance the department name is used if nothing else was requested
        if ($setTitle) {
            $this->displayName = Departments::getName($this->params['departmentID']);
        }
    }

    /**
     * Checks for ids for a given resource type and sets them in the parameters
     *
     * @param string $resourceName the name of the resource type
     *
     * @return void sets object variable indexes
     */
    private function setResourceArray($resourceName)
    {
        $resourceIDs = Input::getFilterIDs($resourceName);

        if (!empty($resourceIDs)) {
            $this->params["{$resourceName}IDs"] = $resourceIDs;
            $this->params['resourcesRequested'] = $resourceName;
        }
    }

    /**
     * sets notification value in user_profile table depending on user selection
     * @return string value of previous selection
     */
    public function setCheckboxChecked()
    {
        $userID = Factory::getUser()->id;
        if ($userID == 0) {
            return '';
        }

        $table       = '#__user_profiles';
        $profile_key = 'organizer_notify';
        $query       = $this->_db->getQuery(true);

        $query->select('profile_value')
            ->from($table)
            ->where("profile_key = '$profile_key'")
            ->where("user_id = $userID");
        $this->_db->setQuery($query);

        if (OrganizerHelper::executeQuery('loadResult') == 1) {
            return 'checked';
        } else {
            return '';
        }
    }
}
