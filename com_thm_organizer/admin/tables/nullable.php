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

/**
 * Abstract class for use by resources with nullable values.
 */
abstract class THM_OrganizerTableNullable extends \Joomla\CMS\Table\Table
{
    /**
     * This functions overwrites \JTables default of $updateNulls = false.
     *
     * @param boolean $updateNulls True to update fields even if they are null.
     *
     * @return boolean  True on success.
     */
    public function store($updateNulls = true)
    {
        return parent::store($updateNulls);
    }
}
