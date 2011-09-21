<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model semester edit
 * @description db abstraction file for editing semester entries
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */

defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelsemester_edit extends JModel
{
    public $semesterID;
    public $organization;
    public $semesterDesc;

    function __construct()
    {
        parent::__construct();

        $semesterID = JRequest::getVar('semesterID');
        if($semesterID)
        {
            $dbo = JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $query->select("*");
            $query->from("#__thm_organizer_semesters");
            $query->where("id = '$semesterID'");
            $dbo->setQuery((string)$query);
            $semester = $dbo->loadAssoc();
            if(!empty($semester))
            {
                $this->semesterID = $semesterID;
                $this->organization = $semester['organization'];
                $this->semesterDesc = $semester['semesterDesc'];
            }
            else $this->setNewData();
        }
        else $this->setNewData();
    }

    /**
     * setNewData
     *
     * fills object variables with empty data
     */
    private function setNewData()
    {
        $this->semesterID = 0;
        $this->organization = '';
        $this->semesterDesc = '';
    }
}
?>