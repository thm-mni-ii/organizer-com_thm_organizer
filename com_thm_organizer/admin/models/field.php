<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once 'merge.php';

/**
 * Class which manages stored field (of expertise) data.
 */
class THM_OrganizerModelField extends THM_OrganizerModelMerge
{
    protected $fkColumn = 'fieldID';

    protected $tableName = 'fields';

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string $name    The table name. Optional.
     * @param   string $prefix  The class prefix. Optional.
     * @param   array  $options Configuration array for model. Optional.
     *
     * @return  \JTable  A \JTable object
     */
    public function getTable($name = 'fields', $prefix = 'thm_organizerTable', $options = [])
    {
        return JTable::getInstance($name, $prefix, $options);
    }

    /**
     * Updates key references to the entry being merged.
     *
     * @return boolean  true on success, otherwise false
     */
    protected function updateAssociations()
    {
        $ppUpdated = $this->updateAssociation('plan_pools');
        if (!$ppUpdated) {
            return false;
        }

        $psUpdated = $this->updateAssociation('plan_subjects');
        if (!$psUpdated) {
            return false;
        }

        $poolsUpdated = $this->updateAssociation('pools');
        if (!$poolsUpdated) {
            return false;
        }

        $programsUpdated = $this->updateAssociation('programs');
        if (!$programsUpdated) {
            return false;
        }

        $subjectsUpdated = $this->updateAssociation('subjects');
        if (!$subjectsUpdated) {
            return false;
        }

        return $this->updateAssociation('teachers');
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
