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
     * @return  mixed    A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_thm_organizer.schedule_edit', 'schedule_edit', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form))
        {
            return false;
        }

		return $form;
    }

	/**
	 * Method to load the form data
	 *
	 * @return  Object
	 */
    protected function loadFormData()
    {
        $scheduleIDs = JRequest::getVar('cid',  null, '', 'array');
        $scheduleID = (empty($scheduleIDs))? JRequest::getVar('scheduleID') : $scheduleIDs[0];
        $data = $this->getItem($scheduleID);
        if (!empty($data))
		{
			unset($data->schedule);
			$data->creationdate = date("d.m.Y", strtotime($data->creationdate));
			$data->startdate = date("d.m.Y", strtotime($data->startdate));
			$data->enddate = date("d.m.Y", strtotime($data->enddate));
		}
        return $data;
    }

    /**
	 * Method to get the table
	 *
	 * @param   String  $type    Type  			(default: 'schedules')
	 * @param   String  $prefix  Prefix  		(default: 'THM_OrganizerTable')
	 * @param   Array   $config  Configuration  (default: 'Array')
	 *
	 * @return  JTable object
	 */
    public function getTable($type = 'schedules', $prefix = 'thm_organizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
}
