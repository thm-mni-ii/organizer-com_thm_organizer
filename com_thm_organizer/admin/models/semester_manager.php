<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester manager model
 * @description db abstraction file for the semester manager view
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */

defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );
 
class thm_organizersModelsemester_manager extends JModel
{
    /**
     * @var array a list of semesters
     */
    public $semesters;

    public function __construct()
    {
        parent::__construct();
        $this->loadSemesters();
    }

    /**
     * loadSemesters
     *
     * retrieves the semesters from the database
     */
    private function loadSemesters()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $select = "DISTINCT s.id, s.organization, s.semesterDesc ";
        $query->select($select);
        $query->from("#__thm_organizer_semesters AS s");
        $dbo->setQuery((string)$query);
        $semesters = $dbo->loadAssocList();
        if(isset($semesters) and count($semesters))
        {
            foreach($semesters as $k => $semester)
            {
                $query = $dbo->getQuery(true);
                $select = "DATE_FORMAT(startdate, '%d.%m.%Y') AS startdate, ";
                $select .= "DATE_FORMAT(enddate, '%d.%m.%Y') AS enddate ";
                $query->select($select);
                $query->from("#__thm_organizer_schedules");
                $query->where("active IS NOT NULL");
                $query->where("sid = '{$semester['id']}'");
                $dbo->setQuery((string)$query);
                $dates = $dbo->loadAssoc();

                if(isset($dates))
                {
                    $semesters[$k]['startdate'] = $dates['startdate'];
                    $semesters[$k]['enddate'] = $dates['enddate'];
                }
                else
                {
                    $semesters[$k]['startdate'] = '';
                    $semesters[$k]['enddate'] = '';
                }

                $semesterEditLink = 'index.php?option=com_thm_organizer';
                $semesterEditLink .= '&view=semester_edit&semesterID='.$semester['id'];
                $semesters[$k]['url'] = $semesterEditLink;

                $title = JText::_('COM_THM_ORGANIZER_SEM_MANAGE_SCHEDULES');
                $schedules_link = "index.php?option=com_thm_organizer";
                $schedules_link .= "&view=schedule_manager&semesterID={$semester['id']}";
                $schedules_button = "<div class='button2-left'><div class='blank'>";
                $schedules_button .= "<a title='$title' href='$schedules_link' >";
                $schedules_button .= "$title</a></div></div>";
                $semesters[$k]['schedules_button'] = $schedules_button;
            }
        }
        else $semesters = array();
        $this->semesters = $semesters;
    }

}