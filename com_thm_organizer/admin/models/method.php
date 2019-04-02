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
 * Class which manages stored (lesson) method data.
 */
class THM_OrganizerModelMethod extends THM_OrganizerModelMerge
{
    protected $fkColumn = 'methodID';

    protected $tableName = 'methods';

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param string $name    The table name. Optional.
     * @param string $prefix  The class prefix. Optional.
     * @param array  $options Configuration array for model. Optional.
     *
     * @return  \JTable  A \JTable object
     */
    public function getTable($name = 'methods', $prefix = 'thm_organizerTable', $options = [])
    {
        return \JTable::getInstance($name, $prefix, $options);
    }

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
