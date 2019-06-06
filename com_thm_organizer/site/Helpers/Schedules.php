<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Exception;
use Joomla\CMS\Factory;

/**
 * Provides general functions for schedule access checks, data retrieval and display.
 */
class Schedules
{
    const SEMESTER_MODE = 1;

    const PERIOD_MODE = 2;

    const INSTANCE_MODE = 3;

    /**
     * Adds the date clauses to the query
     *
     * @param array   $parameters the parameters configuring the export
     * @param object &$query      the query object
     *
     * @return void modifies the query object
     */
    private static function addDateClauses($parameters, &$query)
    {
        $dates = self::getDates($parameters);
        $query->where("c.schedule_date >= '{$dates['startDate']}'");
        $query->where("c.schedule_date <= '{$dates['endDate']}'");
    }

    /**
     * Requested resources are not restrictive amongst themselves
     *
     * @param array   $parameters the request parameters
     * @param object &$query      the query object
     *
     * @return void modifies the query object
     */
    private static function addResourceClauses($parameters, &$query)
    {
        $wherray = [];

        if (!empty($parameters['groupIDs'])) {
            $wherray[] = "gr.id IN ('" . implode("', '", $parameters['groupIDs']) . "')";
        }

        if (!empty($parameters['teacherIDs'])) {
            foreach ($parameters['teacherIDs'] as $teacherID) {
                $regexp = '"teachers":\\{[^\}]*"' . $teacherID . '"';
                $regexp .= (empty($parameters['delta'])) ? ':("new"|"")' : '';

                $wherray[] = "conf.configuration REGEXP '$regexp'";
            }
        }

        if (!empty($parameters['roomIDs'])) {
            foreach ($parameters['roomIDs'] as $roomID) {
                $regexp = '"rooms":\\{[^\}]*"' . $roomID . '"';
                $regexp .= (empty($parameters['delta'])) ? ':("new"|"")' : '';

                $wherray[] = "conf.configuration REGEXP '$regexp'";
            }
        }

        if (!empty($parameters['subjectIDs'])) {
            $wherray[] = "co.id IN ('" . implode("', '", $parameters['subjectIDs']) . "')";
        }

        if (!empty($parameters['lessonIDs'])) {
            $wherray[] = "l.id IN ('" . implode("', '", $parameters['lessonIDs']) . "')";
        }

        $query->where('(' . implode(' OR ', $wherray) . ')');
    }

