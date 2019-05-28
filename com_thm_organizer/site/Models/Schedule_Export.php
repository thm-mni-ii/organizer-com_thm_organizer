<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Organizer\Helpers\Departments;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Schedules;
use Organizer\Helpers\Teachers;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class retrieves information for the creation of a schedule export form.
 */
class Schedule_Export extends BaseModel
{
    public $defaultGrid = 1;

    public $docTitle;

    public $grid;

    public $lessons;

    public $pageTitle;

    public $parameters;

    /**
     * Schedule_Export constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $format        = OrganizerHelper::getInput()->getString('format');
        $lessonFormats = ['pdf', 'ics', 'xls'];

        // Don't bother setting these variables for html and raw formats
        if (in_array($format, $lessonFormats)) {
            $this->setParameters();

            if ($format === 'pdf') {
                $this->setGrid();
            }

            $this->setTitles();
            $this->lessons = Schedules::getLessons($this->parameters);
        }
    }

    /**
     * Retrieves department options
     *
     * @return array an array of department options
     */
    public function getDepartmentOptions()
    {
        $departments = Departments::getOptions(false);
        $options     = [];
        $options[''] = Languages::_('THM_ORGANIZER_SELECT_DEPARTMENT');

        foreach ($departments as $departmentID => $departmentName) {
            $options[$departmentID] = $departmentName;
        }

        return $options;
    }

    /**
     * Retrieves grid options
     *
     * @return array an array of grid options
     */
    public function getGridOptions()
    {
        $shortTag = Languages::getShortTag();
        $query    = $this->_db->getQuery(true);
        $query->select("id, name_$shortTag AS name, defaultGrid")->from('#__thm_organizer_grids');
        $this->_db->setQuery($query);

        $options = [];

        $grids = OrganizerHelper::executeQuery('loadAssocList', []);

        foreach ($grids as $grid) {
            if ($grid['defaultGrid']) {
                $this->defaultGrid = $grid['id'];
            }

            $options[$grid['id']] = $grid['name'];
        }

        return $options;
    }

    /**
     * Attempts to retrieve the titles for the document and page
     *
     * @return array the document and page names
     */
    private function getPoolTitles()
    {
        $titles  = ['docTitle' => '', 'pageTitle' => ''];
        $poolIDs = array_values($this->parameters['poolIDs']);

        if (empty($poolIDs)) {
            return $titles;
        }

        $table       = OrganizerHelper::getTable('Groups');
        $oneResource = count($poolIDs) === 1;

        foreach ($poolIDs as $poolID) {
            try {
                $success = $table->load($poolID);
            } catch (Exception $exc) {
                OrganizerHelper::message($exc->getMessage(), 'error');

                return [];
            }

            if ($success) {
                $untisID = ApplicationHelper::stringURLSafe($table->untisID);

                if ($oneResource) {
                    $titles['docTitle']  = $untisID . '_';
                    $titles['pageTitle'] = $table->full_name;

                    return $titles;
                }

                $titles['docTitle']  .= $untisID . '_';
                $titles['pageTitle'] .= empty($titles['pageTitle']) ? $table->untisID : ", {$table->untisID}";
            }
        }

        return $titles;
    }

    /**
     * Checks for ids for a given resource type and sets them in the parameters
     *
     * @param string  $resourceName the name of the resource type
     * @param array  &$parameters   the parameters array for the model
     *
     * @return void sets indexes in $parameters
     */
    private function setResourceArray($resourceName, &$parameters)
    {
        $input          = OrganizerHelper::getInput();
        $rawResourceIDs = $input->get("{$resourceName}IDs", [], 'raw');

        if (!empty($rawResourceIDs)) {
            if (is_array($rawResourceIDs)) {
                $filteredArray = array_filter($rawResourceIDs);
                if (!empty($filteredArray)) {
                    $parameters["{$resourceName}IDs"] = $filteredArray;
                }

                return;
            }

            if (is_int($rawResourceIDs)) {
                $parameters["{$resourceName}IDs"] = [$rawResourceIDs];

                return;
            }

            if (is_string($rawResourceIDs)) {
                $parameters["{$resourceName}IDs"] = explode(',', $rawResourceIDs);

                return;
            }
        }
    }

    /**
     * Attempts to retrieve the titles for the document and page
     *
     * @return array the document and page names
     */
    private function getRoomTitles()
    {
        $titles  = ['docTitle' => '', 'pageTitle' => ''];
        $roomIDs = array_values($this->parameters['roomIDs']);

        if (empty($roomIDs)) {
            return $titles;
        }

        $table       = OrganizerHelper::getTable('Rooms');
        $oneResource = count($roomIDs) === 1;

        foreach ($roomIDs as $roomID) {
            try {
                $success = $table->load($roomID);
            } catch (Exception $exc) {
                OrganizerHelper::message($exc->getMessage(), 'error');

                return [];
            }

            if ($success) {
                $untisID = ApplicationHelper::stringURLSafe($table->untisID);

                if ($oneResource) {
                    $titles['docTitle']  = $untisID . '_';
                    $titles['pageTitle'] = $table->name;

                    return $titles;
                }

                $titles['docTitle']  .= $untisID . '_';
                $titles['pageTitle'] .= empty($titles['pageTitle']) ? $table->name : ", {$table->name}";
            }
        }

        return $titles;
    }

