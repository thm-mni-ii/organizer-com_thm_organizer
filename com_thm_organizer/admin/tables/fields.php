<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.database.table');

/**
 * Class instantiates a JTable Object associated with the fields table.
 */
class THM_OrganizerTableFields extends JTable
{
    /**
     * Declares the associated table
     *
     * @param JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_fields', 'id', $dbo);
    }

    /**
     * Method to store a row in the database from the JTable instance properties.
     *
     * @param boolean $updateNulls True to update fields even if they are null.
     *
     * @return boolean  True on success.
     */
    public function store($updateNulls = true)
    {
        return parent::store(true);
    }

    /**
     * Set the table column names which are allowed to be null
     *
     * @return boolean  true
     */
    public function check()
    {
        $nullColumns = ['colorID'];
        foreach ($nullColumns as $nullColumn) {
            if (!strlen($this->$nullColumn)) {
                $this->$nullColumn = null;
            }
        }

        return true;
    }
}