    /**
     * Aggregates the distinct lesson configurations to distinct instances
     *
     * @param mixed  $lessons   the lessons which should get aggregated
     * @param string $deltaDate representing date in which deltas gets accepted
     *
     * @return array
     */
    private static function aggregateInstances($lessons, $deltaDate)
    {
        $aggregatedLessons = [];
        $delta             = empty($deltaDate) ?
            date('Y-m-d H:i:s', strtotime('now')) : date('Y-m-d H:i:s', strtotime($deltaDate));

        foreach ($lessons as $lesson) {
            $date         = $lesson['date'];
            $lessonID     = $lesson['lessonID'];
            $subjectDelta = (empty($lesson['subjectDelta']) or $lesson['subjectsModified'] < $delta) ?
                '' : $lesson['subjectDelta'];
            $startTime    = substr(str_replace(':', '', $lesson['startTime']), 0, 4);
            $endTime      = substr(str_replace(':', '', $lesson['endTime']), 0, 4);
            $times        = "$startTime-$endTime";

            if (empty($aggregatedLessons[$date])) {
                $aggregatedLessons[$date] = [];
            }

            if (empty($aggregatedLessons[$date][$times])) {
                $aggregatedLessons[$date][$times] = [];
            }

            if (empty($aggregatedLessons[$date][$times][$lessonID])) {
                $aggregatedLessons[$date][$times][$lessonID]              = [];
                $aggregatedLessons[$date][$times][$lessonID]['ccmID']     = empty($lesson['ccmID']) ? '' : $lesson['ccmID'];
                $aggregatedLessons[$date][$times][$lessonID]['comment']   = empty($lesson['comment']) ? '' : $lesson['comment'];
                $aggregatedLessons[$date][$times][$lessonID]['endTime']   = $lesson['endTime'];
                $aggregatedLessons[$date][$times][$lessonID]['full']      = !Courses::canAcceptParticipant($lessonID);
                $aggregatedLessons[$date][$times][$lessonID]['gridID']    = $lesson['gridID'];
                $aggregatedLessons[$date][$times][$lessonID]['method']    = empty($lesson['method']) ? '' : $lesson['method'];
                $aggregatedLessons[$date][$times][$lessonID]['regType']   = $lesson['regType'];
                $aggregatedLessons[$date][$times][$lessonID]['startTime'] = $lesson['startTime'];
                $aggregatedLessons[$date][$times][$lessonID]['subjects']  = [];

                $aggregatedLessons[$date][$times][$lessonID]['lessonDelta']
                    = (empty($lesson['lessonDelta']) or $lesson['lessonModified'] < $delta) ? '' : $lesson['lessonDelta'];

                $aggregatedLessons[$date][$times][$lessonID]['calendarDelta']
                    = (empty($lesson['calendarDelta']) or $lesson['calendarModified'] < $delta) ? '' : $lesson['calendarDelta'];
            }

            $subjectData = self::getSubjectData($lesson);
            $subjectName = $subjectData['name'];

            $configuration             = json_decode($lesson['configuration'], true);
            $configuration['modified'] = empty($lesson['configModified']) ? '' : $lesson['configModified'];
            self::resolveConfiguration($configuration, $delta);

            if (empty($aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName])) {
                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]                = $subjectData;
                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['teachers']    = $configuration['teachers'];
                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['rooms']       = $configuration['rooms'];
                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['programs']    = [];
                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['groupDeltas'] = [];
            } else {
                $previousTeachers = $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['teachers'];
                $previousRooms    = $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['rooms'];

                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['teachers']
                    = $previousTeachers + $configuration['teachers'];

                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['rooms'] = $previousRooms + $configuration['rooms'];

                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['subjectDelta'] = $subjectDelta;
            }

            $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['groupDeltas'][$lesson['groupID']]
                = (empty($lesson['groupDelta']) or $lesson['groupModified'] < $delta) ? '' : $lesson['groupDelta'];

            $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['teacherDeltas'] = $configuration['teacherDeltas'];

            $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['roomDeltas'] = $configuration['roomDeltas'];

            $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['groups'][$lesson['groupID']]
                = [
                'untisID'  => $lesson['groupUntisID'],
                'name'     => $lesson['grouoName'],
                'fullName' => $lesson['groupFullName']
            ];

