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

require_once JPATH_SITE . '/components/com_thm_organizer/Helpers/rooms.php';
require_once JPATH_SITE . '/components/com_thm_organizer/Helpers/teachers.php';

/**
 * Retrieves lesson and event data for a filtered set of rooms.
 */
class THM_OrganizerModelRoom_Overview extends \Joomla\CMS\MVC\Model\FormModel
{
    const DAY = 1;
    const WEEK = 2;

    public $data = [];

    public $defaultCampus = 0;

    public $defaultTemplate = self::DAY;

    public $endDate = [];

    public $grid = [];

    public $rooms = [];

    public $startDate = [];

    public $types = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->populateState();
        $this->setRooms();
        $this->setGrid();
        $this->setData();
    }

    /**
     * Method to get the form
     *
     * @param array   $data     Data for the form.
     * @param boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return \JForm|boolean  A \JForm object on success, false on failure
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_thm_organizer.room_overview',
            'room_overview',
            ['control' => 'jform', 'load_data' => true]
        );

        return !empty($form) ? $form : false;
    }

    /**
     * Method to auto-populate the model state.
     *
     * @return void
     */
    protected function populateState()
    {
        $input         = OrganizerHelper::getInput();
        $params        = OrganizerHelper::getParams();
        $format        = $params->get('dateFormat', 'd.m.Y');
        $formData      = $input->get('jform', [], 'array');
        $defaultDate   = date($format);
        $defaultCampus = $params->get('campusID', 0);

        if (empty($formData)) {
            $formData['template']   = self::DAY;
            $formData['date']       = $defaultDate;
            $formData['campusID']   = $defaultCampus;
            $formData['buildingID'] = '';
            $formData['types']      = [''];
            $formData['rooms']      = [''];
        } else {
            $reqTemplate          = empty($formData['template']) ? self::DAY : (int)$formData['template'];
            $validTemplates       = [self::DAY, self::WEEK];
            $templateValid        = in_array($reqTemplate, $validTemplates);
            $formData['template'] = $templateValid ? $reqTemplate : self::DAY;

            $reqDate          = empty($formData['date']) ? $defaultDate : $formData['date'];
            $dateValid        = strtotime($reqDate) !== false;
            $formData['date'] = $dateValid ? date($format, strtotime($reqDate)) : $defaultDate;

            $formData['campusID']   = empty($formData['campusID']) ? $defaultCampus : (int)$formData['campusID'];
            $formData['buildingID'] = empty($formData['buildingID']) ? '' : (int)$formData['buildingID'];

            if (isset($formData['types'])) {
                $formData['types'] = Joomla\Utilities\ArrayHelper::toInteger($formData['types']);
                if (count($formData['types']) > 1) {
                    $zeroIndex = array_search(0, $formData['types']);
                    if ($zeroIndex !== false) {
                        unset($formData['types'][$zeroIndex]);
                    }
                }
            } else {
                $formData['types'] = [''];
            }

            if (isset($formData['rooms'])) {
                $formData['rooms'] = Joomla\Utilities\ArrayHelper::toInteger($formData['rooms']);
                if (count($formData['rooms']) > 1) {
                    $zeroIndex = array_search(0, $formData['rooms']);
                    if ($zeroIndex !== false) {
                        unset($formData['rooms'][$zeroIndex]);
                    }
                }
            } else {
                $formData['rooms'] = [''];
            }
        }

        $this->defaultCampus = $formData['campusID'];

        foreach ($formData as $index => $value) {
            $this->state->set($index, $value);
        }
    }

    /**
     * Sets the data object variable with corresponding room information
     *
     * @return void  modifies the object data variable
     */
    private function setData()
    {
        $date = THM_OrganizerHelperDate::standardizeDate($this->state->get('date'));
        switch ($this->state->get('template')) {
            case self::DAY:
                $this->startDate = $this->endDate = $date;
                $this->getDay($date);
                break;

            case self::WEEK:
                $this->startDate = date('Y-m-d', strtotime('monday this week', strtotime($date)));
                $this->endDate   = date('Y-m-d', strtotime('sunday this week', strtotime($date)));
                $this->getInterval();
                break;
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
        $query->select('grid')->from('#__thm_organizer_grids as g')->where("defaultGrid = '1'");
        $this->_db->setQuery($query);

        $defaultGrid = OrganizerHelper::executeQuery('loadResult');
        $defaultGrid = json_decode($defaultGrid, true);

        if (empty($this->defaultCampus)) {
            $this->grid = $defaultGrid;

            return;
        }

        // Attempt to load the default grid for the campus
        $query = $this->_db->getQuery(true);
        $query->select('g1.grid as grid, g2.grid as parentGrid')
            ->from('#__thm_organizer_campuses as c1')
            ->leftJoin('#__thm_organizer_grids as g1 on c1.gridID = g1.id')
            ->leftJoin('#__thm_organizer_campuses as c2 on c2.id = c1.parentID')
            ->leftJoin('#__thm_organizer_grids as g2 on c2.gridID = g2.id')
            ->where("c1.id = '$this->defaultCampus'")
            ->where('(c1.gridID IS NOT NULL OR (c1.gridID IS NULL and c2.gridID IS NOT NULL))');
        $this->_db->setQuery($query);
        $campusGrids = OrganizerHelper::executeQuery('loadAssoc', []);

        if (empty($campusGrids)) {
            $this->grid = $defaultGrid;

            return;
        }

        if (empty($campusGrids['grid'])) {
            $this->grid = json_decode($campusGrids['parentGrid'], true);

            return;
        }

        $this->grid = json_decode($campusGrids['grid'], true);
    }

    /**
     * Gets the room information for a week
     *
     * @return void sets the daily event information over the given interval
     */
    private function getInterval()
    {
        $endDT = strtotime($this->endDate);
        for ($currentDT = strtotime($this->startDate); $currentDT <= $endDT;) {
            $currentDate = date('Y-m-d', $currentDT);
            $this->getDay($currentDate);
            $currentDT = strtotime('+1 day', $currentDT);
        }
    }

    /**
     * Gets the room information for a day
     *
     * @param string $date the date string
     *
     * @return void  room information for the given day is added to the $blocks object variable
     */
    private function getDay($date)
    {
        $isSunday = date('l', strtotime($date)) == 'Sunday';
        if ($isSunday) {
            $template = $this->state->get('template');
            $getNext  = ($template == DAY and $isSunday);
            if ($getNext) {
                $date = date('Y-m-d', strtotime("$date + 1 days"));
            } else {
                return;
            }
        }

        $events = $this->getEvents($date);
        $blocks = $this->processBlocks($events);

        if (count($blocks)) {
            $this->data[$date] = $blocks;

            return;
        }
    }

    /**
     * Sets event information for the given block in the given schedule
     *
     * @param string $date the date for which to retrieve events
     *
     * @return array the events for the given date
     */
    private function getEvents($date)
    {
        $shortTag = Languages::getShortTag();

        $query = $this->_db->getQuery(true);

        $select = 'DISTINCT conf.id, conf.configuration, cal.startTime, cal.endTime, ';
        $select .= "d.short_name_$shortTag AS department, d.id AS departmentID, ";
        $select .= "l.id as lessonID, l.comment, m.abbreviation_$shortTag AS method, ";
        $select .= "ps.name AS psName, s.name_$shortTag AS sName";
        $query->select($select)
            ->from('#__thm_organizer_calendar AS cal')
            ->innerJoin('#__thm_organizer_calendar_configuration_map AS ccm ON ccm.calendarID = cal.id')
            ->innerJoin('#__thm_organizer_lesson_configurations AS conf ON ccm.configurationID = conf.id')
            ->innerJoin('#__thm_organizer_lessons AS l ON cal.lessonID = l.id')
            ->innerJoin('#__thm_organizer_departments AS d ON l.departmentID = d.id')
            ->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id AND conf.lessonID = ls.id')
            ->innerJoin('#__thm_organizer_plan_subjects AS ps ON ls.subjectID = ps.id')
            ->innerJoin('#__thm_organizer_lesson_pools AS lp ON lp.subjectID = ls.id')
            ->innerJoin('#__thm_organizer_plan_pools AS pp ON lp.poolID = pp.id')
            ->leftJoin('#__thm_organizer_plan_pool_publishing AS ppp ON ppp.planPoolID = pp.id AND ppp.planningPeriodID = l.planningPeriodID')
            ->leftJoin('#__thm_organizer_methods AS m ON l.methodID = m.id')
            ->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id')
            ->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id')
            ->where("cal.schedule_date = '$date'")
            ->where("cal.delta != 'removed'")
            ->where("l.delta != 'removed'")
            ->where("ls.delta != 'removed'")
            ->where("(ppp.published IS NULL OR ppp.published = '1')");
        $this->_db->setQuery($query);

        $results = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($results)) {
            return [];
        }

        $events = [];
        foreach ($results as $result) {
            $startTime = substr(str_replace(':', '', $result['startTime']), 0, 4);
            $endTime   = substr(str_replace(':', '', $result['endTime']), 0, 4);
            $times     = "$startTime-$endTime";

            if (empty($events[$times])) {
                $events[$times] = [];
            }

            $lessonID = $result['lessonID'];

            if (empty($events[$startTime][$lessonID])) {
                $events[$times][$lessonID]                = [];
                $events[$times][$lessonID]['departments'] = [];
                $events[$times][$lessonID]['titles']      = [];
                $events[$times][$lessonID]['teachers']    = [];
                $events[$times][$lessonID]['rooms']       = [];
                $events[$times][$lessonID]['method']      = empty($result['method']) ? '' : " - {$result['method']}";
                $events[$times][$lessonID]['startTime']   = $startTime;
                $events[$times][$lessonID]['endTime']     = substr($result['endTime'], 0, 5);
            }

            $events[$times][$lessonID]['departments'][$result['departmentID']] = $result['department'];
            $events[$times][$lessonID]['comment']                              = $result['comment'];

            $title = empty($result['sName']) ? $result['psName'] : $result['sName'];

            if (!in_array($title, $events[$times][$lessonID]['titles'])) {
                $events[$times][$lessonID]['titles'][] = $title;
            }

            $configuration = json_decode($result['configuration'], true);

            foreach ($configuration['teachers'] as $teacherID => $delta) {
                $addSpeaker = ($delta != 'removed' and empty($events[$times][$lessonID]['teachers'][$teacherID]));

                if ($addSpeaker) {
                    $events[$times][$lessonID]['teachers'][$teacherID] = THM_OrganizerHelperTeachers::getLNFName($teacherID);
                }
            }

            foreach ($configuration['rooms'] as $roomID => $delta) {
                $nonExistent = empty($events[$times][$lessonID]['rooms'][$roomID]);
                $requested   = !empty($this->rooms[$roomID]);
                $addRoom     = ($delta != 'removed' and $nonExistent and $requested);

                if ($addRoom) {
                    $events[$times][$lessonID]['rooms'][$roomID] = $this->rooms[$roomID];
                }
            }
        }

        return $events;
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
            $blockStartTime                = $block['startTime'];
            $blockEndTime                  = $block['endTime'];
            $blocks[$blockNo]['startTime'] = $blockStartTime;
            $blocks[$blockNo]['endTime']   = $blockEndTime;

            foreach ($events as $times => $eventInstances) {
                list($eventStartTime, $eventEndTime) = explode('-', $times);
                $before = $eventEndTime < $blockStartTime;
                $after  = $eventStartTime > $blockEndTime;

                if ($before or $after) {
                    continue;
                }

                $divTime    = '';
                $startSynch = $blockStartTime == $eventStartTime;
                $endSynch   = $blockEndTime == $eventEndTime;

                if (!$startSynch or !$endSynch) {
                    $divTime .= THM_OrganizerHelperDate::formatTime($eventStartTime);
                    $divTime .= ' - ';
                    $divTime .= THM_OrganizerHelperDate::formatTime($eventEndTime);
                }

                foreach ($eventInstances as $eventID => $eventInstance) {
                    $instance               = [];
                    $instance['department'] = implode(' / ', $eventInstance['departments']);
                    $instance['teachers']   = implode(' / ', $eventInstance['teachers']);
                    $instance['title']      = implode(' / ', $eventInstance['titles']);
                    $instance['title']      .= $eventInstance['method'];
                    $instance['comment']    = $eventInstance['comment'];
                    $instance['divTime']    = $divTime;

                    foreach (array_keys($eventInstance['rooms']) as $roomID) {
                        $blocks[$blockNo][$roomID][$eventID] = $instance;
                    }
                }
            }
        }

        return $blocks;
    }

    /**
     * Gets the rooms and relevant room types
     *
     * @return void  sets the rooms and types object variables
     */
    private function setRooms()
    {
        $rooms = THM_OrganizerHelperRooms::getRooms();

        if (!empty($rooms)) {
            foreach ($rooms as $room) {
                $this->rooms[$room['id']] = $room;
            }
        }
    }
}
