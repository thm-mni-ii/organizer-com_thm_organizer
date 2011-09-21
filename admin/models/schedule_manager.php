<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model schedule_manager
 * @description database abstraction file for the schedule manager view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelschedule_manager extends JModel
{
    public $semesterName;
    public $semesterID;
    public $schedules;

    public function __construct()
    {
        parent::__construct();
        $this->semesterID = JRequest::getInt('semesterID');
        $this->semesterName = $this->getSemesterName();
        $this->schedules = $this->getSchedules();
    }
    
    /**
     * getSemester
     * 
     * retrieves the name of a given semester
     * 
     * @return string the name of the semester
     */
    private function getSemesterName()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("CONCAT( organization, ' - ', semesterDesc ) AS semesterName");
        $query->from("#__thm_organizer_semesters");
        $query->where("id = '{$this->semesterID}'");
        $dbo->setQuery((string)$query);
        return $dbo->loadResult();
    }

    /**
     * private function getSchedules
     *
     * creates a list of schedules asscociated with the selected semester
     */
    private function getSchedules()
    {
        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $select = "id, filename, active, description, ";
        $select .= "DATE_FORMAT(startdate, '%d.%m.%Y') AS startdate, ";
        $select .= "DATE_FORMAT(startdate, '%d.%m.%Y') AS enddate, ";
        $select .= "DATE_FORMAT(includedate, '%d.%m.%Y') AS includedate ";
        $query->select($select);
        $query->from("#__thm_organizer_schedules");
        $query->where("sid = '{$this->semesterID}'");
        $dbo->setQuery((string)$query);
        $schedules = $dbo->loadAssocList();
        if(isset($schedules) and count($schedules))
            $this->setHTML($schedules);
        else $schedules = array();
        return $schedules;
    }

    private function setHTML(&$schedules)
    {
        $url = "index.php?option=com_thm_organizer&task=TASKTEXT";
        $url .= "&semesterID={$this->semesterID}&scheduleID=IDTEXT";

        $activateTitle = JText::_('COM_THM_ORGANIZER_SM_ACTIVATE_TITLE');
        $activateTitle .= "::".JText::_('COM_THM_ORGANIZER_SM_ACTIVATE_DESC');
        $deactivateTitle = JText::_('COM_THM_ORGANIZER_SM_DEACTIVATE_TITLE');
        $deactivateTitle .= "::".JText::_('COM_THM_ORGANIZER_SM_DEACTIVATE_DESC');
        $deleteTitle = JText::_('COM_THM_ORGANIZER_SM_SCHEDULE_DELETE_TITLE');
        $deleteTitle .= JText::_('COM_THM_ORGANIZER_SM_SCHEDULE_DELETE_DESC');
        $startdateTitle = JText::_('COM_THM_ORGANIZER_SM_STARTDATE_TITLE');
        $startdateTitle .= JText::_('COM_THM_ORGANIZER_SM_STARTDATE_DESC');
        $enddateTitle = JText::_('COM_THM_ORGANIZER_SM_ENDDATE_TITLE');
        $enddateTitle .= JText::_('COM_THM_ORGANIZER_SM_ENDDATE_DESC');

        $attribs = array();
        $attribs['class'] = "hasTip";
        $attribs['title'] = "";

        //public static function link($url, $text, $attribs = null)
        $activeImage = JHTML::_('image',
                         'administrator/templates/bluestork/images/admin/tick.png',
                         JText::_( 'Active' ),
                         array( 'class' => 'thm_organizer_schm_tick'));

        $inactiveImage = JHTML::_('image',
                         'administrator/templates/bluestork/images/admin/disabled.png',
                         JText::_( 'Active' ),
                         array( 'class' => 'thm_organizer_schm_tick'));

        $deleteImage = JHTML::_('image',
                         'administrator/templates/bluestork/images/admin/trash.png',
                         JText::_( 'Active' ),
                         array( 'class' => 'thm_organizer_schm_tick'));


        foreach($schedules as $k => $schedule)
        {
            if($schedule["active"])
            {
                $attribs['title'] = $deactivateTitle;
                $deactivateURL = str_replace('TASKTEXT', 'schedule.deactivate', $url);
                $deactivateURL = str_replace('IDTEXT', $schedule['id'], $deactivateURL);
                $schedules[$k]['publish'] = JHtml::_('link', $deactivateURL, $activeImage, $attribs);
                $schedules[$k]['delete'] = "";
            }
            else
            {   
                $attribs['title'] = $activateTitle;
                $activateURL = str_replace('TASKTEXT', 'schedule.activate', $url);
                $activateURL = str_replace('IDTEXT', $schedule['id'], $activateURL);
                $schedules[$k]['publish'] = JHtml::_('link', $activateURL, $inactiveImage, $attribs);
                
                $attribs['title'] = $deleteTitle;
                $deleteURL = str_replace('TASKTEXT', 'schedule.delete', $url);
                $deleteURL = str_replace('IDTEXT', $schedule['id'], $deleteURL);
                $schedules[$k]['delete'] = JHtml::_('link', $deleteURL, $deleteImage, $attribs);
            }
            $attribs['size'] = '10';
            $attribs['title'] = $startdateTitle;
            $schedules[$k]['startdate'] = JHtml::_('calendar', $schedule['startdate'], 'startdate', 'startdate', 'Y.m.d');
            $attribs['title'] = $enddateTitle;
            $schedules[$k]['enddate'] = JHtml::_('calendar', $schedule['enddate'], 'enddate', 'enddate', 'Y.m.d');
        }
    }
}
?>
