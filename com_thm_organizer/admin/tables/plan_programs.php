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
 * Class instantiates a \JTable Object associated with the plan_programs table.
 */
class THM_OrganizerTablePlan_Programs extends THM_OrganizerTableNullable
{
    /**
     * Declares the associated table
     *
     * @param \JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_plan_programs', 'id', $dbo);
    }

    /**
     * Set the table column names which are allowed to be null
     *
     * @return boolean  true
     */
    public function check()
    {
        if (empty($this->programID)) {
            $this->programID = null;
        }

        return true;
    }
}
