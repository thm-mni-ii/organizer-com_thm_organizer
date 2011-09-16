<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester manager model
 * @description db abstraction file for the semester manager view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
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
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("id, semesterDesc, organization");
        $query->from('#__thm_organizer_semesters');
        $dbo->setQuery((string)$query);
        $semesters = $dbo->loadAssocList();
        if(isset($semesters) and count($semesters))
        {
            foreach($semesters as $k => $semester)
            {
                $linkButton = '<div class="button2-left"><div class="blank">';
                $linkButton .= '<a class="modal" title="TITLETEXT" href="LINKTEXT" ';
                $linkButton .= 'rel="{handler: \'iframe\', size: {DIMENSIONS}}">';
                $linkButton .= 'TITLETEXT</a></div></div>';

                $semesterEditLink = 'index.php?option=com_thm_organizer&view=semester_edit';
                $semesterEditLink .= '&layout=modal&tmpl=component&semesterID='.$semester['id'];
                $semesters[$k]['link'] = $semesterEditLink;

                $semesterEditButton = $linkButton;
                $dimensions = 'x: 600, y: 180';
                $semesterEditButton = str_replace('DIMENSIONS', $dimensions, $semesterEditButton);
                $semesterEditButton = str_replace('LINKTEXT', $semesterEditLink, $semesterEditButton);
                $semesterEditButton =
                    str_replace('TITLETEXT', JText::_('COM_THM_ORGANIZER_SM_EDIT'), $semesterEditButton);
                $semesters[$k]['semesterEditButton'] = $semesterEditButton;

                $scheduleManagerLink = 'index.php?option=com_thm_organizer&view=schedule_manager';
                $scheduleManagerLink .= '&layout=modal&tmpl=component&semesterID='.$semester['id'];

                $scheduleManagerButton = $linkButton;
                $dimensions = 'x: 900, y: 350';
                $scheduleManagerButton = str_replace('DIMENSIONS', $dimensions, $scheduleManagerButton);
                $scheduleManagerButton = str_replace('LINKTEXT', $scheduleManagerLink, $scheduleManagerButton);
                $scheduleManagerButton =
                    str_replace('TITLETEXT', JText::_('COM_THM_ORGANIZER_SM_SCHEDULE_MANAGER'), $scheduleManagerButton);
                $semesters[$k]['scheduleManagerButton'] = $scheduleManagerButton;
            }
        }
        else $semesters = array();
        $this->semesters = $semesters;
    }

}