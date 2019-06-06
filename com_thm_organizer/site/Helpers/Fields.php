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

use stdClass;

/**
 * Provides general functions for room type access checks, data retrieval and display.
 */
class Fields implements ResourceCategory
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

    /**
     * Sets indexes for previously defined resource category types. Does not create them.
     *
     * @param object &$scheduleModel the validating schedule model
     * @param string  $untisID       the id of the resource in Untis
     *
     * @return void modifies the scheduleModel, setting the id property of the resource
     */
    public static function setID(&$scheduleModel, $untisID)
    {
        $table  = OrganizerHelper::getTable('Fields');
        $data   = ['untisID' => $untisID];
        $exists = $table->load($data);

        if ($exists) {
            $scheduleModel->schedule->fields->$untisID     = new stdClass;
            $scheduleModel->schedule->fields->$untisID->id = $table->id;
        }
    }
}
