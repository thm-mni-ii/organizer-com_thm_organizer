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

defined('_JEXEC') or die;

use Organizer\Helpers\Plan_Programs;

/**
 * Class which manages stored plan (degree) program / organizational grouping data.
 */
class Plan_Program extends MergeModel
{
    protected $deptResource = 'programID';

    protected $fkColumn = 'programID';

    protected $tableName = 'plan_programs';

    /**
     * Provides resource specific user access checks
     *
     * @return boolean  true if the user may edit the given resource, otherwise false
     */
    protected function allowEdit()
    {
        $allIDs = [];
        if (!empty($this->data['id'])) {
            $allIDs = $allIDs + [$this->data['id']];
        }
        if (!empty($this->data['otherIDs'])) {
            $allIDs = $allIDs + $this->data['otherIDs'];
        }

        return Plan_Programs::allowEdit($allIDs);
    }

    /**
     * Updates key references to the entry being merged.
     *
     * @return boolean  true on success, otherwise false
     */
    protected function updateAssociations()
    {
        $drUpdated = $this->updateDRAssociation('program');
        if (!$drUpdated) {
            return false;
        }

        return $this->updateAssociation('plan_pools');
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