            if (!empty($subjectData['subjectID'])) {
                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['programs'][$subjectData['subjectID']]
                    = Mappings::getSubjectPrograms($subjectData['subjectID']);
            }
        }

        ksort($aggregatedLessons);

        return $aggregatedLessons;
    }

    /**
     * deletes lessons in the personal schedule of a logged in user
     *
     * @return string JSON coded and deleted ccmIDs
     * @throws Exception => invalid request / unauthorized access
     */
    public static function deleteUserLesson()
    {
        $input = OrganizerHelper::getInput();

        $ccmID = $input->getString('ccmID');
        if (empty($ccmID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
        }

        $userID = Factory::getUser()->id;
        if (empty($userID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $mode     = $input->getInt('mode', self::PERIOD_MODE);
        $mappings = self::getMatchingLessons($mode, $ccmID);

        $deletedCcmIDs = [];
        foreach ($mappings as $lessonID => $ccmIDs) {
            $userLessonTable = OrganizerHelper::getTable('User_Lessons');

            if (!$userLessonTable->load(['userID' => $userID, 'lessonID' => $lessonID])) {
                continue;
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

        return $deletedCcmIDs;
    }

    /**
     * Filters the teacher ids to view access
     *
     * @param array &$teacherIDs the teacher ids.
     * @param int    $userID     the id of the user whose authorizations will be checked
     *
     * @return void removes unauthorized entries from the array
     */
    private static function filterTeacherIDs(&$teacherIDs, $userID)
    {
        if (empty($userID)) {
            $teacherIDs = [];

            return;
        }

        if (Access::isAdmin($userID) or Access::allowHRAccess()) {
            return;
        }

        $userTeacherID     = Teachers::getIDByUserID($userID);
        $accessibleDeptIDs = Access::getAccessibleDepartments('view', $userID);

        foreach ($teacherIDs as $key => $teacherID) {
            if (!empty($userTeacherID) and $userTeacherID == $teacherID) {
                continue;
            }
            $teacherDepartments = Teachers::getDepartmentIDs($teacherID);
            $overlap            = array_intersect($accessibleDeptIDs, $teacherDepartments);

            if (empty($overlap)) {
                unset($teacherIDs[$key]);
            }
        }
    }

    /**
     * Get startTime, endTime, schedule_date, day of week and subjectID from calendar_configuration_map table
     *
     * @param int $ccmID primary key of ccm
     *
     * @return object|boolean
     */
    private static function getCalendarData($ccmID)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('cal.lessonID, startTime, endTime, schedule_date, DAYOFWEEK(schedule_date) AS weekday, courseID')
            ->from('#__thm_organizer_calendar_configuration_map AS map')
            ->innerJoin('#__thm_organizer_calendar AS cal ON cal.id = map.calendarID')
            ->innerJoin('#__thm_organizer_lessons AS l ON l.id = cal.lessonID')
            ->innerJoin('#__thm_organizer_lesson_courses AS lcrs ON lcrs.lessonID = l.id')
            ->where("map.id = '$ccmID'")
            ->where("cal.delta != 'removed'");

        $query->order('map.id');
        $dbo->setQuery($query);

        $calReference = OrganizerHelper::executeQuery('loadObject');

        return empty($calReference) ? false : $calReference;
    }

    /**
     * Resolves the given date to the start and end dates for the requested time period
     *
     * @param array $parameters the schedule configuration parameters
     *
     * @return array the corresponding start and end dates
     */
    public static function getDates($parameters)
    {
        $startDayNo = empty($parameters['startDay']) ? 1 : $parameters['startDay'];
        $endDayNo   = empty($parameters['endDay']) ? 6 : $parameters['endDay'];
        $type       = $parameters['format'] == 'ics' ? 'ics' : $parameters['dateRestriction'];

        $date     = $parameters['date'];
        $dateTime = strtotime($date);

        $reqDoW = date('w', $dateTime);

        // Single day is not requested, or the day does not fall within the configured range
        $notInRange = ($type !== 'day' and !($reqDoW >= $startDayNo and $reqDoW <= $endDayNo));
        if ($notInRange) {
            if ($reqDoW === 6) {
                $dateTime = strtotime('-1 day', $dateTime);
            } else {
                $dateTime = strtotime('+1 day', $dateTime);
            }
            $date = date('Y-m-d', strtotime($dateTime));
        }

        switch ($type) {
            case 'day':
                $dates = ['startDate' => $date, 'endDate' => $date];
                break;

            case 'week':
                $dates = Dates::getWeek($date, $startDayNo, $endDayNo);
                break;

            case 'month':
                $dates = Dates::getMonth($date, $startDayNo, $endDayNo);
                break;

            case 'semester':
                $dates = Dates::getSemester($date);
                break;

            case 'ics':
                // ICS calendars get the next 6 months of data
                $dates = Dates::getICSDates($date, $startDayNo, $endDayNo);
                break;
        }

        return $dates;
    }

    /**
     * Gets the lessons for the given group ids.
     *
     * @param array $parameters array of group ids or a single group id
     *
     * @return array
     * @throws Exception => unauthorized access to teacher lessons
     */
    public static function getLessons($parameters)
    {
        if (!empty($parameters['teacherIDs'])) {
            self::filterTeacherIDs($parameters['teacherIDs'], $parameters['userID']);

            if (empty($parameters['teacherIDs'])) {
                throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
            }
        }

        if (!isset($parameters['departmentIDs'])) {
            $parameters['showUnpublished'] = Access::isAdmin();
        } else {
            $allowedIDs   = Access::getAccessibleDepartments('schedule');
            $overlap      = array_intersect($parameters['departmentIDs'], $allowedIDs);
            $overlapCount = count($overlap);

            // If the user has planning access to all requested departments show unpublished automatically.
            if ($overlapCount and $overlapCount == count($parameters['departmentIDs'])) {
                $parameters['departmentIDs']   = $overlap;
                $parameters['showUnpublished'] = true;
            } else {
                $parameters['showUnpublished'] = false;
            }
        }

        $tag   = Languages::getShortTag();
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $select = "DISTINCT ccm.id AS ccmID, l.id AS lessonID, l.comment, m.abbreviation_$tag AS method, ";
        $select .= 'l.registration_type AS regType, l.max_participants AS maxParties, ';
        $select .= 'co.id AS coID, co.name AS courseName, co.subjectNo, co.untisID AS courseUntisID, ';
        $select .= 'gr.id AS groupID, group.untisID AS groupUntisID, group.name AS groupName, ';
        $select .= 'group.full_name AS groupFullName, group.gridID, ';
        $select .= 'c.schedule_date AS date, c.startTime, c.endTime, ';
        $select .= 'conf.configuration, conf.modified AS configModified, cat.id AS categoryID';

        if (!empty($parameters['delta'])) {
            $select .= ', lg.delta AS groupDelta, lg.modified AS groupModified';
            $select .= ', lcrs.delta AS subjectsDelta, lcrs.modified AS subjectsModified';
            $select .= ', l.delta AS lessonDelta, l.modified AS lessonModified';
            $select .= ', c.delta AS calendarDelta, c.modified AS calendarModified';
            $select .= ', lt.delta AS teacherDelta, lt.modified AS teacherModified';
        }

        $query->select($select);
        self::setLessonQuery($parameters, $query);

        $query->innerJoin('#__thm_organizer_categories AS cat ON gr.categoryID = cat.id');
        $query->innerJoin('#__thm_organizer_lesson_teachers AS lt ON lt.lessonCourseID = lcrs.id');
        $query->innerJoin('#__thm_organizer_teachers AS teacher ON lt.teacherID = teacher.id');

        $query->leftJoin('#__thm_organizer_methods AS m ON l.methodID = m.id');

        if (empty($parameters['delta'])) {
            $query->where("lt.delta != 'removed'");
        } else {
            $query->where("(lt.delta != 'removed' OR (lt.delta = 'removed' AND lt.modified > '" . $parameters['delta'] . "'))");
        }

        self::addDateClauses($parameters, $query);
        $query->order('c.startTime');
        $dbo->setQuery($query);

        $rawLessons = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($rawLessons)) {
            return self::getNextAvailableDates($parameters);
        }

        $aggregatedLessons = self::aggregateInstances($rawLessons, $parameters['delta']);
        $dates             = self::getDates($parameters);
        $startDT           = strtotime($dates['startDate']);
        $endDT             = strtotime($dates['endDate']);

        for ($currentDT = $startDT; $currentDT <= $endDT; $currentDT = strtotime('+1 days', $currentDT)) {
            $index = date('Y-m-d', $currentDT);
            if (!isset($aggregatedLessons[$index])) {
                $aggregatedLessons[$index] = [];
            }
        }

        ksort($aggregatedLessons);

        if (!empty($parameters['mySchedule']) and !empty($parameters['userID'])) {
            return self::getUserFilteredLessons($aggregatedLessons, $parameters['userID']);
        }

        return $aggregatedLessons;
    }

    /**
     * Get an array with matching ccmIDs, sorted by lessonIDs
     *
     * @param int $mode  global param like self::SEMESTER_MODE
     * @param int $ccmID primary key of ccm
     *
     * @return array (lessonID => [ccmIDs])
     */
    private static function getMatchingLessons($mode, $ccmID)
    {
        $calReference = self::getCalendarData($ccmID);

        if (!$calReference) {
            return [];
        } // Only the instance selected
        elseif ($mode == self::INSTANCE_MODE) {
            return [$calReference->lessonID => [$ccmID]];
        }

        $ccmIDs = Courses::getInstances($calReference->lessonID, $mode, $calReference);

        return empty($ccmIDs) ? [] : [$calReference->lessonID => $ccmIDs];
    }

    /**
     * Searches for the next and last date where lessons can be found and returns them.
     *
     * @param array $parameters the schedule configuration parameters
     *
     * @return array next and latest available dates
     */
    public static function getNextAvailableDates($parameters)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('MIN(c.schedule_date) AS minDate');
        // Do not show dates from removed lessons
        $parameters['delta'] = null;
        self::setLessonQuery($parameters, $query);
        $query->where("c.schedule_date > '" . $parameters['date'] . "'");
        $dbo->setQuery($query);

        $futureDate = OrganizerHelper::executeQuery('loadResult');

        $query = $dbo->getQuery(true);
        $query->select('MAX(c.schedule_date) AS maxDate');
        self::setLessonQuery($parameters, $query);
        $query->where("c.schedule_date < '" . $parameters['date'] . "'");
        $dbo->setQuery($query);

        $pastDate = OrganizerHelper::executeQuery('loadResult');

        return ['pastDate' => $pastDate, 'futureDate' => $futureDate];
    }

    /**
     * Saves the term to the corresponding table if not already existent.
     *
     * @param string $termName  the abbreviation for the term
     * @param int    $startDate the integer value of the start date
     * @param int    $endDate   the integer value of the end date
     *
     * @return int id of database entry
     */
    public static function getTermID($termName, $startDate, $endDate)
    {
        $data              = [];
        $data['startDate'] = date('Y-m-d', $startDate);
        $data['endDate']   = date('Y-m-d', $endDate);

        $table  = OrganizerHelper::getTable('Terms');
        $exists = $table->load($data);
        if ($exists) {
            return $table->id;
        }

        $shortYear    = date('y', $endDate);
        $data['name'] = $termName . $shortYear;
        $table->save($data);

        return $table->id;
    }

    /**
     * Retrieves the subject data as appropriate
     *
     * @param array $lesson the lesson information
     *
     * @return array an array of subject information
     */
    private static function getSubjectData($lesson)
    {
        $return = [
            'courseID'  => $lesson['courseID'],
            'subjectID' => null,
            'subjectNo' => $lesson['subjectNo'],
            'name'      => $lesson['courseName'],
            'shortName' => $lesson['courseUntisID'],
            'abbr'      => $lesson['courseUntisID']
        ];

        $tag           = Languages::getShortTag();
        $dbo           = Factory::getDbo();
        $subjectsQuery = $dbo->getQuery(true);

        $select = "DISTINCT m.rgt, m.lft, s.id AS subjectID, s.name_$tag AS name, s.short_name_$tag AS shortName, ";
        $select .= "s.abbreviation_$tag AS abbr";

        $subjectsQuery->select($select)
            ->from('#__thm_organizer_subjects AS s')
            ->innerJoin('#__thm_organizer_subject_mappings AS sm ON sm.subjectID = s.id')
            ->leftJoin('#__thm_organizer_mappings AS m ON m.subjectID = s.id')
            ->where("sm.courseID ='{$lesson['courseID']}'");
        $dbo->setQuery($subjectsQuery);

        $mappedSubjects = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($mappedSubjects)) {
            return $return;
        }

        $tempMappings        = $mappedSubjects;
        $subject             = array_shift($tempMappings);
        $return['abbr']      = empty($subject['abbr']) ? $return['abbr'] : $subject['abbr'];
        $return['name']      = $subject['name'];
        $return['shortName'] = empty($subject['shortName']) ? $return['shortName'] : $subject['shortName'];
        $return['subjectID'] = $subject['subjectID'];

        if (count($mappedSubjects) === 1) {
            return $return;
        }

        $programQuery = $dbo->getQuery(true);

        $select = 'rgt, lft';
        $programQuery->select($select)
            ->from('#__thm_organizer_mappings AS m')
            ->innerJoin('#__thm_organizer_programs AS p ON p.id = m.programID')
            ->innerJoin('#__thm_organizer_categories AS cat ON cat.programID = p.id')
            ->innerJoin('#__thm_organizer_groups AS gr ON gr.categoryID = cat.id')
            ->where("gr.id ='{$lesson['groupID']}'");
        $dbo->setQuery($programQuery);
        $programMapping = OrganizerHelper::executeQuery('loadAssoc', []);

        if (empty($programMapping)) {
            return $return;
        }

        $left  = $programMapping['lft'];
        $right = $programMapping['rgt'];

        foreach ($mappedSubjects as $subject) {
            $found = ($subject['lft'] > $left and $subject['rgt'] < $right);

            if ($found) {
                $return['subjectID'] = $subject['subjectID'];
                $return['name']      = $subject['name'];
                $return['shortName'] = empty($subject['shortName']) ? $return['shortName'] : $subject['shortName'];
                $return['abbr']      = empty($subject['abbr']) ? $return['abbr'] : $subject['abbr'];

                break;
            }
        }

        return $return;
    }

    /**
     * Filters given lessons by their ccmIDs for the logged in user
     *
     * @param array $lessons aggregated lessons
     * @param int   $userID  the user id for personal lessons
     *
     * @return array lessonIDs as keys and ccmIDs as values
     */
    private static function getUserFilteredLessons($lessons, $userID)
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('lessonID, configuration')
            ->from('#__thm_organizer_user_lessons')
            ->where("userID = $userID");
        $dbo->setQuery($query);

        $results = OrganizerHelper::executeQuery('loadAssocList', [], 'lessonID');
        if (empty($results)) {
            return [];
        }

        $configurations = [];
        foreach ($results as $index => $result) {
            $configurations[$index] = json_decode($result['configuration']);
        }

        foreach ($lessons as $date => $blockTimes) {
            foreach ($blockTimes as $times => $lessonSet) {
                foreach ($lessonSet as $lessonID => $lessonData) {
                    if (empty($configurations[$lessonID])
                        or !in_array($lessonData['ccmID'], $configurations[$lessonID])) {
                        unset($lessons[$date][$times][$lessonID]);
                    }
                }
            }
        }

        return $lessons;
    }

    /**
     * Removes deprecated room and teacher indexes and resolves the remaining indexes to the names to be displayed
     *
     * @param mixed  &$configuration the lesson instance configuration
     * @param string  $delta         max date in which the delta gets accepted
     *
     * @return void
     */
    private static function resolveConfiguration(&$configuration, $delta)
    {
        $configuration['teacherDeltas'] = [];

        foreach ($configuration['teachers'] as $teacherID => $teacherDelta) {
            if ($teacherDelta == 'removed' and $configuration['modified'] < $delta) {
                unset($configuration['teachers'][$teacherID]);
                continue;
            }

            $configuration['teacherDeltas'][$teacherID] = $teacherDelta;
            $configuration['teachers'][$teacherID]      = Teachers::getLNFName($teacherID, true);
        }

        $configuration['roomDeltas'] = [];

        foreach ($configuration['rooms'] as $roomID => $roomDelta) {
            if ($roomDelta == 'removed' and $configuration['modified'] < $delta) {
                unset($configuration['rooms'][$roomID]);
                continue;
            }

            $configuration['roomDeltas'][$roomID] = $roomDelta;
            $configuration['rooms'][$roomID]      = Rooms::getName($roomID);
        }
    }

    /**
     * Saves lesson instance references in the personal schedule of the user
     *
     * @return array saved ccmIDs
     * @throws Exception => invalid request / unauthorized access
     */
    public static function saveUserLesson()
    {
        $input = OrganizerHelper::getInput();

        $ccmID = $input->getString('ccmID');
        if (empty($ccmID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
        }

        $userID = Factory::getUser()->id;
        if (empty($userID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $savedCcmIDs = [];
        $mode        = $input->getInt('mode', self::PERIOD_MODE);
        $mappings    = self::getMatchingLessons($mode, $ccmID);

        foreach ($mappings as $lessonID => $ccmIDs) {
            try {
                $userLessonTable = OrganizerHelper::getTable('User_Lessons');
                $hasUserLesson   = $userLessonTable->load(['userID' => $userID, 'lessonID' => $lessonID]);
            } catch (Exception $e) {
                return '[]';
            }

            $conditions = [
                'userID'      => $userID,
                'lessonID'    => $lessonID,
                'user_date'   => date('Y-m-d H:i:s'),
                'status'      => (int)Courses::canAcceptParticipant($lessonID),
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

        return $savedCcmIDs;
    }

    /**
     * Modifies query to get lessons, constrained by parameters
     *
     * @param array            $parameters the schedule configuration parameters
     * @param \JDatabaseQuery &$query      the query object
     *
     * @return void
     */
    private static function setLessonQuery($parameters, &$query)
    {
        $query->from('#__thm_organizer_lessons AS l');
        $query->innerJoin('#__thm_organizer_calendar AS c ON l.id = c.lessonID');
        $query->innerJoin('#__thm_organizer_lesson_courses AS lcrs ON lcrs.lessonID = l.id');
        $query->innerJoin('#__thm_organizer_lesson_configurations AS conf ON conf.lessonCourseID = lcrs.id');
        $query->innerJoin('#__thm_organizer_calendar_configuration_map AS ccm ON ccm.calendarID = c.id AND ccm.configurationID = conf.id');
        $query->innerJoin('#__thm_organizer_lesson_groups AS lg ON lg.lessonCourseID = lcrs.id');
        $query->innerJoin('#__thm_organizer_courses AS co ON co.id = lcrs.courseID');
        $query->innerJoin('#__thm_organizer_groups AS gr ON gr.id = lg.groupID');
        $query->leftJoin('#__thm_organizer_group_publishing AS grp ON grp.groupID = gr.id AND grp.termID = l.termID');

        if (empty($parameters['showUnpublished'])) {
            $query->where("(grp.published IS NULL OR grp.published = '1')");
        }

        if (empty($parameters['delta'])) {
            $query->where("lg.delta != 'removed'");
            $query->where("lcrs.delta != 'removed'");
            $query->where("l.delta != 'removed'");
            $query->where("c.delta != 'removed'");
        } else {
            $query->where("(lg.delta != 'removed' OR (lg.delta = 'removed' AND lg.modified > '" . $parameters['delta'] . "'))");
            $query->where("(lcrs.delta != 'removed' OR (lcrs.delta = 'removed' AND lcrs.modified > '" . $parameters['delta'] . "'))");
            $query->where("(l.delta != 'removed' OR (l.delta = 'removed' AND l.modified > '" . $parameters['delta'] . "'))");
            $query->where("(c.delta != 'removed' OR (c.delta = 'removed' AND c.modified > '" . $parameters['delta'] . "'))");
        }

        if (!empty($parameters['mySchedule']) and !empty($parameters['userID'])) {
            $query->innerJoin('#__thm_organizer_user_lessons AS u ON u.lessonID = l.id');
            $query->where('u.userID = ' . $parameters['userID']);
        } else {
            self::addResourceClauses($parameters, $query);
        }
    }
}
