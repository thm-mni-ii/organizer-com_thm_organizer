<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelRoom_display
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT . '/helpers/teacher.php';

if (!defined('SCHEDULE'))
{
    define('SCHEDULE', 1);
}
if (!defined('ALTERNATING'))
{
    define('ALTERNATING', 2);
}
if (!defined('CONTENT'))
{
    define('CONTENT', 3);
}
if (!defined('APPOINTMENTS'))
{
    define('APPOINTMENTS', 4);
}

/**
 * Retrieves lesson and event data for a single room and day
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelRoom_Display extends JModelLegacy
{
    public $params = array();

    private $_schedules;

    public $blocks;

    private $_dbDate = "";

    public $appointments = array();

    public $information = array();

    public $notices = array();

    public $upcoming = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $input = JFactory::getApplication()->input;

        $ipData = array('ip' => $input->server->getString('REMOTE_ADDR', ''));
        $monitorEntry = JTable::getInstance('monitors', 'thm_organizerTable');
        $registered = $monitorEntry->load($ipData);

        if (!$registered)
        {
            $this->params['layout'] = 'default';
            return;
        }

        $templateSet = $input->getString('tmpl', '') == 'component';
        if (!$templateSet)
        {
            $this->redirectToComponentTemplate();
        }

        $this->setParams($monitorEntry);

        $this->setRoomInformation();

        $this->setScheduleInformation();
    }

    /**
     * Redirects to the component template
     *
     * @return  void
     */
    private function redirectToComponentTemplate()
    {
        $app = JFactory::getApplication();
        $base = JURI::root() . 'index.php?';
        $query = $app->input->server->get('QUERY_STRING', '', 'raw') . '&tmpl=component';
        $app->redirect($base . $query);
    }

    /**
     * Sets display parameters
     *
     * @param $monitorEntry
     */
    private function setParams(&$monitorEntry)
    {
        if ($monitorEntry->useDefaults)
        {
            $this->params['display'] = JComponentHelper::getParams('com_thm_organizer')->get('display', 1);
            $this->params['schedule_refresh'] = JComponentHelper::getParams('com_thm_organizer')->get('schedule_refresh');
            $this->params['content_refresh'] = JComponentHelper::getParams('com_thm_organizer')->get('content_refresh');
            $this->params['content'] = JComponentHelper::getParams('com_thm_organizer')->get('content');
            return;
        }
        else
        {
            $this->params['display'] = $monitorEntry->display;
            $this->params['schedule_refresh'] = $monitorEntry->schedule_refresh;
            $this->params['content_refresh'] = $monitorEntry->content_refresh;
            $this->params['content'] = $monitorEntry->content;
        }

        if ($this->params['display'] == SCHEDULE)
        {
            $this->params['layout'] = 'schedule';
        }
        if ($this->params['display'] == ALTERNATING)
        {
            $this->setAlternatingLayout();
        }
        if ($this->params['display'] == CONTENT)
        {
            $this->params['layout'] = 'content';
        }
        if ($this->params['display'] == APPOINTMENTS)
        {
            $this->params['layout'] = 'appointments';
        }

        $this->params['roomID'] = $monitorEntry->roomID;
    }


    /**
     * Determines which display behaviour is desired based on which layout was previously used
     *
     * @return  void
     */
    private function setAlternatingLayout()
    {
        $session = JFactory::getSession();
        $displayed = $session->get('displayed', 'schedule');

        if ($displayed == 'schedule')
        {
            $this->params['layout'] = 'content';
            return;
        }
        if ($displayed == 'schedule')
        {
            $this->params['layout'] = 'content';
            return;
        }

        $session->set('displayed', $this->params['layout']);
    }

    /**
     * Retrieves the name and id of the room
     *
     * @return  void
     */
    private function setRoomInformation()
    {
        $roomEntry = JTable::getInstance('rooms', 'thm_organizerTable');
        try
        {
            $roomEntry->load($this->params['roomID']);
            $this->params['roomName'] = $roomEntry->longname;
            $this->params['gpuntisID'] = strpos($roomEntry->gpuntisID, 'RM_') === 0?
                substr($roomEntry->gpuntisID, 3) : $roomEntry->gpuntisID;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            $this->params['roomName'] = '';
            $this->params['gpuntisID'] = '';
        }
    }

    /**
     * Sets information about the daily schedule
     *
     * @return  void
     */
    private function setScheduleInformation()
    {
        // For testing
        //$this->params['date'] = getDate(strtotime('06.11.2014'));
        $this->params['date'] = getdate(time());
        $this->_dbDate = date('Y-m-d', $this->params['date'][0]);
        $this->setSchedules();
        if (count($this->_schedules))
        {
            $this->getBlocks();
        }
    }

    /**
     * Retireves schedules valid for the requested date
     *
     * @return  void
     */
     private function setSchedules()
     {
         $dbo = $this->getDbo();
         $query = $dbo->getQuery(true);
         $query->select("schedule");
         $query->from("#__thm_organizer_schedules");
         $query->where("startdate <= '$this->_dbDate'");
         $query->where("enddate >= '$this->_dbDate'");
         $query->where("active = 1");
         $dbo->setQuery((string) $query);
         
        try 
        {
            $schedules = $dbo->loadColumn();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
        }
        
         if (empty($schedules))
         {
             JFactory::getApplication()->redirect('index.php', JText::_('COM_THM_ORGANIZER_MESSAGE_NO_SCHEDULES_FOR_DATE'), 'error');
         }

         foreach ($schedules as $key => $schedule)
         {
             $schedules[$key] = json_decode($schedule);
         }
         $this->_schedules = $schedules;
     }

    /**
     * Creates an array of blocks and fills them with data
     *
     * @return void
     */
    private function getBlocks()
    {
        $schedulePeriods = $this->_schedules[0]->periods;
        $this->blocks = array();
        foreach ($schedulePeriods as $period)
        {
            if ($period->day == $this->params['date']['wday'])
            {
                $this->blocks[$period->period] = array();
                $this->blocks[$period->period]['period'] = $period->period;
                $this->blocks[$period->period]['starttime'] = substr($period->starttime, 0, 2) . ":" . substr($period->starttime, 2);
                $this->blocks[$period->period]['endtime'] = substr($period->endtime, 0, 2) . ":" . substr($period->endtime, 2);
                $this->blocks[$period->period]['displayTime'] = $this->blocks[$period->period]['starttime'] . " - ";
                $this->blocks[$period->period]['displayTime'] .= $this->blocks[$period->period]['endtime'];
            }
        }
        foreach (array_keys($this->blocks) as $key)
        {
            $this->setLessonData($key);
        }
    }

    /**
     * Adds basic lesson information to a block (if available)
     *
     * @param   int  $blockID  the id of the block being iterated
     *
     * @return void
     */
    private function setLessonData($blockID)
    {
        $lessonFound = false;
        $lessonTitle = $teacherText = '';
        foreach ($this->_schedules as $schedule)
        {
            // No need to reiterate
            if ($lessonFound)
            {
                break;
            }
            foreach ($schedule->calendar->{$this->_dbDate}->$blockID as $lessonID => $rooms)
            {
                // No need to reiterate
                if ($lessonFound)
                {
                    break;
                }

                foreach ($rooms as $roomID => $roomDelta)
                {
                    if ($roomID == 'delta')
                    {
                        if ($roomDelta == 'removed')
                        {
                            break;
                        }
                        else
                        {
                            continue;
                        }
                    }
                    if ($roomID == $this->params['gpuntisID'])
                    {
                        $lessonFound = true;
                        $subjects = (array) $schedule->lessons->$lessonID->subjects;
                        foreach ($subjects as $subjectID => $subjectDelta)
                        {
                            if ($subjectDelta == 'removed')
                            {
                                unset($subjects[$subjectID]);
                            }
                        }
                        if (count($subjects) > 1)
                        {
                            $lessonName = $schedule->lessons->$lessonID->name;
                        }
                        else
                        {
                            $subjects = array_keys($subjects);
                            $subjectID = array_shift($subjects);
                            $longname = $schedule->subjects->$subjectID->longname;
                            $shortname = $schedule->subjects->$subjectID->name;
                            $lessonName = (strlen($longname) <= 30)? $longname : $shortname;
                            $lessonName .= " - " . $schedule->lessons->$lessonID->description;
                            $lessonTitle .= $lessonName;
                        }
                        $teachersIDs = (array) $schedule->lessons->$lessonID->teachers;
                        $teachers = array();
                        foreach ($teachersIDs as $teacherID => $teacherDelta)
                        {
                            if ($teacherDelta == 'removed')
                            {
                                unset($teachers[$teacherID]);
                                continue;
                            }
 
                            $teachers[$schedule->teachers->$teacherID->surname] = $schedule->teachers->$teacherID->surname;
                        }
                        $teacherText .= implode(', ', $teachers);
                    }
                }
            }
        }
        if ($lessonFound)
        {
            $this->blocks[$blockID]['title'] = $lessonTitle;
            $this->blocks[$blockID]['extraInformation'] = $teacherText;
            $this->blocks[$blockID]['type'] = 'COM_THM_ORGANIZER_RD_TYPE_LESSON';
            $this->blocks[$blockID]['teacherText'] = $teacherText;
            $this->lessonsExist = true;
        }
        else
        {
            $this->blocks[$blockID]['title'] = JText::_('COM_THM_ORGANIZER_NO_INFORMATION_AVAILABLE');
            $this->blocks[$blockID]['extraInformation'] = '';
            $this->blocks[$blockID]['type'] = 'empty';
        }
    }
}
