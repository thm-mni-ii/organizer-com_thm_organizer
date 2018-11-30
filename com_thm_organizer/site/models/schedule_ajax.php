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

defined('_JEXEC') or die;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/courses.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/pools.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/programs.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/room_types.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/rooms.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/schedule.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/teachers.php';

/**
 * Class retrieves dynamic schedule information.
 */
class THM_OrganizerModelSchedule_Ajax extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    const SEMESTER_MODE = 1;
    const PERIOD_MODE = 2;
    const INSTANCE_MODE = 3;

    /**
     * deletes lessons in the personal schedule of a logged in user
     *
     * @return string JSON coded and deleted ccmIDs
     * @throws Exception => invalid request / unauthorized access
     */
    public function deleteLesson()
    {
        $input = THM_OrganizerHelperComponent::getInput();

        $ccmID = $input->getString('ccmID');
        if (empty($ccmID)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_400'), 400);
        }

        $userID = JFactory::getUser()->id;
        if (empty($userID)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $mode     = $input->getInt('mode', self::PERIOD_MODE);
        $mappings = $this->getMatchingLessons($mode, $ccmID);

        $deletedCcmIDs = [];
        foreach ($mappings as $lessonID => $ccmIDs) {
            try {
                $userLessonTable = JTable::getInstance('user_lessons', 'thm_organizerTable');
                if (!$userLessonTable->load(['userID' => $userID, 'lessonID' => $lessonID])) {
                    continue;
                }
            } catch (Exception $e) {
                return '[]';
            }

            $deletedCcmIDs = array_merge($deletedCcmIDs, $ccmIDs);

            // Delete a lesson completely? delete whole row in database
            if ($mode == self::SEMESTER_MODE) {
                $userLessonTable->delete($userLessonTable->id);
            } else {
                $configurations = array_flip(json_decode($userLessonTable->configuration));
                foreach ($ccmIDs as $ccmID) {
                    unset($configurations[$ccmID]);
                }

                $configurations = array_flip($configurations);
                if (empty($configurations)) {
                    $userLessonTable->delete($userLessonTable->id);
                } else {
                    $conditions = [
                        'id'            => $userLessonTable->id,
                        'userID'        => $userID,
                        'lessonID'      => $userLessonTable->lessonID,
                        'configuration' => array_values($configurations),
                        'user_date'     => date('Y-m-d H:i:s')
                    ];
                    $userLessonTable->bind($conditions);
                }

                $userLessonTable->store();
            }
        }

        return json_encode($deletedCcmIDs);
    }

    /**
     * Get startTime, endTime, schedule_date, day of week and subjectID from calendar_configuration_map table
     *
     * @param int $ccmID primary key of ccm
     *
     * @return object|boolean
     */
    private function getCalendarData($ccmID)
    {
        $query = $this->_db->getQuery(true);
        $query->select('cal.lessonID, startTime, endTime, schedule_date, DAYOFWEEK(schedule_date) AS weekday, subjectID')
            ->from('#__thm_organizer_calendar_configuration_map AS map')
            ->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = map.calendarID')
            ->innerJoin('#__thm_organizer_lessons AS l ON l.id = cal.lessonID')
            ->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id')
            ->where("map.id = '$ccmID'")
            ->where("cal.delta != 'removed'");

        $query->order('map.id');
        $this->_db->setQuery($query);

        $calReference = THM_OrganizerHelperComponent::executeQuery('loadObject');

        return empty($calReference) ? false : $calReference;
    }

    /**
     * get lessons by chosen resource
     *
     * @return string JSON coded lessons
     */
    public function getLessons()
    {
        $input       = THM_OrganizerHelperComponent::getInput();
        $inputParams = $input->getArray();
        $inputKeys   = array_keys($inputParams);
        $parameters  = [];
        foreach ($inputKeys as $key) {
            if (preg_match('/\w+IDs/', $key)) {
                $parameters[$key] = explode(',', $inputParams[$key]);
            }
        }

        $parameters['userID']          = JFactory::getUser()->id;
        $parameters['mySchedule']      = $input->getBool('mySchedule', false);
        $parameters['date']            = $input->getString('date', date('Y-m-d', time()));
        $parameters['dateRestriction'] = $input->getString('dateRestriction');

        if (empty($parameters['dateRestriction'])) {
            $oneDay                        = $input->getBool('oneDay', false);
            $parameters['dateRestriction'] = $oneDay ? 'day' : 'week';
        }

        $parameters['format'] = '';
        $deltaDays            = $input->getString('deltaDays', '14');
        $parameters['delta']  = empty($deltaDays) ? '' : date('Y-m-d', strtotime('-' . $deltaDays . ' days'));

        $lessons = THM_OrganizerHelperSchedule::getLessons($parameters);

        return empty($lessons) ? '[]' : json_encode($lessons);
    }

    /**
     * Get an array with matching ccmIDs, sorted by lessonIDs
     *
     * @param int $mode  global param like self::SEMESTER_MODE
     * @param int $ccmID primary key of ccm
     *
     * @return array (lessonID => [ccmIDs])
     */
    private function getMatchingLessons($mode, $ccmID)
    {
        $calReference = $this->getCalendarData($ccmID);

        if (!$calReference) {
            return [];
        } // Only the instance selected
        elseif ($mode == self::INSTANCE_MODE) {
            return [$calReference->lessonID => [$ccmID]];
        }

        $ccmIDs = THM_OrganizerHelperCourses::getInstances($calReference->lessonID, $mode, $calReference);

        return empty($ccmIDs) ? [] : [$calReference->lessonID => $ccmIDs];
    }

    /**
     * Getter method for programs
     *
     * @return string  a json coded array of available program objects
     */
    public function getPrograms()
    {
        $programs = THM_OrganizerHelperPrograms::getPlanPrograms();

        $results = [];
        foreach ($programs as $program) {
            $name           = empty($program['name']) ? $program['ppName'] : $program['name'];
            $results[$name] = $program['id'];
        }

        return empty($results) ? '[]' : json_encode($results);
    }

    /**
     * Getter method for pools
     *
     * @return string  all pools in JSON format
     */
    public function getPools()
    {
        $selectedPrograms = THM_OrganizerHelperComponent::getInput()->getString('programIDs');
        $programIDs       = explode(',', $selectedPrograms);
        $result           = THM_OrganizerHelperPools::getPlanPools(count($programIDs) == 1);

        return empty($result) ? '[]' : json_encode($result);
    }

    /**
     * Getter method for subjects associated with a given pool
     *
     * @return string a json encoded collection of pool lessons
     */
    public function getPoolLessons()
    {
        return json_encode(THM_OrganizerHelperPools::getPoolLessons());
    }

    /**
     * Getter method for subjects associated with a given pool
     *
     * @return string a json encoded collection of pool subjects
     */
    public function getPoolSubjects()
    {
        return json_encode(THM_OrganizerHelperPools::getPoolSubjects());
    }

    /**
     * Getter method for rooms in database
     *
     * @return string  all rooms in JSON format
     */
    public function getRooms()
    {
        $rooms = THM_OrganizerHelperRooms::getRooms();

        $result = [];
        foreach ($rooms as $room) {
            $result[$room['name']] = $room['id'];
        }

        return empty($result) ? '[]' : json_encode($result);
    }

    /**
     * Getter method for room types
     *
     * @return string  all room types in JSON format
     */
    public function getRoomTypes()
    {
        $types   = THM_OrganizerHelperRoomTypes::getUsedRoomTypes();
        $default = [JText::_('JALL') => '0'];

        return json_encode(array_merge($default, $types));
    }

    /**
     * Getter method for subjects associated with a given pool
     *
     * @return string a json encoded collection of subject lessons
     */
    public function getSubjectLessons()
    {
        return json_encode(THM_OrganizerHelperSubjects::getSubjectLessons());
    }

    /**
     * Getter method for teachers in database
     *
     * @return string  all teachers in JSON format
     */
    public function getTeachers()
    {
        $result = THM_OrganizerHelperTeachers::getPlanTeachers();

        return empty($result) ? '[]' : json_encode($result);
    }

    /**
     * Returns title of given resource
     *
     * @return string
     */
    public function getTitle()
    {
        $resource = THM_OrganizerHelperComponent::getInput()->getString('resource');
        $value    = THM_OrganizerHelperComponent::getInput()->getInt('value');

        switch ($resource) {
            case 'room':
                require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/rooms.php';
                $title = JText::_('COM_THM_ORGANIZER_ROOM') . ' ' . THM_OrganizerHelperRooms::getName($value);
                break;
            case 'pool':
                require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/pools.php';
                $title = THM_OrganizerHelperPools::getFullName($value);
                break;
            case 'teacher':
                require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/teachers.php';
                $title = THM_OrganizerHelperTeachers::getDefaultName($value);
                break;
            default:
                $title = '';
        }

        return $title;
    }

    /**
     * saves lessons in the personal schedule of the logged in user
     *
     * @return string JSON coded and saved ccmIDs
     * @throws Exception => invalid request / unauthorized access
     */
    public function saveLesson()
    {
        $input = THM_OrganizerHelperComponent::getInput();

        $ccmID = $input->getString('ccmID');
        if (empty($ccmID)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_400'), 400);
        }

        $userID = JFactory::getUser()->id;
        if (empty($userID)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $savedCcmIDs = [];
        $mode        = $input->getInt('mode', self::PERIOD_MODE);
        $mappings    = $this->getMatchingLessons($mode, $ccmID);

        foreach ($mappings as $lessonID => $ccmIDs) {
            try {
                $userLessonTable = JTable::getInstance('user_lessons', 'thm_organizerTable');
                $hasUserLesson   = $userLessonTable->load(['userID' => $userID, 'lessonID' => $lessonID]);
            } catch (Exception $e) {
                return '[]';
            }

            $conditions = [
                'userID'      => $userID,
                'lessonID'    => $lessonID,
                'user_date'   => date('Y-m-d H:i:s'),
                'status'      => (int)THM_OrganizerHelperCourses::canAcceptParticipant($lessonID),
                'status_date' => date('Y-m-d H:i:s'),
            ];

            if ($hasUserLesson) {
                $conditions['id'] = $userLessonTable->id;
                $oldCcmIds        = json_decode($userLessonTable->configuration);
                $ccmIDs           = array_merge($ccmIDs, array_diff($oldCcmIds, $ccmIDs));
            }

            $conditions['configuration'] = $ccmIDs;

            if ($userLessonTable->bind($conditions) and $userLessonTable->store()) {
                $savedCcmIDs = array_merge($savedCcmIDs, $ccmIDs);
            }
        }

        return json_encode($savedCcmIDs);
    }
}
