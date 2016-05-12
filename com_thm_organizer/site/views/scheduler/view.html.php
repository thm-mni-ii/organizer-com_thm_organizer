<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        thm_organizerViewScheduler
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * View class for the display of schedules
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewScheduler extends JViewLegacy
{
    private $_schedule = null;

    public $joomlaItemid = 0;

    public $searchModuleID = '';

    public $displayModuleNumber = true;

    public $semesterID = 0;

    public $params;

    public $config = array();

    public $libraries = array();

    public $fpdf, $ics, $phpexcel = false;

    public $requestResources = array();

    public $startup = null;

    public $loadLessonsOnStartUp = true;

    /**
     * Method to get extra
     *
     * @param   String  $tpl  template
     *
     * @return  mixed  false on error, otherwise void
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.tooltip');

        $app = JFactory::getApplication();
        $input = $app->input;
        $this->loadLanguage();


        $libraryInstalled = jimport('thm_core.js.extjs.extjs');
        if (!$libraryInstalled)
        {
            $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_EXTJS4_LIBRARY_NOT_INSTALLED'), 'error');
            return false;
        }

        $validRequest = $this->validateRequest();
        if (!$validRequest)
        {
            $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_NO_ACTIVE_SCHEDULE'), 'error');
            return false;
        }

        $scheduleRow = $this->getScheduleRow();
        $validRow = $this->validateSchedule($scheduleRow);
        if (!$validRow)
        {
            return false;
        }

        $this->joomlaItemid = $this->config['isMenu']? $app->getMenu()->getActive()->id : 0;

        $schedulerModel = $this->getModel();

        $this->params = $app->getMenu()->getActive()->params;

        // $eventModel->canWrite;
        $this->config['canWrite'] = false;
        $this->config['sessionID'] = $schedulerModel->getSessionID();
        $this->searchModuleID = $input->getString('moduleID', '');
        $this->displayModuleNumber = (bool) $this->params->get("displayModuleNumber", true);
        $this->config['deltaDisplayDays'] = (int) $this->params->get("deltaDisplayDays", 14);
        $this->config['displayDaysInWeek'] = (int) $this->params->get("displayDaysInWeek", 0);
        $this->config['name'] = $scheduleRow->departmentname . "_" . $scheduleRow->semestername . "_";
        $this->config['name'] .= $scheduleRow->term_startdate . "_" . $scheduleRow->term_enddate;

        $scheduleRow->creationdate = THM_OrganizerHelperComponent::formatDate($scheduleRow->creationdate);

        // Leaving this parameter alone for now because it may have side effects
        $this->semesterID = $scheduleRow->id;

        $this->_schedule->periods->length = count((array) $this->_schedule->periods);
        foreach ($this->_schedule->periods as &$period)
        {
            $this->formatPeriod($period);
        }

        $this->checkLibraries();
        $this->prepareDocument();
        $this->setStartUp($scheduleRow);
        $this->setRequestedResources();

        parent::display($tpl);
    }

    /**
     * Loads the appropriate language constants into the view context
     *
     * @return void
     */
    private function loadLanguage()
    {
        $menu = JFactory::getApplication()->getMenu();
        $tag = $menu->getActive()->language;
        $languageHandler = JFactory::getLanguage();

        if ($tag === "en-GB")
        {
            $languageHandler->load("com_thm_organizer", JPATH_SITE, $tag, true);
            $this->config['languageTag'] = "en";
        }
        else
        {
            $languageHandler->load("com_thm_organizer", JPATH_SITE, 'de-DE', true);
            $this->config['languageTag'] = "de";
        }
    }

    /**
     * Checks whether the information is available to retrieve a schedule
     *
     * @return  bool  true if the required information is available, otherwise false
     */
    private function validateRequest()
    {
        $app = JFactory::getApplication();
        $menu = $app->getMenu()->getActive();
        $requestedSchedule = $app->input->getInt('scheduleID', 0);
        $validMenu = (!empty($menu) AND !empty($menu->id));
        if ($validMenu)
        {
            $this->config['isMenu'] = true;
            return true;
        }
        elseif ($requestedSchedule > 0)
        {
            $this->config['isMenu'] = false;
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Imports libraries and sets library variables
     *
     * @return  void
     */
    private function checkLibraries()
    {
        $app = JFactory::getApplication();
        $this->fpdf = jimport('thm_core.fpdf.fpdf');
        if (!$this->fpdf)
        {
            $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_FPDF_LIBRARY_NOT_INSTALLED'), 'notice');
        }

        $this->phpexcel = jimport('thm_core.phpexcel.PHPExcel');
        if (!$this->phpexcel)
        {
            $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_PHPEXCEL_LIBRARY_NOT_INSTALLED'), 'notice');
        }

        $this->ics = jimport('thm_core.icalcreator.iCalcreator');
        if (!$this->ics)
        {
            $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_ICS_LIBRARY_NOT_INSTALLED'), 'notice');
        }
    }

    /**
     * Adds resource files to the document
     *
     * @return  void
     */
    private function prepareDocument()
    {
        $doc = JFactory::getDocument();
        $doc->addStyleSheet(JUri::root() . "/libraries/thm_core/js/extjs/css/ext-theme-gray-all.css");
        $doc->addStyleSheet(JUri::root() . "/media/com_thm_organizer/fonts/iconfont-frontend.css");
        $doc->addStyleSheet(JUri::root() . "/media/com_thm_organizer/css/scheduler.css");
        $doc->addStyleSheet(JUri::root() . "/components/com_thm_organizer/views/scheduler/tmpl/mySched/style.css");
    }

    /**
     * Retrieves the schedule row from the database.
     *
     * @return  mixed  object if successful, otherwise null
     */
    private function getScheduleRow()
    {
        $model = $this->getModel();
        $app = JFactory::getApplication();
        if ($this->config['isMenu'])
        {
            $params = $app->getMenu()->getActive()->params;
            return $model->getActiveSchedule($params->get("departmentSemesterSelection"));
        }
        else
        {
            $requestedScheduleID = $app->input->getInt("scheduleID", 0);
            return $requestedScheduleID? $model->getActiveScheduleByID($requestedScheduleID) : null;
        }
    }

    /**
     * Validates the schedule data retrieved from the database
     *
     * @param   mixed  &$scheduleRow  object if the query was successful, otherwise false
     *
     * @return  bool  true if the schedule structure matches the expected structure
     */
    private function validateSchedule(&$scheduleRow)
    {
        $app = JFactory::getApplication();
        $invalidRow = (!is_object($scheduleRow) OR !is_string($scheduleRow->schedule));
        if ($invalidRow)
        {
            $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_NO_ACTIVE_SCHEDULE'), 'error');
            return false;
        }

        $schedule = json_decode($scheduleRow->schedule);
        unset($scheduleRow->schedule);

        if (empty($schedule))
        {
            $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_SCHEDULE_FLAWED'), 'error');
            return false;
        }

        $validResources = $this->validateResources($schedule);
        if (!$validResources)
        {
            $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_SCHEDULE_FLAWED'), 'error');
            return false;
        }
        $this->_schedule = $schedule;
        return true;
    }

    /**
     * Checks whether the major resource groupings are existent and have values
     *
     * @param   object  &$schedule  the schedule object
     *
     * @return  bool  true if the schedule has values of all required resource types, otherwise false
     */
    private function validateResources(&$schedule)
    {
        $periodsEmpty = empty($schedule->periods);
        $fieldsEmpty = empty($schedule->fields);
        $roomTypesEmpty = empty($schedule->roomtypes);

        // Lessontypes was renamed to methods, but old json schedules will still have the lessontypes index
        $methodsEmpty = (empty($schedule->methods) AND empty($schedule->lessontypes));
        $degreesEmpty = empty($schedule->degrees);
        $roomsEmpty = empty($schedule->rooms);
        $subjectsEmpty = empty($schedule->subjects);
        $teachersEmpty = empty($schedule->teachers);
        $poolsEmpty = empty($schedule->pools);
        $calendarEmpty = empty($schedule->calendar);
        $lessonsEmpty = empty($schedule->lessons);

        return !($periodsEmpty OR $fieldsEmpty OR $roomTypesEmpty OR $methodsEmpty OR $degreesEmpty OR $roomsEmpty
            OR $subjectsEmpty OR $teachersEmpty OR $poolsEmpty OR $calendarEmpty OR $lessonsEmpty);
    }

    /**
     * Formats the block row names for word wrapping in HTML
     *
     * @param   mixed  &$period  object if an actual period, otherwise int for the period count
     *
     * @return  void
     */
    private function formatPeriod(&$period)
    {
        // Can also be the count of the periods
        if (!is_object($period))
        {
            return;
        }

        $validStartTime = (isset($period->starttime) AND is_string($period->starttime));
        if ($validStartTime)
        {
            $period->starttime = wordwrap($period->starttime, 2, ':', true);
        }

        $validEndTime = (isset($period->endtime) AND is_string($period->endtime));
        if ($validEndTime)
        {
            $period->endtime = wordwrap($period->endtime, 2, ':', true);
        }
    }

    /**
     * Sets the startup variable for javascript
     *
     * @param   object  &$scheduleRow  the schedule row from the database
     *
     * @return  void
     */
    private function setStartUp(&$scheduleRow)
    {
        $ajaxModel = JModelLegacy::getInstance('Ajaxhandler', 'thm_organizerModel', array('ignore_request' => false));
        $scheduleObject = array();

        $scheduleObject['curriculumColors'] = array();
        $scheduleObject["Grid.load"] = $this->_schedule->periods;
        $scheduleObject["Calendar"] = $this->_schedule->calendar;

        // $scheduleObject["Events.load"] = true; //$ajaxModel->executeTask("Events.load");
        $scheduleObject["UserSchedule.load"] = new stdClass;
        $scheduleObject["UserSchedule.load"]->respChanges = $ajaxModel->executeTask("UserSchedule.load", array("username" => "respChanges"));
        $scheduleObject["ScheduleDescription.load"] = new stdClass;
        $scheduleObject["ScheduleDescription.load"]->data = $scheduleRow;

        $params = $this->params;
        $this->loadLessonsOnStartUp = $this->config['isMenu']? (bool) $params->get('loadLessonsOnStartUp', true) : true;

        if ($this->loadLessonsOnStartUp)
        {
            $scheduleObject["Lessons"] = $this->getLessons();
        }

        $this->startup = rawurlencode(json_encode($scheduleObject));
    }

    /**
     * Retrieves the lessons for the schedule
     *
     * @return  array  the schedule lessons
     */
    private function getLessons()
    {
        $lessons = array();
        foreach ($this->_schedule->calendar as $date => $blocks)
        {
            if (!is_object($blocks))
            {
                continue;
            }
            $this->setLessonsByDate($date, $blocks, $lessons);
        }
        return $lessons;
    }

    /**
     * Sets the lessons for a particular date
     *
     * @param   string  $date      the date index
     * @param   object  &$blocks   the blocks for the given date
     * @param   array   &$lessons  the array to contain the lessons
     *
     * @return  void
     */
    private function setLessonsByDate($date, &$blocks, &$lessons)
    {
        foreach ($blocks as $block => $blockLessons)
        {
            foreach ($blockLessons as $lessonID => $rooms)
            {
                $this->setLesson($date, $block, $lessonID, $rooms, $lessons);
            }
        }
    }

    /**
     * Sets the data for a lesson instance (and for the lesson itself on initial call)
     *
     * @param   string  $date      the date index
     * @param   string  $block     the block index
     * @param   string  $lessonID  the untis lesson id
     * @param   object  &$rooms    the rooms in which the lesson takes (has taken) place
     * @param   array   &$lessons  the array to contain the lessons
     *
     * @return  void  the data is added to the lessons array
     */
    private function setLesson($date, $block, $lessonID, &$rooms, &$lessons)
    {
        $currentDate = new DateTime($date);
        $dow = strtolower($currentDate->format("l"));

        $lessonIndex = $lessonID . $block . $dow;

        if (!array_key_exists($lessonIndex, $lessons))
        {
            $lessons[$lessonIndex] = clone $this->_schedule->lessons->{$lessonID};
            $lessons[$lessonIndex]->lessonKey = $lessonID;
            $lessons[$lessonIndex]->block = $block;
            $lessons[$lessonIndex]->dow = $dow;
        }

        if (!isset($lessons[$lessonIndex]->calendar))
        {
            $lessons[$lessonIndex]->calendar = array();
        }

        $lessons[$lessonIndex]->calendar[$date][$block]["lessonData"] = $rooms;
    }

    /**
     * Sets variables for requested resource plans.
     *
     * @return  void
     */
    private function setRequestedResources()
    {
        $input = JFactory::getApplication()->input;
        $this->requestResources['teachers'] = $input->get('teacherID', array(), 'array');
        $this->requestResources['rooms'] = $input->get('roomID', array(), 'array');
        $this->requestResources['pools'] = $input->get('poolID', array(), 'array');
        $this->requestResources['subjects'] = $input->get('subjectID', array(), 'array');
    }
}
