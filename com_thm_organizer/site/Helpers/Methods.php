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
class Methods implements ResourceCategory
{
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
        $table  = OrganizerHelper::getTable('Methods');
        $data   = ['gpuntisID' => $untisID];
        $exists = $table->load($data);

        if ($exists) {
            $scheduleModel->schedule->methods->$untisID            = new stdClass;
            $scheduleModel->schedule->methods->$untisID->id        = $table->id;
        }
    }
}
