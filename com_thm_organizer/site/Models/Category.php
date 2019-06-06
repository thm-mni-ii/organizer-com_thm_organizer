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

use Organizer\Helpers\Categories;

/**
 * Class which manages event categories.
 */
class Category extends MergeModel
{
    protected $deptResource = 'categoryID';

    protected $fkColumn = 'categoryID';

    protected $tableName = 'categories';

    /**
     * Provides resource specific user access checks
     *
     * @return boolean  true if the user may edit the given resource, otherwise false
     */
    protected function allowEdit()
    {
        return Categories::allowEdit($this->selected);
    }

    /**
     * Updates key references to the entry being merged.
     *
     * @return boolean  true on success, otherwise false
     */
    protected function updateAssociations()
    {
        $drUpdated = $this->updateDRAssociation();
        if (!$drUpdated) {
            return false;
        }

        return $this->updateAssociation('groups');
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
