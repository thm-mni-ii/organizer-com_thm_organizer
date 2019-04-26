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
define('SCHEDULE', 1);
define('ALTERNATING', 2);
define('CONTENT', 3);

require_once JPATH_SITE . '/components/com_thm_organizer/Helpers/teachers.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Class retrieves information about daily events for display on monitors.
 */
class THM_OrganizerModelRoom_Display extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    public $blocks = [];

    private $grid;

    public $monitorID = null;

    public $params = [];

    public $roomID = null;

    /**
     * Constructor
     *
     * @throws  Exception
     */
    public function __construct()
    {
        parent::__construct();
        $this->setRoomData();
        $this->ensureComponentTemplate();
        $this->setDisplayParams();
        $this->setGrid();
        $this->getDay();
    }

    /**
     * Redirects to the component template if it has not already been done
     *
     * @return void redirects to component template
     */
    protected function ensureComponentTemplate()
    {
        $app         = OrganizerHelper::getApplication();
        $templateSet = $app->input->getString('tmpl', '') == 'component';
        if (!$templateSet) {
            $query = $app->input->server->get('QUERY_STRING', '', 'raw') . '&tmpl=component';
            $app->redirect(Uri::root() . 'index.php?' . $query);
        }
    }

    /**
     * Gets the room information for a day
     *
     * @return void  room information for the given day is added to the $blocks object variable
     */
    private function getDay()
    {
        $date     = date('Y-m-d');
        $isSunday = date('l', strtotime($date)) == 'Sunday';
        if ($isSunday) {
            return;
        }

        $events = $this->getEvents($date);
        $blocks = $this->processBlocks($events);

        if (count($blocks)) {
            $this->blocks = $blocks;

            return;
        }
    }

    /**
     * Sets event information for the given block in the given schedule
     *
     * @param string $date the date on which the events occur
     *
     * @return array the events for the given date
     */
    protected function getEvents($date)
    {
        $shortTag = Languages::getShortTag();

        $query = $this->_db->getQuery(true);

        $select = "DISTINCT conf.id, conf.configuration, cal.startTime, cal.endTime, ";
        $select .= "l.id as lessonID, m.abbreviation_$shortTag AS method, ";
        $select .= "ps.name AS psName, s.name_$shortTag AS sName";
        $query->select($select)
            ->from('#__thm_organizer_calendar AS cal')
            ->innerJoin('#__thm_organizer_calendar_configuration_map AS ccm ON ccm.calendarID = cal.id')
            ->innerJoin('#__thm_organizer_lesson_configurations AS conf ON ccm.configurationID = conf.id')
            ->innerJoin('#__thm_organizer_lessons AS l ON cal.lessonID = l.id')
            ->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id AND conf.lessonID = ls.id')
            ->innerJoin('#__thm_organizer_plan_subjects AS ps ON ls.subjectID = ps.id')
            ->leftJoin('#__thm_organizer_methods AS m ON l.methodID = m.id')
            ->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id')
            ->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id')
            ->where("cal.schedule_date = '$date'")
            ->where("cal.delta != 'removed'")
            ->where("l.delta != 'removed'")
            ->where("ls.delta != 'removed'");
        $this->_db->setQuery($query);

        $results = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($results)) {
            return [];
        }

        $events = [];
        foreach ($results as $result) {

            $configuration = json_decode($result['configuration'], true);
            $relevant      = $this->hasRelevantRoom($configuration['rooms']);
            if (!$relevant) {
                continue;
            }

            $startTime = substr(str_replace(':', '', $result['startTime']), 0, 4);
            $endTime   = substr(str_replace(':', '', $result['endTime']), 0, 4);
            $times     = "$startTime-$endTime";

            if (empty($events[$times])) {
                $events[$times] = [];
            }

            $lessonID = $result['lessonID'];

            if (empty($events[$startTime][$lessonID])) {
                $events[$times][$lessonID]              = [];
                $events[$times][$lessonID]['titles']    = [];
                $events[$times][$lessonID]['teachers']  = [];
                $events[$times][$lessonID]['method']    = empty($result['method']) ? '' : " - {$result['method']}";
                $events[$times][$lessonID]['startTime'] = $startTime;
                $events[$times][$lessonID]['endTime']   = $endTime;
            }

            $title = empty($result['sName']) ? $result['psName'] : $result['sName'];

            if (!in_array($title, $events[$times][$lessonID]['titles'])) {
                $events[$times][$lessonID]['titles'][] = $title;
            }

            if (empty($events[$times][$lessonID]['teachers'])) {
                $events[$times][$lessonID]['teachers'] = $this->getEventTeachers($configuration['teachers']);
            } else {
                $existingTeachers                      = $events[$times][$lessonID]['teachers'];
                $newTeachers                           = $this->getEventTeachers($configuration['teachers']);
                $events[$times][$lessonID]['teachers'] = array_merge($existingTeachers, $newTeachers);
            }
        }

        return $events;
    }

    /**
     * Adds the teacher names to the teacher instances index.
     *
     * @param array $instanceTeachers the teachers associated with the instance
     *
     * @return array an array of teachers in the form id => 'surname(s), forename(s)'
     */
    private function getEventTeachers(&$instanceTeachers)
    {
        $teachers = [];

        foreach ($instanceTeachers as $teacherID => $delta) {
            if ($delta == 'removed') {
                unset($instanceTeachers[$teacherID]);
                continue;
            }

            $teachers[$teacherID] = THM_OrganizerHelperTeachers::getLNFName($teacherID);
        }

        asort($teachers);

        return $teachers;
    }

    /**
     * Adds the room names to the room instances index, if the room was requested.
     *
     * @param array $instanceRooms the rooms associated with the instance
     *
     * @return bool true if the instance is associated with a requested room
     */
    private function hasRelevantRoom(&$instanceRooms)
    {
        foreach ($instanceRooms as $roomID => $delta) {
            if ($delta == 'removed' or $roomID != $this->roomID) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Determines which display behaviour is desired based on which layout was previously used
     *
     * @return void
     */
    private function setAlternatingLayout()
    {
        $session   = Factory::getSession();
        $displayed = $session->get('displayed', 'schedule');

        if ($displayed == 'schedule') {
            $this->params['layout'] = 'content';
        }

        if ($displayed == 'content') {
            $this->params['layout'] = 'schedule';
        }

        $session->set('displayed', $this->params['layout']);
    }

    /**
     * Sets display parameters
     *
     * @return void
     */
    private function setDisplayParams()
    {
        if (!empty($this->monitorID)) {
            $monitorEntry = \JTable::getInstance('monitors', 'thm_organizerTable');
            $monitorEntry->load($this->monitorID);
        }

        if (isset($monitorEntry) and !$monitorEntry->useDefaults) {
            $this->params['display']          = empty($monitorEntry->display) ? SCHEDULE : $monitorEntry->display;
            $this->params['schedule_refresh'] = $monitorEntry->schedule_refresh;
            $this->params['content_refresh']  = $monitorEntry->content_refresh;
            $this->params['content']          = $monitorEntry->content;
        } else {
            $params                           = OrganizerHelper::getParams();
            $this->params['display']          = $params->get('display', SCHEDULE);
            $this->params['schedule_refresh'] = $params->get('schedule_refresh', 60);
            $this->params['content_refresh']  = $params->get('content_refresh', 60);

            $this->params['content'] = OrganizerHelper::getParams()->get('content');
        }

        // No need for special handling if no content has been set
        if (empty($this->params['content'])) {
            $this->params['display'] = SCHEDULE;
        }

        switch ($this->params['display']) {
            case ALTERNATING:
                $this->setAlternatingLayout();
                break;
            case CONTENT:
                $this->params['layout'] = 'content';
                break;
            case SCHEDULE:
            default:
                $this->params['layout'] = 'schedule';
        }
    }

    /**
     * Gets the main grid from the first schedule
     *
     * @return void  sets the object grid variable
     */
    private function setGrid()
    {
        $query = $this->_db->getQuery(true);
        $query->select('grid')->from('#__thm_organizer_grids')->where("defaultGrid = '1'");
        $this->_db->setQuery($query);

        $rawGrid = OrganizerHelper::executeQuery('loadResult');

        if (!empty($rawGrid)) {
            $this->grid = json_decode($rawGrid, true);
        }
    }

    /**
     * Checks whether the accessing agent is a registered monitor
     *
     * @return void sets instance variables
     */
    private function setRoomData()
    {
        $input        = OrganizerHelper::getInput();
        $ipData       = ['ip' => $input->server->getString('REMOTE_ADDR', '')];
        $monitorEntry = \JTable::getInstance('monitors', 'thm_organizerTable');
        $roomEntry    = \JTable::getInstance('rooms', 'thm_organizerTable');
        $registered   = $monitorEntry->load($ipData);

        if ($registered and !empty($monitorEntry->roomID)) {
            $this->monitorID = $monitorEntry->id;
            $exists          = $roomEntry->load($monitorEntry->roomID);

            if ($exists) {
                $this->roomID   = $roomEntry->id;
                $this->roomName = $roomEntry->name;

                return;
            }
        }

        $name = $input->getString('name');

        if (!empty($name)) {
            $roomData = ['name' => $name];
            $exists   = $roomEntry->load($roomData);
            if ($exists) {
                $this->roomID   = $roomEntry->id;
                $this->roomName = $name;

                return;
            }
        }

        // Room could not be resolved => redirect to home
        OrganizerHelper::getApplication()->redirect(Uri::root());
    }

    /**
     * Resolves the daily events to their respective grid blocks
     *
     * @param array $events the events for the given day
     *
     * @return array the blocks
     */
    private function processBlocks($events)
    {
        $blocks = [];
        foreach ($this->grid['periods'] as $blockNo => $block) {
            $blocks[$blockNo]              = [];
            $blockStartTime                = THM_OrganizerHelperDate::formatTime($block['startTime']);
            $blockEndTime                  = THM_OrganizerHelperDate::formatTime($block['endTime']);
            $blocks[$blockNo]['startTime'] = $blockStartTime;
            $blocks[$blockNo]['endTime']   = $blockEndTime;
            $blocks[$blockNo]['lessons']   = [];

            foreach ($events as $times => $eventInstances) {
                list($eventStartTime, $eventEndTime) = explode('-', $times);
                $eventStartTime = THM_OrganizerHelperDate::formatTime($eventStartTime);
                $eventEndTime   = THM_OrganizerHelperDate::formatTime($eventEndTime);
                $before         = $eventEndTime < $blockStartTime;
                $after          = $eventStartTime > $blockEndTime;

                if ($before or $after) {
                    continue;
                }

                $divTime    = '';
                $startSynch = $blockStartTime == $eventStartTime;
                $endSynch   = $blockEndTime == $eventEndTime;

                if (!$startSynch or !$endSynch) {
                    $startTime = THM_OrganizerHelperDate::formatTime($eventStartTime);
                    $endTime   = THM_OrganizerHelperDate::formatTime($eventEndTime);
                    $divTime   .= " ($startTime -  $endTime)";
                }

                foreach ($eventInstances as $lessonID => $eventInstance) {
                    $instanceTeachers = $eventInstance['teachers'];
                    if (empty($blocks[$blockNo]['lessons'][$lessonID])) {
                        $blocks[$blockNo]['lessons'][$lessonID]             = [];
                        $blocks[$blockNo]['lessons'][$lessonID]['teachers'] = $instanceTeachers;
                        $blocks[$blockNo]['lessons'][$lessonID]['titles']   = $eventInstance['titles'];
                        $blocks[$blockNo]['lessons'][$lessonID]['method']   = $eventInstance['method'];
                        $blocks[$blockNo]['lessons'][$lessonID]['divTime']  = $divTime;
                        continue;
                    }

                    $existingTeachers = $blocks[$blockNo]['lessons'][$lessonID]['teachers'];

                    $blocks[$blockNo]['lessons'][$lessonID]['teachers']
                        = array_unique(array_merge($instanceTeachers, $existingTeachers));

                    $blocks[$blockNo]['lessons'][$lessonID]['titles'] = array_unique(array_merge(
                        $blocks[$blockNo]['lessons'][$lessonID]['titles'],
                        $eventInstance['titles']
                    ));
                }
            }
        }

        return $blocks;
    }
}
