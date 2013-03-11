<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        database abstraction for the schedule edit view
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class loading persistent data to be used for schedule edit output
 * 
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSchedule_Edit extends JModelAdmin
{
    /**
     * retrieves the jform object for this view
     * 
     * @param   array    $data      unused
     * @param   boolean  $loadData  if the form data should be pulled dynamically
     *
     * @return    mixed    A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_thm_organizer.schedule_edit',
                                'schedule_edit',
                                array('control' => 'jform', 'load_data' => $loadData)
                               );
        if (empty($form))
        {
            return false;
        }
        else
        {
            return $form;
        }
    }

    /**
     * retrieves the data that should be injected in the form the loading is
     * done in jmodel admin
     *
     * @return  mixed  The data for the form.
     */
    protected function loadFormData()
    {
        $scheduleIDs = JRequest::getVar('cid',  null, '', 'array');
        $scheduleID = (empty($scheduleIDs))? JRequest::getVar('scheduleID') : $scheduleIDs[0];
        $data = $this->getItem($scheduleID);
        unset($data->schedule);
        $data->creationdate = thm_organizerHelper::germanizeDate($data->creationdate);
        $data->startdate = thm_organizerHelper::germanizeDate($data->startdate);
        $data->enddate = thm_organizerHelper::germanizeDate($data->enddate);
        return $data;
    }

    /**
     * Method to get a single record.
	 * 
	 * @param   integer  $scheduleID  The id of the selected schedule
     *
     * @return  mixed  Object on success, false on failure.
     */
    public function getItem($scheduleID)
    {
        return ($scheduleID)? parent::getItem($scheduleID) : $this->getTable();
    }

    /**
     * returns a table object the parameters are completely superfluous in the
     * implementing classes since they are always set by default
     *
     * @param   string  $type    the table type to instantiate
     * @param   string  $prefix  a prefix for the table class name. optional.
     * @param   array   $config  configuration array for model. optional.
     *
     * @return    JTable    A database object
    */
    public function getTable($type = 'schedules', $prefix = 'thm_organizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
}
