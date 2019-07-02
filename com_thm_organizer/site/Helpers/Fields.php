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
 * Provides general functions for room type access checks, data retrieval and display.
 */
class Fields
{
    /**
     * Creates the display for a field item as used in a list view.
     *
     * @param int $fieldID the field id
     *
     * @return string the HTML output of the field attribute display
     */
    public static function getListDisplay($fieldID)
    {
        $table = OrganizerHelper::getTable('Fields');

        $text    = '';
        $colorID = 0;
        if ($table->load($fieldID)) {
            $textColumn = 'field_' . Languages::getShortTag();
            $text       = $table->$textColumn;
            $colorID    = $table->colorID;
        }

        return Colors::getListDisplay($text, $colorID);
    }
}
