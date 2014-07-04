<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        thm_organizerViewScheduler
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

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

    public $schedulerFromMenu = false;

    public $joomlaItemid = 0;

    public $canWriteEvents = false;

    public $jsid = '';

    public $searchModuleID = '';

    public $displayModuleNumber = true;

    public $deltaDisplayDays = 14;

    public $semesterID = 0;

    public $semAuthor = "";

    public $scheduleName = "";

    public $semesterName = "";

    public $languageTag = 'de';

    public $FPDFInstalled = false;

    public $iCalcreatorInstalled = false;

    public $PHPExcelInstalled = false;

    public $startup = null;

    public $requestTeacherIDs = array();

    public $requestRoomIDs = array();

    public $requestPoolIDs = array();

    public $requestSubjectIDs = array();

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

        $libraryInstalled = jimport('extjs4.extjs4');
        if (!$libraryInstalled)
        {
            $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_EXTJS4_LIBRARY_NOT_INSTALLED'), 'error');
            return false;
        }

        $validRequest = $this->validateRequest();
        if (!$validRequest)
        {
            $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_SCHEDULER_NO_ACTIVE_SCHEDULE'), 'error');
            return false;
        }

        $scheduleRow = $this->getScheduleRow();
        $validRow = $this->validateSchedule($scheduleRow);
        if (!$validRow)
        {
            return false;
        }

        $this->joomlaItemid = $this->schedulerFromMenu? $app->getMenu()->getActive()->id : 0;

        $schedulerModel = $this->getModel();
        $eventModel = JModelLegacy::getInstance('event_manager', 'thm_organizerModel', array('ignore_request' => false, 'display_type' => 4));

        $params = $app->getMenu()->getActive()->params;
        $this->canWriteEvents = $eventModel->canWrite;
        $this->jsid = $schedulerModel->getSessionID();
        $this->searchModuleID = $input->getString('moduleID', '');
        $this->displayModuleNumber = (bool) $params->get("displayModuleNumber", true);
        $this->deltaDisplayDays = (int) $params->get("deltaDisplayDays", 14);
        $this->semesterID = $scheduleRow->id;
        $this->semAuthor = "";
        $this->scheduleName = $scheduleRow->departmentname . ";" . $scheduleRow->semestername . ";";
        $this->scheduleName .= $scheduleRow->startdate . ";" . $scheduleRow->enddate;

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
            $this->languageTag = "en";
        }
        else
        {
            $languageHandler->load("com_thm_organizer", JPATH_SITE, 'de-DE', true);
            $this->languageTag = "de";
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
            $this->schedulerFromMenu = true;
            return true;
        }
        elseif ($requestedSchedule > 0)
        {
            $this->schedulerFromMenu = false;
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
        $this->FPDFInstalled = jimport('fpdf.fpdf');
        $this->iCalcreatorInstalled = jimport('iCalcreator.iCalcreator');
        $this->PHPExcelInstalled = jimport('PHPExcel.PHPExcel');
    }

    /**
     * Adds resource files to the document
     *
     * @return  void
     */
    private function prepareDocument()
    {
        $doc = JFactory::getDocument();
        $doc->addStyleSheet($this->baseurl . '/libraries/extjs4/css/ext-all-gray.css');
        $doc->addStyleSheet(JURI::root(true) . "/components/com_thm_organizer/views/scheduler/tmpl/mySched/style.css");
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
        if ($this->schedulerFromMenu) // Called via menu item
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
            $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_SCHEDULER_NO_ACTIVE_SCHEDULE'), 'error');
            return false;
        }

        $schedule = json_decode($scheduleRow->schedule);
        unset($scheduleRow->schedule);

        if (empty($schedule))
        {
            $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_SCHEDULER_DATA_FLAWED'), 'error');
            return false;
        }

        $validResources = $this->validateResources($schedule);
        if (!$validResources)
        {
            $app->enqueueMessage(JText::_('COM_THM_ORGANIZER_SCHEDULER_IMPORTANT_DATA_MISSING'), 'error');
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
        if (empty($schedule->periods))
        {
            return false;
        }
        if (empty($schedule->fields))
        {
            return false;
        }
        if (empty($schedule->roomtypes))
        {
            return false;
        }
        if (empty($schedule->lessontypes))
        {
            return false;
        }
        if (empty($schedule->degrees))
        {
            return false;
        }
        if (empty($schedule->rooms))
        {
            return false;
        }
        if (empty($schedule->subjects))
        {
            return false;
        }
        if (empty($schedule->teachers))
        {
            return false;
        }
        if (empty($schedule->pools))
        {
            return false;
        }
        if (empty($schedule->calendar))
        {
            return false;
        }
        if (empty($schedule->lessons))
        {
            return false;
        }
        return true;
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
        $scheduleObject["Events.load"] = $ajaxModel->executeTask("Events.load");
        $scheduleObject["UserSchedule.load"] = new stdClass;
        $scheduleObject["UserSchedule.load"]->respChanges = $ajaxModel->executeTask("UserSchedule.load", array("username" => "respChanges"));
        $scheduleObject["ScheduleDescription.load"] = new stdClass;
        $scheduleObject["ScheduleDescription.load"]->data = $scheduleRow;

        $params = JFactory::getApplication()->getMenu()->getActive()->params;
        $this->loadLessonsOnStartUp = $this->schedulerFromMenu? (bool) $params->get('loadLessonsOnStartUp', true) : true;

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
        $this->requestTeacherIDs = $input->get('teacherID', array(), 'array');
        $this->requestRoomIDs = $input->get('roomID', array(), 'array');
        $this->requestPoolIDs = $input->get('poolID', array(), 'array');
        $this->requestSubjectIDs = $input->get('subjectID', array(), 'array');
    }
}
