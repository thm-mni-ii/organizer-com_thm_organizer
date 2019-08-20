<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Organizer\Helpers\Access;

/**
 * Class which manages stored room type data.
 */
class Roomtype extends MergeModel
{
    protected $fkColumn = 'roomtypeID';

    protected $tableName = 'roomtypes';

    /**
     * Provides room type specific user access checks
     *
     * @return boolean  true if the user may edit the given resource, otherwise false
     */
    protected function allowEdit()
    {
        return Access::allowFMAccess();
    }

    /**
     * Updates key references to the entry being merged.
     *
     * @return boolean  true on success, otherwise false
     */
    protected function updateAssociations()
    {
        return $this->updateAssociation('rooms');
    }

    /**
     * Processes the data for an individual schedule
     *
     * @param object &$schedule the schedule being processed
     *
     * @return void
     */
    protected function updateSchedule(&$schedule)
    {
        return;
    }
}
