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

/**
 * Class which manages stored (lesson) method data.
 */
class Method extends MergeModel
{
    protected $fkColumn = 'methodID';

    protected $tableName = 'methods';

    /**
     * Updates key references to the entry being merged.
     *
     * @return boolean  true on success, otherwise false
     */
    protected function updateAssociations()
    {
        return $this->updateAssociation('lessons');
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
        foreach ($schedule->lessons as $lessonIndex => $lesson) {
            if (isset($lesson->methodID) and in_array($lesson->methodID, $this->data['otherIDs'])) {
                $schedule->lessons->$lessonIndex->methodID = $this->data['id'];
            }
        }
    }
}
