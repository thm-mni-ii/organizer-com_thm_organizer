<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerTableFields
 * @description fields table class
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.table');
/**
 * Class representing the fields table.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerTableFields extends JTable
{
    /**
     * Constructor to call the parent constructor
     *
     * @param   JDatabase  &$dbo  A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_fields', 'id', $dbo);
    }

    /**
     * Method to store a row in the database from the JTable instance properties.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     */
    public function store($updateNulls = true)
    {
        return parent::store(true);
    }

    /**
     * Set the table column names which are allowed to be null
     *
     * @param   object  &$table  the table to be modified
     *
     * @return  boolean  true
     */
    public function check()
    {
        $nullColumns = array('colorID');
        foreach ($nullColumns as $nullColumn)
        {
            if (!strlen($this->$nullColumn))
            {
                $this->$nullColumn = NULL;
            }
        }
        return true;
    }
}
