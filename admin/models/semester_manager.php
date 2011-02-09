<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester manager model
 * @description db abstraction file for the semester manager view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined('_JEXEC') or die();
jimport( 'joomla.application.component.model' );
 
class thm_organizersModelsemester_manager extends JModel
{
    public $semesters;

    public function __construct()
    {
        parent::__construct();
        $this->loadSemesters();
        if(!empty($this->semesters))
            $this->setSemesterEditLinks();
    }

    private function loadSemesters()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_semesters AS s');
        $query->leftJoin('#__thm_organizer_usergroups AS ug ON s.author = ug.id');
        $dbo->setQuery((string)$query);
        $semesters = $dbo->loadAssocList();
        $this->semesters = $semesters;
    }

    private function addLinks()
    {
        foreach($this->semesters as $sKey => $sValue)
        {
            $this->semesters[$sKey]['link'] = 'index.php?option=com_thm_organizer&view=semester_edit&semester='.$sValue['sid'];
        }
    }
}