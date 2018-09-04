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
require_once JPATH_ROOT . '/media/com_thm_organizer/models/merge.php';

/**
 * Class which manages stored plan (degree) program / organizational grouping data.
 */
class THM_OrganizerModelPlan_Program extends THM_OrganizerModelMerge
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
        $allIDs = [$this->data['id']];
        if (!empty($this->data['otherIDs'])) {
            $allIDs = $allIDs + $this->data['otherIDs'];
        }

        return THM_OrganizerHelperPlan_Programs::allowEdit($allIDs);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string $name    The table name. Optional.
     * @param   string $prefix  The class prefix. Optional.
     * @param   array  $options Configuration array for model. Optional.
     *
     * @return  \JTable  A \JTable object
     *
     * @throws  \Exception
     */
    public function getTable($name = 'plan_programs', $prefix = 'thm_organizerTable', $options = [])
    {
        return JTable::getInstance($name, $prefix);
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
