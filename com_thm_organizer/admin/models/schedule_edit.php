<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model semester edit
 * @description db abstraction file for editing schedule entries
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.modeladmin');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
class thm_organizersModelschedule_edit extends JModelAdmin
{
    /**
     * getForm
     *
     * retrieves the jform object for this view
     *
     * @return	mixed	A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_thm_organizer.schedule_edit', 'schedule_edit', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;
        else return $form;
    }
    
    /**
     * Method to get a single record.
     *
     * @param	integer	The id of the primary key.
     *
     * @return	mixed	Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $scheduleIDs = JRequest::getVar('cid',  null, '', 'array');
        $scheduleID = (empty($scheduleIDs))? JRequest::getVar('scheduleID') : $scheduleIDs[0];
        $schedule = ($scheduleID)? parent::getItem($scheduleID) : $this->getTable();
        return $schedule;
    }

    /**
     * getTable
     *
     * returns a table object the parameters are completely superfluous in the
     * implementing classes since they are always set by default
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     *
     * @return	JTable	A database object
    */
    public function getTable($type = 'schedules', $prefix = 'thm_organizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * loadFormData
     *
     * retrieves the data that should be injected in the form the loading is
     * done in jmodel admin
     *
     * @return	mixed	The data for the form.
     */
    protected function loadFormData()
    {
        if (empty($data)) $data = $this->getItem();
        $data->filename = str_replace(".xml", "", $data->filename);
        unset($data->file);
        $data->creationdate = thm_organizerHelper::germanizeDate($data->creationdate);
        $data->startdate = thm_organizerHelper::germanizeDate($data->startdate);
        $data->enddate = thm_organizerHelper::germanizeDate($data->enddate);
        $data->plantypeID = $this->getPlanType($data->plantypeID);
        return $data;
    }

    /**
     * getPlanType
     *
     * retrieves the name of the plan type currently being edited
     *
     * @param int $id the plantypeID from the schedules table row
     * @return string a translated name of the plan type
     */
    private function getPlanType($id)
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select('plantype');
        $query->from('#__thm_organizer_plantypes');
        $query->where("id = '$id'");
        $dbo->setQuery((string)$query);
        return JText::_($dbo->loadResult());
    }
}
?>