<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once 'nullable.php';

/**
 * Class instantiates a JTable Object associated with the rooms table.
 */
class THM_OrganizerTableRooms extends THM_OrganizerTableNullable
{
    /**
     * Declares the associated table
     *
     * @param JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_rooms', 'id', $dbo);
    }

    /**
     * Set the table column names which are allowed to be null
     *
     * @return boolean  true
     */
    public function check()
    {
        $nullColumns = ['typeID', 'buildingID'];
        foreach ($nullColumns as $nullColumn) {
            if (!strlen($this->$nullColumn)) {
                $this->$nullColumn = null;
            }
        }

        return true;
    }
}
