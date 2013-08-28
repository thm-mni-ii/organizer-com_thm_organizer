<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelTeacher
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Class THM_OrganizerModelLecturer for component com_thm_organizer
 *
 * Class provides methods to deal with lecturer
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelTeacher extends JModel
{
    /**
     * Attempts to save a teacher entry, updating schedule data as necessary.
     *
     * @return true on success, otherwise false
     */
    public function save()
    {
        $dbo = JFactory::getDbo();
        $data = JRequest::getVar('jform', null, null, null, 4);
        $dbo->transactionStart();
        $scheduleSuccess = $this->updateScheduleData($data, "'" . $data['id'] . "'");
        if ($scheduleSuccess)
        {
            $table = JTable::getInstance('teachers', 'thm_organizerTable');
            $teacherSuccess = $table->save($data);
            if ($teacherSuccess)
            {
                $dbo->transactionCommit();
                return true;
            }
        }
        $dbo->transactionRollback();
        return false;
    }

    /**
     * Attempts an iterative merge of all teacher entries.  Due to the attempted
     * merge of multiple entries with individual success codes no return value
     * is given.
     *
     * @return void
     */
    public function autoMergeAll()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('*')->from('#__thm_organizer_teachers')->order('surname, id');
        $dbo->setQuery((string) $query);
        $teacherEntries = $dbo->loadAssocList();
 
        if (empty($teacherEntries))
        {
            return;
        }

        $deletedIDs = array();
        for ($index = 0; $index < count($teacherEntries); $index++)
        {
            $currentEntry = $teacherEntries[$index];
            if (in_array($currentEntry['id'], $deletedIDs))
            {
                continue;
            }

            $nextIndex = $index + 1;
            $nextEntry = $teacherEntries[$nextIndex];
            while ($nextEntry != false
                AND $currentEntry['surname'] == $nextEntry['surname'])
            {
                $entries = array($currentEntry, $nextEntry);
                $merged = $this->autoMerge($entries);
                if ($merged)
                {
                    $deletedIDs[] = $nextEntry['id'];
                }
                $nextIndex++;
                $nextEntry = $teacherEntries[$nextIndex];
            }
        }
    }

    /**
     * Performs an automated merge of teacher entries, in as far as this is
     * possible according to plausibility constraints.
     *
     * @param   array  $teacherEntries  entries to be compared
     *
     * @return  boolean  true on success, otherwise false
     */
    public function autoMerge($teacherEntries = null)
    {
        if (empty($teacherEntries))
        {
            $dbo = JFactory::getDbo();
            $cids = "'" . implode("', '", JRequest::getVar('cid', array(), 'post', 'array')) . "'";

            $query = $dbo->getQuery(true);
            $query->select('*');
            $query->from('#__thm_organizer_teachers');
            $query->where("id IN ( $cids )");
            $query->order('id ASC');

            $dbo->setQuery((string) $query);
            $teacherEntries = $dbo->loadAssocList();
        }

        $data = array();
        $otherIDs = array();
        foreach ($teacherEntries as $entry)
        {
            foreach ($entry as $property => $value)
            {
                $value = trim($value);

                // Property value is not set for DB Entry
                if (!empty($value))
                {
                    if ($property == 'gpuntisID')
                    {
                        $value = str_replace('TR_', '', $value);
                    }

                    // Initial set of data property
                    if (empty($data[$property]))
                    {
                        $data[$property] = $value;
                    }

                    // Value differentiation
                    elseif ($data[$property] != $value)
                    {
                        if ($property == 'gpuntisID' AND isset($entry['forename']))
                        {
                            if ($data[$property] == $value . substr($entry['forename'], 0, 1))
                            {
                                continue;
                            }
                            elseif ($data[$property] . substr($entry['forename'], 0, 1) == $value)
                            {
                                $data[$property] = $value;
                            }
                        }
                        elseif ($property == 'id')
                        {
                            $otherIDs[] = $value;
                        }
                        else
                        {
                            return false;
                        }
                    }
                }
            }
        }
        $data['otherIDs'] = "'" . implode("', '", $otherIDs) . "'";
        return $this->merge($data);
    }

    /**
     * Merges resource entries and cleans association tables.
     *
     * @param   array  &$data  array used by the automerge function to
     *                         automatically set teacher values
     *
     * @return  boolean  true on success, otherwise false
     */
    public function merge(&$data = null)
    {
        // Clean POST variables
        if (empty($data))
        {
            $data['id'] = JRequest::getInt('id');
            $data['surname'] = JRequest::getString('surname');
            $data['forename'] = JRequest::getString('forename');
            $data['title'] = JRequest::getString('title');
            $data['username'] = JRequest::getString('username');
            $data['gpuntisID'] = JRequest::getString('gpuntisID');
            $data['fieldID'] = JRequest::getInt('fieldID')? JRequest::getInt('fieldID') : null;
            $data['otherIDs'] = "'" . implode("', '", explode(',', JRequest::getString('otherIDs'))) . "'";
        }

        $dbo = JFactory::getDbo();
        $dbo->transactionStart();

        $eventsSuccess = $this->updateAssociation($data['id'], $data['otherIDs'], 'event');
        if (!$eventsSuccess)
        {
            $dbo->transactionRollback();
            return false;
        }
 
        $subjectsSuccess = $this->updateAssociation($data['id'], $data['otherIDs'], 'subject');
        if (!$subjectsSuccess)
        {
            $dbo->transactionRollback();
            return false;
        }

        if (!empty($data['gpuntisID']))
        {
            $allIDs = "'{$data['id']}', " . $data['otherIDs'];
            $schedulesSuccess = $this->updateScheduleData($data, $allIDs);
            if (!$schedulesSuccess)
            {
                $dbo->transactionRollback();
                return false;
            }
        }
 
        // Update entry with lowest ID
        $teacher = JTable::getInstance('teachers', 'thm_organizerTable');
        $success = $teacher->save($data);
        if (!$success)
        {
            $dbo->transactionRollback();
            return false;
        }

        $query = $dbo->getQuery(true);
        $query->delete('#__thm_organizer_teachers');
        $query->where("id IN ( {$data['otherIDs']} )");
        $dbo->setQuery((string) $query);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            $dbo->transactionRollback();
            return false;
        }

        $dbo->transactionCommit();
        return true;
    }

    /**
     * Replaces old teacher associations
     *
     * @param   int     $newID      the id onto which the teacher entries merge
     * @param   string  $oldIDs     a string containing the ids to be replaced
     * @param   string  $tableName  the unique part of the table name
     *
     * @return  boolean  true on success, otherwise false
     */
    private function updateAssociation($newID, $oldIDs, $tableName)
    {
        $dbo = JFactory::getDbo();

        $query = $dbo->getQuery(true);
        $query->update("#__thm_organizer_{$tableName}_teachers");
        $query->set("teacherID = '$newID'");
        $query->where("teacherID IN ( $oldIDs )");
        $dbo->setQuery((string) $query);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            $dbo->transactionRollback();
            return false;
        }
        return true;
    }

    /**
     * Updates teacher data and lesson associations in active schedules
     *
     * @param   array   &$data  teacher data corrresponding to a table row
     * @param   string  $IDs    a list of ids suitable for retrieval of teacher
     *                          gpuntisIDs to be replaced in saved schedules
     *
     * @return boolean
     */
    public function updateScheduleData(&$data, $IDs)
    {
        $dbo = JFactory::getDbo();

        if (empty($data['gpuntisID']))
        {
            return true;
        }
        else
        {
            $data['gpuntisID'] = str_replace('TR_', '', $data['gpuntisID']);
        }

        $scheduleQuery = $dbo->getQuery(true);
        $scheduleQuery->select('id, schedule');
        $scheduleQuery->from('#__thm_organizer_schedules');
        $dbo->setQuery((string) $scheduleQuery);
        $schedules = $dbo->loadAssocList();
        if (empty($schedules))
        {
            return true;
        }

        if (!empty($data['fieldID']))
        {
            $fieldQuery = $dbo->getQuery(true);
            $fieldQuery->select('gpuntisID');
            $fieldQuery->from('__thm_organizer_fields');
            $fieldQuery->where("id = '{$data['fieldID']}'");
            $dbo->setQuery((string) $fieldQuery);
            $field = str_replace('DS_', '', $dbo->loadResult());
        }

        $oldNameQuery = $dbo->getQuery(true);
        $oldNameQuery->select('gpuntisID');
        $oldNameQuery->from('#__thm_organizer_teachers');
        $oldNameQuery->where("id IN ( $IDs )");
        $oldNameQuery->where("gpuntisID IS NOT NULL");
        $dbo->setQuery((string) $oldNameQuery);
        $oldNames = $dbo->loadResultArray();

        $scheduleTable = JTable::getInstance('schedules', 'thm_organizerTable');
        foreach ($schedules as $schedule)
        {
            $scheduleObject = json_decode($schedule['schedule']);

            foreach ($oldNames AS $oldName)
            {
                if (isset($scheduleObject->teachers->{$oldName}))
                {
                    unset($scheduleObject->teachers->{$oldName});
                    foreach ($scheduleObject->lessons as $lessonID => $lesson)
                    {
                        if (isset($lesson->teachers->$oldName))
                        {
                            $delta = $lesson->teachers->$oldName;
                            unset($scheduleObject->lessons->{$lessonID}->teachers->$oldName);
                            $scheduleObject->lessons->{$lessonID}->teachers->{$data['gpuntisID']} = $delta;
                        }
                    }
                }
            }

            if (!isset($scheduleObject->teachers->{$data['gpuntisID']}))
            {
                $scheduleObject->teachers->{$data['gpuntisID']} = new stdClass;
            }

            $scheduleObject->teachers->{$data['gpuntisID']}->gpuntisID = $data['gpuntisID'];
            $scheduleObject->teachers->{$data['gpuntisID']}->surname = $data['surname'];
            if (isset($data['forename']))
            {
                $scheduleObject->teachers->{$data['gpuntisID']}->forename = $data['forename'];
            }
            if (isset($data['username']))
            {
                $scheduleObject->teachers->{$data['gpuntisID']}->username = $data['username'];
            }

            if (!empty($data['fieldID']))
            {
                $scheduleObject->teachers->{$data['gpuntisID']}->fieldID = $data['fieldID'];
                if (!empty($field))
                {
                    $scheduleObject->teachers->{$data['gpuntisID']}->description = $field;
                }
            }
            if (isset($scheduleObject->teachers->{$data['gpuntisID']}->firstname))
            {
                unset($scheduleObject->teachers->{$data['gpuntisID']}->firstname);
            }

            $schedule['schedule'] = json_encode($scheduleObject);
            $success = $scheduleTable->save($schedule);
            if (!$success)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Deletes teacher resource entries.
     *
     * @return boolean
     */
    public function delete()
    {
        $query = $this->_db->getQuery(true);
        $query->delete('#__thm_organizer_teachers');
        $cids = "'" . implode("', '", JRequest::getVar('cid', array(), 'post', 'array')) . "'";
        $query->where("id IN ( $cids )");
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->query();
            return true;
        }
        catch ( Exception $exception)
        {
            return false;
        }
    }
}
