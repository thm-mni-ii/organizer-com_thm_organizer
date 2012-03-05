<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model schedule manager
 * @description datase abstraction file for the schedules table
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
class thm_organizersModelschedule extends JModel
{
    /**
     * update
     *
     * updates schedules table row information
     */
    public function update()
    {
        $data = $this->cleanRequestData();
        $table = JTable::getInstance('schedules', 'thm_organizerTable');
        $table->load($data['id']);
        $success = $table->save($data);
        if($success)return $data['id'];
        else return false;
    }

    /**
     * cleanRequestData
     *
     * filters the data from the request
     *
     * @return array cleaned request data
     */
    protected function cleanRequestData()
    {
        $data = JRequest::getVar('jform', null, null, null, 4);
        $data['id'] = JRequest::getInt('scheduleID');
        $data['filename'] = addslashes($data['filename']);
        $data['description'] = addslashes($data['description']);
        $data['startdate'] = thm_organizerHelper::dbizeDate($data['startdate']);
        $data['enddate'] = thm_organizerHelper::dbizeDate($data['enddate']);
        return $data;
    }

    /**
     * upload
     *
     * saves a schedule file to the database. must be implemented
     * by inheriting classes.
     */
    public function upload(){}

    /**
     * validate
     *
     * checks a given schedule's structure and consistency. must be implemented
     * by inheriting classes.
     */
    public function validate(){}

    /**
     * checkForActivationConflicts
     *
     * checks whether activation conflicts would occur through the
     * (de)activation of the selected schedules. this would occur if more than
     * one schedule for a given semester was selected for activation.
     *
     * @return bool true if conflicts were found, otherwise false
     */
    public function checkForActivationConflicts()
    {
        $scheduleIDs = JRequest::getVar('cid', array(), 'post', 'array');
        $whereIDs = "( '".implode("', '", $scheduleIDs)."' )";

        $semesterIDs = $this->getIDsByColumn($whereIDs, "sid");
        $plantypeIDs = $this->getIDsByColumn($whereIDs, "plantypeID");

        $dbo = $this->getDbo();
        foreach($semesterIDs as $semesterID)
        {
            foreach($plantypeIDs as $plantypeID)
            {
                $query = $dbo->getQuery(true);
                $query->select("COUNT(id)");
                $query->from("#__thm_organizer_schedules");
                $query->where("sid = '$semesterID'");
                $query->where("plantypeID = '$plantypeID'");
                $query->where("id IN $whereIDs");;
                $dbo->setQuery((string)$query);
                if($dbo->loadResult() > 1) return true;
            }
        }
        return false;
    }

    /**
     * getIDsByColumn
     *
     * retrieves a list of IDs from the schedules table for the given
     * scheduleIDs and column name
     *
     * @param string $scheduleIDs the ids of the schedules selected
     * @param string $column_name the column name where the foreign keys are
     *                            stored
     * @return array of IDs or null on empty result
     */
    protected function getIDsByColumn($scheduleIDs, $column_name)
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT ( $column_name )");
        $query->from("#__thm_organizer_schedules");
        $query->where("id IN $whereIDs");
        $dbo->setQuery((string)$query);
        return $dbo->loadResultArray();
    }

    /**
     * activate
     *
     * (de)activates the given schedule in the context of its semester. must be
     * implemented by inheriting classes.
     */
    public function activate(){}

    /**
     * private function getNewData
     */
    protected function handleNewData(&$schedule){}

    /**
     * getOldData
     *
     * retrieves lesson data for the currently active schedule, encapsulates it
     * in a php array structure, and then removes it.
     */
    protected function handleOldData($semesterID, $plantypeID){}

    /**
     * deactivate
     *
     * sets the current active schedule to inactive. must be implemented by
     * inheriting classes.
     */
    public function deactivate(&$schedule, &$return){}

    /**
     * delete
     *
     * removes the selected schedule
     *
     * @return bool true on success, otherwise false
     */
    public function delete($scheduleID)
    {
        $table = JTable::getInstance('schedules', 'thm_organizerTable');
        $table->load($scheduleID);
        return $table->delete($scheduleID);
    }
}
?>
