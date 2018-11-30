<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/mapping.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/rooms.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/teachers.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/courses.php';


/**
 * Provides general functions for schedule access checks, data retrieval and display.
 */
class THM_OrganizerHelperSchedule
{
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
                $aggregatedLessons[$date][$times][$lessonID]['full']      = !THM_OrganizerHelperCourses::canAcceptParticipant($lessonID);
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
                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]               = $subjectData;
                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['teachers']   = $configuration['teachers'];
                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['rooms']      = $configuration['rooms'];
                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['programs']   = [];
                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['poolDeltas'] = [];
            } else {
                $previousTeachers = $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['teachers'];
                $previousRooms    = $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['rooms'];

                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['teachers']
                    = $previousTeachers + $configuration['teachers'];

                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['rooms'] = $previousRooms + $configuration['rooms'];

                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['subjectDelta'] = $subjectDelta;
            }

            $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['poolDeltas'][$lesson['poolID']]
                = (empty($lesson['poolDelta']) or $lesson['poolModified'] < $delta) ? '' : $lesson['poolDelta'];

            $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['teacherDeltas'] = $configuration['teacherDeltas'];

            $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['roomDeltas'] = $configuration['roomDeltas'];

            $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['pools'][$lesson['poolID']]
                = [
                'gpuntisID' => $lesson['poolGPUntisID'],
                'name'      => $lesson['poolName'],
                'fullName'  => $lesson['poolFullName']
            ];

            if (!empty($subjectData['subjectID'])) {
                $aggregatedLessons[$date][$times][$lessonID]['subjects'][$subjectName]['programs'][$subjectData['subjectID']]
                    = THM_OrganizerHelperMapping::getSubjectPrograms($subjectData['subjectID']);
            }
        }

        ksort($aggregatedLessons);

        return $aggregatedLessons;
    }

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

        if (!empty($parameters['poolIDs'])) {
            $wherray[] = "pool.id IN ('" . implode("', '", $parameters['poolIDs']) . "')";
        }

        if (!empty($parameters['teacherIDs'])) {
            foreach ($parameters['teacherIDs'] as $teacherID) {
                $regexp = '"teachers":\\{("[0-9]+":"[\w]*",)*"' . $teacherID . '"';
                $regexp .= (empty($parameters['delta'])) ? ':("new"|"")' : '';

                $wherray[] = "lc.configuration REGEXP '$regexp'";
            }
        }

        if (!empty($parameters['roomIDs'])) {
            foreach ($parameters['roomIDs'] as $roomID) {
                $regexp = '"rooms":\\{("[0-9]+":"[\w]*",)*"' . $roomID . '"';
                $regexp .= (empty($parameters['delta'])) ? ':("new"|"")' : '';

                $wherray[] = "lc.configuration REGEXP '$regexp'";
            }
        }

        if (!empty($parameters['subjectIDs'])) {
            $wherray[] = "ps.id IN ('" . implode("', '", $parameters['subjectIDs']) . "')";
        }

        if (!empty($parameters['lessonIDs'])) {
            $wherray[] = "l.id IN ('" . implode("', '", $parameters['lessonIDs']) . "')";
        }

        $query->where('(' . implode(' OR ', $wherray) . ')');
    }

    /**
     * Filters the teacher ids to view access
     *
     * @param array &$teacherIDs the teacher ids.
     *
     * @return void removes unauthorized entries from the array
     */
    private static function filterTeacherIDs(&$teacherIDs, $userID)
    {
        if (empty($userID)) {
            $teacherIDs = [];

            return;
        }

        if (THM_OrganizerHelperAccess::isAdmin($userID) or THM_OrganizerHelperAccess::allowHRAccess()) {
            return;
        }

        $userTeacherID   = THM_OrganizerHelperTeachers::getIDFromUserData($userID);
        $accessibleDeptIDs = THM_OrganizerHelperAccess::getAccessibleDepartments('schedule', $userID);

        foreach ($teacherIDs as $key => $teacherID) {
            if (!empty($userTeacherID) and $userTeacherID == $teacherID) {
                continue;
            }
            $teacherDepartments = THM_OrganizerHelperTeachers::getDepartmentIDs($teacherID);
            $overlap            = array_intersect($accessibleDeptIDs, $teacherDepartments);

            if (empty($overlap)) {
                unset($teacherIDs[$key]);
            }
        }
    }

    /**
     * Gets the lessons for the given pool ids.
     *
     * @param array $parameters array of pool ids or a single pool id
     *
     * @return array
     * @throws Exception => unauthorized access to teacher lessons
     */
    public static function getLessons($parameters)
    {
        if (!empty($parameters['teacherIDs'])) {
            self::filterTeacherIDs($parameters['teacherIDs'], $parameters['userID']);

            if (empty($parameters['teacherIDs'])) {
                throw new Exception(JText::_('COM_THM_ORGANIZER_401'), 401);
            }
        }

        if (!isset($parameters['departmentIDs'])) {
            $parameters['showUnpublished'] = THM_OrganizerHelperAccess::isAdmin();
        } else {
            $allowedIDs = THM_OrganizerHelperAccess::getAccessibleDepartments('schedule');
            $overlap    = array_intersect($parameters['departmentIDs'], $allowedIDs);

            // If the user has planning access to all requested departments show unpublished automatically.
            if (count($overlap) == count($parameters['departmentIDs'])) {
                $parameters['departmentIDs']   = $overlap;
                $parameters['showUnpublished'] = true;
            } else {
                $parameters['showUnpublished'] = false;
            }
        }

        $tag   = THM_OrganizerHelperLanguage::getShortTag();
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $select = "DISTINCT ccm.id AS ccmID, l.id AS lessonID, l.comment, m.abbreviation_$tag AS method, ";
        $select .= 'l.registration_type AS regType, l.max_participants AS maxParties, ';
        $select .= 'ps.id AS psID, ps.name AS psName, ps.subjectNo, ps.gpuntisID AS psUntisID, ';
        $select .= 'pool.id AS poolID, pool.gpuntisID AS poolGPUntisID, pool.name AS poolName, pool.full_name AS poolFullName, pool.gridID, ';
        $select .= 'c.schedule_date AS date, c.startTime, c.endTime, ';
        $select .= 'lc.configuration, lc.modified AS configModified, pp.id AS planProgramID';

        if (!empty($parameters['delta'])) {
            $select .= ', lp.delta AS poolDelta, lp.modified AS poolModified';
            $select .= ', ls.delta AS subjectsDelta, ls.modified AS subjectsModified';
            $select .= ', l.delta AS lessonDelta, l.modified AS lessonModified';
            $select .= ', c.delta AS calendarDelta, c.modified AS calendarModified';
            $select .= ', lt.delta AS teacherDelta, lt.modified AS teacherModified';
        }

        $query->select($select);
        self::setLessonQuery($parameters, $query);

        $query->innerJoin('#__thm_organizer_plan_programs AS pp ON pool.programID = pp.id');
        $query->innerJoin('#__thm_organizer_lesson_teachers AS lt ON lt.subjectID = ls.id');
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

        $rawLessons = THM_OrganizerHelperComponent::executeQuery('loadAssocList');
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
     * Retrieves the subject data as appropriate
     *
     * @param array $lesson the lesson information
     *
     * @return array an array of subject information
     */
    private static function getSubjectData($lesson)
    {
        $return = [
            'planSubjectID' => $lesson['psID'],
            'subjectID'     => null,
            'subjectNo'     => $lesson['subjectNo'],
            'name'          => $lesson['psName'],
            'shortName'     => $lesson['psUntisID'],
            'abbr'          => $lesson['psUntisID']
        ];

        $tag           = THM_OrganizerHelperLanguage::getShortTag();
        $dbo           = JFactory::getDbo();
        $subjectsQuery = $dbo->getQuery(true);

        $select = "DISTINCT m.rgt, m.lft, s.id AS subjectID, s.name_$tag AS name, s.short_name_$tag AS shortName, ";
        $select .= "s.abbreviation_$tag AS abbr";

        $subjectsQuery->select($select)
            ->from('#__thm_organizer_subjects AS s')
            ->innerJoin('#__thm_organizer_subject_mappings AS sm ON sm.subjectID = s.id')
            ->leftJoin('#__thm_organizer_mappings AS m ON m.subjectID = s.id')
            ->where("sm.plan_subjectID ='{$lesson['psID']}'");
        $dbo->setQuery($subjectsQuery);

        $mappedSubjects = THM_OrganizerHelperComponent::executeQuery('loadAssocList');
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
            ->innerJoin('#__thm_organizer_plan_programs AS ppr ON ppr.programID = p.id')
            ->innerJoin('#__thm_organizer_plan_pools AS ppo ON ppo.programID = ppr.id')
            ->where("ppo.id ='{$lesson['poolID']}'");
        $dbo->setQuery($programQuery);
        $programMapping = THM_OrganizerHelperComponent::executeQuery('loadAssoc', []);

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
     * Saves the planning period to the corresponding table if not already existent.
     *
     * @param string $ppName    the abbreviation for the planning period
     * @param int    $startDate the integer value of the start date
     * @param int    $endDate   the integer value of the end date
     *
     * @return int id of database entry
     */
    public static function getPlanningPeriodID($ppName, $startDate, $endDate)
    {
        $data              = [];
        $data['startDate'] = date('Y-m-d', $startDate);
        $data['endDate']   = date('Y-m-d', $endDate);

        $table  = JTable::getInstance('planning_periods', 'thm_organizerTable');
        $exists = $table->load($data);
        if ($exists) {
            return $table->id;
        }

        $shortYear    = date('y', $endDate);
        $data['name'] = $ppName . $shortYear;
        $table->save($data);

        return $table->id;
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
            $configuration['teachers'][$teacherID]      = THM_OrganizerHelperTeachers::getLNFName($teacherID, true);
        }

        $configuration['roomDeltas'] = [];

        foreach ($configuration['rooms'] as $roomID => $roomDelta) {
            if ($roomDelta == 'removed' and $configuration['modified'] < $delta) {
                unset($configuration['rooms'][$roomID]);
                continue;
            }

            $configuration['roomDeltas'][$roomID] = $roomDelta;
            $configuration['rooms'][$roomID]      = THM_OrganizerHelperRooms::getName($roomID);
        }
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
        $date = $parameters['date'];
        $type = $parameters['format'] == 'ics' ? 'ics' : $parameters['dateRestriction'];

        $startDayNo = empty($parameters['startDay']) ? 1 : $parameters['startDay'];
        $endDayNo   = empty($parameters['endDay']) ? 6 : $parameters['endDay'];

        $startDayName = date('l', strtotime("Sunday + $startDayNo days"));
        $endDayName   = date('l', strtotime("Sunday + $endDayNo days"));

        switch ($type) {
            case 'day':
                $dates = ['startDate' => $date, 'endDate' => $date];
                break;

            case 'week':
                $startDate = date('Y-m-d', strtotime("$startDayName this week", strtotime($date)));
                $endDate   = date('Y-m-d', strtotime("$endDayName this week", strtotime($date)));
                $dates     = ['startDate' => $startDate, 'endDate' => $endDate];
                break;

            case 'month':
                $monthStart = date('Y-m-d', strtotime('first day of this month', strtotime($date)));
                $startDate  = date('Y-m-d', strtotime("$startDayName this week", strtotime($monthStart)));
                $monthEnd   = date('Y-m-d', strtotime('last day of this month', strtotime($date)));
                $endDate    = date('Y-m-d', strtotime("$endDayName this week", strtotime($monthEnd)));
                $dates      = ['startDate' => $startDate, 'endDate' => $endDate];
                break;

            case 'semester':
                $dbo   = JFactory::getDbo();
                $query = $dbo->getQuery(true);
                $query->select('startDate, endDate')
                    ->from('#__thm_organizer_planning_periods')
                    ->where("'$date' BETWEEN startDate AND endDate");
                $dbo->setQuery($query);
                $dates = THM_OrganizerHelperComponent::executeQuery('loadAssoc', []);
                break;

            case 'ics':
                // ICS calendars get the next 6 months of data
                $startDate  = date('Y-m-d', strtotime("$startDayName this week", strtotime($date)));
                $previewEnd = date('Y-m-d', strtotime('+6 month', strtotime($date)));
                $endDate    = date('Y-m-d', strtotime("$endDayName this week", strtotime($previewEnd)));
                $dates      = ['startDate' => $startDate, 'endDate' => $endDate];
                break;
        }

        return $dates;
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
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('MIN(c.schedule_date) AS minDate');
        // Do not show dates from removed lessons
        $parameters['delta'] = null;
        self::setLessonQuery($parameters, $query);
        $query->where("c.schedule_date > '" . $parameters['date'] . "'");
        $dbo->setQuery($query);

        $futureDate = THM_OrganizerHelperComponent::executeQuery('loadResult');

        $query = $dbo->getQuery(true);
        $query->select('MAX(c.schedule_date) AS maxDate');
        self::setLessonQuery($parameters, $query);
        $query->where("c.schedule_date < '" . $parameters['date'] . "'");
        $dbo->setQuery($query);

        $pastDate = THM_OrganizerHelperComponent::executeQuery('loadResult');

        return ['pastDate' => $pastDate, 'futureDate' => $futureDate];
    }

    /**
     * Modifies query to get lessons, constrained by parameters
     *
     * @param array           $parameters the schedule configuration parameters
     * @param JDatabaseQuery &$query      the query object
     *
     * @return void
     */
    private static function setLessonQuery($parameters, &$query)
    {
        $query->from('#__thm_organizer_lessons AS l');
        $query->innerJoin('#__thm_organizer_calendar AS c ON l.id = c.lessonID');
        $query->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id');
        $query->innerJoin('#__thm_organizer_lesson_configurations AS lc ON lc.lessonID = ls.id');
        $query->innerJoin('#__thm_organizer_calendar_configuration_map AS ccm ON ccm.calendarID = c.id AND ccm.configurationID = lc.id');
        $query->innerJoin('#__thm_organizer_lesson_pools AS lp ON lp.subjectID = ls.id');
        $query->innerJoin('#__thm_organizer_plan_subjects AS ps ON ls.subjectID = ps.id');
        $query->innerJoin('#__thm_organizer_plan_pools AS pool ON pool.id = lp.poolID');
        $query->leftJoin('#__thm_organizer_plan_pool_publishing AS ppp ON pool.id = ppp.planPoolID AND l.planningPeriodID = ppp.planningPeriodID');

        if (empty($parameters['showUnpublished'])) {
            $query->where("(ppp.published IS NULL OR ppp.published = '1')");
        }

        if (empty($parameters['delta'])) {
            $query->where("lp.delta != 'removed'");
            $query->where("ls.delta != 'removed'");
            $query->where("l.delta != 'removed'");
            $query->where("c.delta != 'removed'");
        } else {
            $query->where("(lp.delta != 'removed' OR (lp.delta = 'removed' AND lp.modified > '" . $parameters['delta'] . "'))");
            $query->where("(ls.delta != 'removed' OR (ls.delta = 'removed' AND ls.modified > '" . $parameters['delta'] . "'))");
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
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('lessonID, configuration')
            ->from('#__thm_organizer_user_lessons')
            ->where("userID = $userID");
        $dbo->setQuery($query);

        $results = THM_OrganizerHelperComponent::executeQuery('loadAssocList', [], 'lessonID');
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
}
