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
                $link = 'index.php?option=com_thm_organizer&view=semester_edit&semesterID=';
                $semesters[$k]['link'] = $link.$semester['id'];
            }
        }
        else $semesters = array();
        $this->semesters = $semesters;
    }

}