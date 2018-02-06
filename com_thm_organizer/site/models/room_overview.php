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

/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/rooms.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/teachers.php';

/**
 * Retrieves lesson and event data for a filtered set of rooms.
 */
class THM_OrganizerModelRoom_Overview extends JModelForm
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
     *
     * @param array $config An array of configuration options (name, state, dbo, table_path, ignore_request).
     *
     * @throws  Exception
     */
    public function __construct($config = [])
    {
        parent::__construct();

        $app = JFactory::getApplication();
        if (!empty($app->getMenu()) AND !empty($app->getMenu()->getActive())) {
            $params              = $app->getMenu()->getActive()->params;
            $this->defaultCampus = $params->get('campusID', 0);
        }

        $this->populateState();
        $this->setRooms();
        $this->setGrid();
        $this->setData();
    }

    /**
     * Method to get the form
     *
     * @param   array   $data     Data for the form.
     * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm|boolean  A JForm object on success, false on failure
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            "com_thm_organizer.room_overview",
            "room_overview",
            ['control' => 'jform', 'load_data' => true]
        );

        return !empty($form) ? $form : false;
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param string $ordering  An optional ordering field.
     * @param string $direction An optional direction (asc|desc).
     *
     * @return  void
     * @throws Exception
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app         = JFactory::getApplication();
        $format      = $app->getParams()->get('dateFormat', 'd.m.Y');
        $formData    = $app->input->get('jform', [], 'array');
        $defaultDate = date($format);

        if (empty($formData)) {
            $formData['template']   = self::DAY;
            $formData['date']       = $defaultDate;
            $formData['campusID']   = $this->defaultCampus;
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

            $formData['campusID']   = empty($formData['campusID']) ? $this->defaultCampus : (int)$formData['campusID'];
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

        foreach ($formData AS $index => $value) {
            $this->state->set($index, $value);
        }
    }

    /**
     * Sets the data object variable with corresponding room information
     *
     * @return  void  modifies the object data variable
     */
    private function setData()
    {
        $date = THM_OrganizerHelperComponent::standardizeDate($this->state->get('date'));
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
     * @return  void  sets the object grid variable
     * @throws Exception
     */
    private function setGrid()
    {
        $query = $this->_db->getQuery(true);
        $query->select('grid')->from('#__thm_organizer_grids')->where("defaultGrid = '1'");
        $this->_db->setQuery($query);

        try {
            $rawGrid = $this->_db->loadResult();;
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

            return;
        }

        $this->grid = json_decode($rawGrid, true);
    }

    /**
     * Gets the room information for a week
     *
     * @return  void sets the daily event information over the given interval
     * @throws Exception
     */
    private function getInterval()
    {
        $dateFormat = JFactory::getApplication()->getParams()->get('dateFormat', 'Y-m-d');
        $endDT      = strtotime($this->endDate);
        for ($currentDT = strtotime($this->startDate); $currentDT <= $endDT; $currentDT = strtotime('+1 day',
            $currentDT)) {
            $currentDate = THM_OrganizerHelperComponent::standardizeDate(date($dateFormat, $currentDT));
            $this->getDay($currentDate);
        }
    }

    /**
     * Gets the room information for a day
     *
     * @param string $date the date string
     *
     * @return  void  room information for the given day is added to the $blocks object variable
     */
    private function getDay($date)
    {
        $isSunday = date('l', strtotime($date)) == 'Sunday';
        if ($isSunday) {
            $template = $this->state->get('template');
            $getNext  = ($template == DAY AND $isSunday);
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
     * @return  array the events for the given date
     * @throws Exception
     */
    private function getEvents($date)
    {
        $lang     = THM_OrganizerHelperLanguage::getLanguage();
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();

        $query = $this->_db->getQuery(true);

        $select = "DISTINCT conf.id, conf.configuration, cal.startTime, cal.endTime, ";
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

        try {
            $results = $this->_db->loadAssocList();
        } catch (Exception $exception) {
            JFactory::getApplication()->enqueueMessage($lang->_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

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

            foreach ($configuration['teachers'] AS $teacherID => $delta) {
                $addSpeaker = ($delta != 'removed' AND empty($events[$times][$lessonID]['teachers'][$teacherID]));

                if ($addSpeaker) {
                    $events[$times][$lessonID]['teachers'][$teacherID] = THM_OrganizerHelperTeachers::getLNFName($teacherID);
                }
            }

            foreach ($configuration['rooms'] AS $roomID => $delta) {
                $nonExistent = empty($events[$times][$lessonID]['rooms'][$roomID]);
                $requested   = !empty($this->rooms[$roomID]);
                $addRoom     = ($delta != 'removed' AND $nonExistent AND $requested);

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
        foreach ($this->grid['periods'] AS $blockNo => $block) {
            $blocks[$blockNo]              = [];
            $blockStartTime                = $block['startTime'];
            $blockEndTime                  = $block['endTime'];
            $blocks[$blockNo]['startTime'] = $blockStartTime;
            $blocks[$blockNo]['endTime']   = $blockEndTime;

            foreach ($events as $times => $eventInstances) {
                list($eventStartTime, $eventEndTime) = explode('-', $times);
                $before = $eventEndTime < $blockStartTime;
                $after  = $eventStartTime > $blockEndTime;

                if ($before OR $after) {
                    continue;
                }

                $divTime    = '';
                $startSynch = $blockStartTime == $eventStartTime;
                $endSynch   = $blockEndTime == $eventEndTime;

                if (!$startSynch or !$endSynch) {
                    $divTime .= THM_OrganizerHelperComponent::formatTime($eventStartTime);
                    $divTime .= ' - ';
                    $divTime .= THM_OrganizerHelperComponent::formatTime($eventEndTime);
                }

                foreach ($eventInstances as $eventID => $eventInstance) {
                    $instance               = [];
                    $instance['department'] = implode(' / ', $eventInstance['departments']);
                    $instance['teachers']   = implode(' / ', $eventInstance['teachers']);
                    $instance['title']      = implode(' / ', $eventInstance['titles']);
                    $instance['title']      .= $eventInstance['method'];
                    $instance['comment']    = $eventInstance['comment'];
                    $instance['divTime']    = $divTime;

                    foreach ($eventInstance['rooms'] as $roomID => $roomName) {
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
     * @return  void  sets the rooms and types object variables
     * @throws Exception
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
