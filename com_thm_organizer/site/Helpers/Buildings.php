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

defined('_JEXEC') or die;

/**
 * Class provides general functions for retrieving building data.
 */
class Buildings
{
    /**
     * Checks for the building entry in the database, creating it as necessary. Adds the id to the building entry in the
     * schedule.
     *
     * @param string $name the building name
     *
     * @return mixed  int the id if the room could be resolved/added, otherwise null
     */
    public static function getID($name)
    {
        $dbo = \Factory::getDbo();
        $table   = new \Organizer\Tables\Buildings($dbo);
        $data    = ['name' => $name];
        $success = $table->load($data);

        if ($success) {
            return $table->id;
        }

        // Entry not found
        $success = $table->save($data);

        return $success ? $table->id : null;
    }
}
