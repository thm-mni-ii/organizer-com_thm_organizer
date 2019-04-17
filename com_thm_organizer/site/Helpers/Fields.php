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
 * Class provides general functions for retrieving field data.
 */
class Fields
{
    /**
     * Returns the id of the color associated with the field
     *
     * @param int $fieldID the field id
     *
     * @return int the id if the color, otherwise 0
     */
    public static function getColorID($fieldID)
    {
        $dbo = \Factory::getDbo();
        $table   = new \Organizer\Tables\Fields($dbo);
        $success = $table->load($fieldID);

        return $success ? $table->colorID : 0;
    }
}
