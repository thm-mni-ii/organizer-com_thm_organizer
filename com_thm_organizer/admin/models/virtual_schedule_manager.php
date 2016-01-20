<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.model
 * @name        THM_OrganizerModelVirtual_Schedule_Manager
 * @description Class to handle virtual schedules
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class THM_OrganizerModelVirtual_Schedule_Manager for component com_thm_organizer
 * Class provides methods display a list of virtual schedules and perform actions on them
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.model
 * @link        www.mni.thm.de
 */
class THM_OrganizerModelVirtual_Schedule_Manager extends JModelList
{

    /**
     * Constructor that calls the parent constructor and intialise variables
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Method to build the sql query to get the virtual schedules
     *
     * @return    String    The sql query
     */
    public function _buildQuery()
    {
        $query = $this->_db->getQuery(true);
        $query->select('*')->from('#__thm_organizer_virtual_schedules');

        return $query;
    }


}
