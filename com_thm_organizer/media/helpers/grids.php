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
 * Class provides general functions for retrieving building data.
 */
class THM_OrganizerHelperGrids
{
    /**
     * Retrieves the table id if existent.
     *
     * @param string $untisID the grid name in untis
     *
     * @return mixed int id on success, otherwise null
     */
    public static function getID($untisID)
    {
        $table  = JTable::getInstance('grids', 'thm_organizerTable');
        $data   = ['gpuntisID' => $untisID];
        $exists = $table->load($data);

        return empty ($exists) ? null : $table->id;
    }
}