    /**
     * Attempts to retrieve the titles for the document and page
     *
     * @return array the document and page names
     */
    private function getSubjectTitles()
    {
        $courseIDs = array_values($this->parameters['courseIDs']);
        $titles    = ['docTitle' => '', 'pageTitle' => ''];

        if (empty($courseIDs)) {
            return $titles;
        }

        $oneResource = count($courseIDs) === 1;
        $tag         = Languages::getShortTag();

        $query = $this->_db->getQuery(true);
        $query->select("co.name AS courseName, co.untisID AS untisID, s.short_name_$tag AS shortName, s.name_$tag AS name");
        $query->from('#__thm_organizer_courses AS co');
        $query->leftJoin('#__thm_organizer_subject_mappings AS sm ON co.id = sm.courseID');
        $query->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id');

        foreach ($courseIDs as $courseID) {
            $query->clear('where');
            $query->where("co.id = '$courseID'");
            $this->_db->setQuery($query);
            $courseNames = OrganizerHelper::executeQuery('loadAssoc', []);

            if (!empty($courseNames)) {
                $untisID = ApplicationHelper::stringURLSafe($courseNames['untisID']);

                if (empty($courseNames['name'])) {
                    if (empty($courseNames['shortName'])) {
                        $name = $courseNames['courseName'];
                    } else {
                        $name = $courseNames['shortName'];
                    }
                } else {
                    $name = $courseNames['name'];
                }

                if ($oneResource) {
                    $titles['docTitle']  = $untisID . '_';
                    $titles['pageTitle'] = $name;

                    return $titles;
                }

                $titles['docTitle']  .= $untisID . '_';
                $titles['pageTitle'] .= empty($titles['pageTitle']) ? $untisID : ", {$untisID}";
            }

        }

        return $titles;
    }

    /**
     * Retrieves teacher options
     *
     * @return array an array of teacher options
     */
    public function getTeacherOptions()
    {
        $teachers = Teachers::getPlanTeachers();

        $options = [];

        foreach ($teachers as $teacherName => $teacherID) {
            $options[$teacherID] = $teacherName;
        }

        return $options;
    }

    /**
     * Attempts to retrieve the titles for the document and page
     *
     * @return array the document and page names
     */
    private function getTeacherTitles()
    {
        $titles     = ['docTitle' => '', 'pageTitle' => ''];
        $teacherIDs = array_values($this->parameters['teacherIDs']);

        if (empty($teacherIDs)) {
            return $titles;
        }

        $table       = OrganizerHelper::getTable('Teachers');
        $oneResource = count($teacherIDs) === 1;

        foreach ($teacherIDs as $teacherID) {
            try {
                $success = $table->load($teacherID);
            } catch (Exception $exc) {
                OrganizerHelper::message($exc->getMessage(), 'error');

                return [];
            }

            if ($success) {
                if ($oneResource) {
                    $displayName         = Teachers::getDefaultName($teacherID);
                    $titles['docTitle']  = ApplicationHelper::stringURLSafe($displayName) . '_';
                    $titles['pageTitle'] = $displayName;

                    return $titles;
                }

                $displayName         = Teachers::getLNFName($teacherID, true);
                $untisID             = ApplicationHelper::stringURLSafe($table->untisID);
                $titles['docTitle']  .= $untisID . '_';
                $titles['pageTitle'] .= empty($titles['pageTitle']) ? $displayName : ", {$displayName}";
            }
        }

        return $titles;
    }

    /**
     * Retrieves the selected grid from the database
     *
     * @return void sets object variables
     */
    private function setGrid()
    {
        $query = $this->_db->getQuery(true);
        $query->select('grid')->from('#__thm_organizer_grids');

        if (empty($this->parameters['gridID'])) {
            $query->where("defaultGrid = '1'");
        } else {
            $query->where("id = '{$this->parameters['gridID']}'");
        }

        $this->_db->setQuery($query);

        $rawGrid = OrganizerHelper::executeQuery('loadResult');
        if (empty($rawGrid)) {
            return;
        }

        $gridSettings = json_decode($rawGrid, true);

        if (!empty($gridSettings['periods'])) {
            $this->grid = $gridSettings['periods'];
        }

        $this->parameters['startDay'] = $gridSettings['startDay'];
        $this->parameters['endDay']   = $gridSettings['endDay'];
    }

