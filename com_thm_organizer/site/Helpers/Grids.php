<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

/**
 * Class provides general functions for retrieving building data.
 */
class Grids
{

    /**
     * Retrieves the grid name.
     *
     * @param int $gridID the grid id
     *
     * @return string the localized name of the grid if found, otherwise an empty string
     */
    public static function getName($gridID)
    {
        $table  = OrganizerHelper::getTable('Grids');
        $exists = $table->load($gridID);
        $column = 'name_' . Languages::getShortTag();

        return empty($exists) ? '' : $table->$column;
    }
}
