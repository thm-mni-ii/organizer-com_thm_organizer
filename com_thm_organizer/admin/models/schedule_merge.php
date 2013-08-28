<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSchedule_Merge
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Loads room entry information to be merged
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSchedule_Merge extends JModel
{
    /**
     * Array holding schedule entry information
     * 
     * @var array
     */
    public $schedules = null;

    /**
     * Pulls a list of schedule data from the database
     *
     * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $select = "id, departmentname, semestername ";
        $cids = "'" . implode("', '", JRequest::getVar('cid', array(), 'post', 'array')) . "'";

        $query->select($select);
        $query->from('#__thm_organizer_schedules');
        $query->where("id IN ( $cids )");
        $query->order('id ASC');

        $dbo->setQuery((string) $query);
        $this->schedules = $dbo->loadAssocList();
    }
}