    /**
     * Sets the basic parameters from the request
     *
     * @return void sets object variables
     */
    private function setParameters()
    {
        $input = OrganizerHelper::getInput();

        $parameters                  = [];
        $parameters['departmentIDs'] = $input->get('departmentIDs', [], 'array');
        $parameters['format']        = $input->getString('format', 'pdf');
        $parameters['mySchedule']    = $input->getBool('myschedule', false);

        if (empty($parameters['mySchedule'])) {
            $this->setResourceArray('pool', $parameters);
            $this->setResourceArray('teacher', $parameters);
            $this->setResourceArray('room', $parameters);
        }

        $userName       = $input->getString('username', '');
        $authentication = urldecode($input->getString('auth', ''));

        if (!empty($userName) and !empty($authentication)) {
            $user                 = Factory::getUser($userName);
            $authenticates        = password_verify($user->email . $user->registerDate, $authentication);
            $parameters['userID'] = $authenticates ? $user->id : Factory::getUser()->id;
        } elseif (Factory::getUser()->id != 0) {
            $parameters['userID'] = Factory::getUser()->id;
        }

        $allowedLengths                = ['day', 'week', 'month', 'semester', 'custom'];
        $rawLength                     = $input->getString('dateRestriction', 'week');
        $parameters['dateRestriction'] = in_array($rawLength, $allowedLengths) ? $rawLength : 'week';

        $rawDate = $input->getString('date');
        if (empty($rawDate)) {
            $parameters['date'] = date('Y-m-d');
        } else {
            $parameters['date'] = Dates::standardizeDate($rawDate);
        }

        switch ($parameters['format']) {
            case 'pdf':
                $parameters['documentFormat'] = $input->getString('documentFormat', 'a4');
                $parameters['displayFormat']  = $input->getString('displayFormat', 'schedule');
                $parameters['gridID']         = $input->getInt('gridID', 0);
                $parameters['grouping']       = $input->getInt('grouping', 1);
                $parameters['pdfWeekFormat']  = $input->getString('pdfWeekFormat', 'sequence');
                $parameters['titles']         = $input->getInt('titles', 1);
                break;
            case 'xls':
                $parameters['documentFormat'] = $input->getString('documentFormat', 'si');
                $parameters['xlsWeekFormat']  = $input->getString('xlsWeekFormat', 'sequence');
                break;
        }
        $parameters['delta'] = 0;

        $this->parameters = $parameters;
    }

    /**
     * Sets the document and page titles
     *
     * @return void sets object variables
     */
    private function setTitles()
    {
        $docTitle      = Languages::_('THM_ORGANIZER_SCHEDULE') . '_';
        $pageTitle     = '';
        $useMySchedule = !empty($this->parameters['mySchedule']);
        $useLessons    = !empty($this->parameters['lessonIDs']);
        $useInstances  = !empty($this->parameters['instanceIDs']);
        $usePools      = !empty($this->parameters['poolIDs']);
        $useTeachers   = !empty($this->parameters['teacherIDs']);
        $useRooms      = !empty($this->parameters['roomIDs']);
        $useSubjects   = !empty($this->parameters['subjectIDs']);

        if ($useMySchedule) {
            $docTitle  = 'mySchedule_';
            $pageTitle = Languages::_('THM_ORGANIZER_MY_SCHEDULE');
        } elseif ((!$useLessons and !$useInstances) and ($usePools xor $useTeachers xor $useRooms xor $useSubjects)) {
            if ($usePools) {
                $titles    = $this->getPoolTitles();
                $docTitle  .= $titles['docTitle'];
                $pageTitle .= empty($pageTitle) ? $titles['pageTitle'] : ", {$titles['pageTitle']}";
            }

            if ($useTeachers) {
                $titles    = $this->getTeacherTitles();
                $docTitle  .= $titles['docTitle'];
                $pageTitle .= empty($pageTitle) ? $titles['pageTitle'] : ", {$titles['pageTitle']}";
            }

            if ($useRooms) {
                $titles    = $this->getRoomTitles();
                $docTitle  .= $titles['docTitle'];
                $pageTitle .= empty($pageTitle) ? $titles['pageTitle'] : ", {$titles['pageTitle']}";
            }

            if ($useSubjects) {
                $titles    = $this->getSubjectTitles();
                $docTitle  .= $titles['docTitle'];
                $pageTitle .= empty($pageTitle) ? $titles['pageTitle'] : ", {$titles['pageTitle']}";
            }
        } else {
            $docTitle  = 'Schedule_';
            $pageTitle = '';
        }

        // Constructed docTitle always ends with a '_' character at this point.
        $this->parameters['docTitle']  = $docTitle . date('Ymd');
        $this->parameters['pageTitle'] = $pageTitle;
    }
}
