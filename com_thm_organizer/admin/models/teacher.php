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
class THM_OrganizerModelTeacher extends JModelLegacy
{
    private $_scheduleModel = null;
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
        
        try 
        {
            $teacherEntries = $dbo->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
 
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
            $teacherEntries = $this->getTeacherEntries();
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
                    $plausible = $this->checkForPlausibility($data, $entry, $property, $value, $otherIDs);
                    if (!$plausible)
                    {
                        return false;
                    }
                }
            }
        }
        $data['otherIDs'] = "'" . implode("', '", $otherIDs) . "'";
        return $this->merge($data);
    }

    /**
     * Retrieves teacher entries from the database
     * 
     * @return  mixed  array on success, otherwise null
     */
    private function getTeacherEntries()
    {
        $dbo = JFactory::getDbo();
        $cids = "'" . implode("', '", JRequest::getVar('cid', array(), 'post', 'array')) . "'";

        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_teachers');
        $query->where("id IN ( $cids )");
        $query->order('id ASC');

        $dbo->setQuery((string) $query);
        
        try 
        {
            $teachers = $dbo->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        return $teachers;
    }

    /**
     * Compares teacher entries and sets merge values
     * 
     * @param   array   &$data      the data for the merge
     * @param   array   &$entry     the current entry being in the iteration
     * @param   string  $property   the name of the property
     * @param   string  $value      the property value
     * @param   array   &$otherIDs  other ids to be merged
     * 
     * @return  boolean  true if plausible, otherwise false
     */
    private function checkForPlausibility(&$data, &$entry, $property, $value, &$otherIDs)
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
                    return true;
                }
                elseif ($data[$property] . substr($entry['forename'], 0, 1) == $value)
                {
                    $data[$property] = $value;
                    return true;
                }
            }
            elseif ($property == 'id')
            {
                $otherIDs[] = $value;
                return true;
            }
            return false;
        }
        return true;
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
            $data['fieldID'] = JRequest::getInt('fieldID')? JRequest::getInt('fieldID') :  null;
            $data['otherIDs'] = "'" . implode("', '", explode(',', JRequest::getString('otherIDs'))) . "'";
        }
        if (!empty($data['fieldID']) AND empty($data['description']))
        {
            $data['field'] = $this->getField($data);
        }

        $dbo = JFactory::getDbo();
        $dbo->transactionStart();

        $dependenciesUpdated = $this->updateDependencies($data);
        if (!$dependenciesUpdated)
        {
            $dbo->transactionRollback();
            return false;
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
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');
            $dbo->transactionRollback();
            return false;
        }

        $dbo->transactionCommit();
        return true;
    }

    /**
     * Updates teacher dependencies
     * 
     * @param   array  $data  the teacher data
     * 
     * @return  boolean  true on success, otherwise false
     */
    private function updateDependencies($data)
    {
        $eventsSuccess = $this->updateAssociation($data['id'], $data['otherIDs'], 'event');
        if (!$eventsSuccess)
        {
            return false;
        }
 
        $subjectsSuccess = $this->updateAssociation($data['id'], $data['otherIDs'], 'subject');
        if (!$subjectsSuccess)
        {
            return false;
        }

        if (!empty($data['gpuntisID']))
        {
            $allIDs = "'{$data['id']}', " . $data['otherIDs'];
            $schedulesSuccess = $this->updateScheduleData($data, $allIDs);
            if (!$schedulesSuccess)
            {
                return false;
            }
        }
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
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');
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
        $newID = $data['gpuntisID'];

        $scheduleQuery = $dbo->getQuery(true);
        $scheduleQuery->select('id, schedule');
        $scheduleQuery->from('#__thm_organizer_schedules');
        $dbo->setQuery((string) $scheduleQuery);
        
        try 
        {
            $schedules = $dbo->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        if (empty($schedules))
        {
            return true;
        }

        $field = $this->getField($data);
        $untisIDs = $this->getUntisIDs($IDs);

        $scheduleTable = JTable::getInstance('schedules', 'thm_organizerTable');
        foreach ($schedules as $schedule)
        {
            $scheduleObject = json_decode($schedule['schedule']);

            foreach ($untisIDs AS $oldUntisID)
            {
                $this->replaceTeachers($scheduleObject, $oldUntisID, $newID, $field);
            }

            $this->setScheduleTeacher($scheduleObject, $data, $newID, $field);

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
     * Checks for the teacher's field attribute in the database
     * 
     * @param   array  $data  the teacher data
     * 
     * @return  mixed  string untis field id if existent, otherwise null
     */
    private function getField($data)
    {
        if (!empty($data['fieldID']))
        {
            $dbo = JFactory::getDbo();
            $fieldQuery = $dbo->getQuery(true);
            $fieldQuery->select('gpuntisID');
            $fieldQuery->from('#__thm_organizer_fields');
            $fieldQuery->where("id = '{$data['fieldID']}'");
            $dbo->setQuery((string) $fieldQuery);
            
            try
            {
                $field = $dbo->loadResult();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }
        }
        return empty($field)? null : str_replace('DS_', '', $field);
    }

    /**
     * Retrieves the existing/deprecated teacher untis ids from the database
     * 
     * @param   string  $IDs  the teacher entry ids
     * 
     * @return  array  array containing the untis ids
     */
    private function getUntisIDs($IDs)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('gpuntisID');
        $query->from('#__thm_organizer_teachers');
        $query->where("id IN ( $IDs )");
        $query->where("gpuntisID IS NOT NULL");
        $dbo->setQuery((string) $query);
        
        try
        {
            $teacherUntisIDs = $dbo->loadResultArray();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        return $teacherUntisIDs;
    }

    /**
     * Removes deprecated teacher objects and replaces them in the lessons
     * 
     * @param   object  &$schedule  the schedule object
     * @param   string  $oldID      the id to be replaced
     * @param   string  $newID      the new id
     * 
     * @return  void
     */
    private function replaceTeachers(&$schedule, $oldID, $newID)
    {
        if (isset($schedule->teachers->$oldID))
        {
            unset($schedule->teachers->$oldID);
            foreach ($schedule->lessons as $lessonID => $lesson)
            {
                if (isset($lesson->teachers->$oldID))
                {
                    $delta = $lesson->teachers->$oldID;
                    unset($schedule->lessons->$lessonID->teachers->$oldID);
                    $schedule->lessons->$lessonID->teachers->$newID = $delta;
                }
            }
        }
    }

    /**
     * Sets the teacher entry in the schedule object
     * 
     * @param   object  &$schedule  the schedule object
     * @param   array   $teacher    the teacher data
     * @param   string  $newID      the new teacher id
     * @param   string  $field      the field id
     * 
     * @return  void
     */
    private function setScheduleTeacher(&$schedule, $teacher, $newID, $field)
    {
        if (!isset($schedule->teachers->$newID))
        {
            $schedule->teachers->$newID = new stdClass;
        }

        $schedule->teachers->$newID->gpuntisID = $teacher['gpuntisID'];
        $schedule->teachers->$newID->surname = $teacher['surname'];
        if (isset($teacher['forename']))
        {
            $schedule->teachers->$newID->forename = $teacher['forename'];
        }
        if (isset($teacher['username']))
        {
            $schedule->teachers->$newID->username = $teacher['username'];
        }

        if (!empty($teacher['fieldID']))
        {
            $schedule->teachers->$newID->fieldID = $teacher['fieldID'];
            if (!empty($field))
            {
                $schedule->teachers->$newID->description = $field;
            }
        }
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
        }
        catch ( Exception $exception)
        {
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');
            return false;
        }
        return true;
    }

    /**
     * Checks whether teacher nodes have the expected structure and required
     * information
     *
     * @param   object  &$scheduleModel  the validating schedule model
     * @param   object  &$teacherNode    the teacher node to be validated
     *
     * @return void
     */
    public function validate(&$scheduleModel, &$teacherNode)
    {
        $this->_scheduleModel = $scheduleModel;

        $warningString = '';
        $gpuntisID = $this->validateUntisID($teacherNode, $warningString);
        if (!$gpuntisID)
        {
            return;
        }

        $teacherID = str_replace('TR_', '', $gpuntisID);
        $this->_scheduleModel->schedule->teachers->$teacherID = new stdClass;
        $this->_scheduleModel->schedule->teachers->$teacherID->gpuntisID = $teacherID;
        $this->_scheduleModel->schedule->teachers->$teacherID->localUntisID
            = str_replace('TR_', '', trim((string) $teacherNode[0]['id']));

        $surname = $this->validateSurname($teacherNode, $teacherID);
        if (!$surname)
        {
            return;
        } 

        $this->validateForename($teacherNode, $teacherID, $warningString);
        $userid = trim((string) $teacherNode->payrollnumber);
        $this->_scheduleModel->schedule->teachers->$teacherID->username = empty($userid)? '' :$userid;
        $this->validateDescription($teacherNode, $teacherID, $warningString);

        if (!empty($warningString))
        {
            $warning = JText::sprintf("COM_THM_ORGANIZER_TR_FIELD_MISSING", $surname, $teacherID, $warningString);
            $this->_scheduleModel->scheduleWarnings[] = $warning;
        }
    }

    /**
     * Validates the teacher's untis id
     * 
     * @param   object  &$teacherNode    the teacher node object
     * @param   string  &$warningString  a string with missing fields
     * 
     * @return  mixed  string untis id if valid, otherwise false
     */
    private function validateUntisID(&$teacherNode, &$warningString)
    {
        $externalID = trim((string) $teacherNode->external_name);
        $internalID = trim((string) $teacherNode[0]['id']);
        if (empty($internalID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_TR_ID_MISSING"), $this->_scheduleModel->scheduleErrors))
            {
                $this->_scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_TR_ID_MISSING");
            }
            return false;
        }
        if (empty($externalID))
        {
            $warningString .= empty($warningString)? '' : ', ';
            $warningString .= JText::_('COM_THM_ORGANIZER_EXTERNALID');
        }
        $gpuntisID = empty($externalID)? $internalID : $externalID;
        return $gpuntisID;
    }

    /**
     * Validates the teacher's surname
     * 
     * @param   object  &$teacherNode  the teacher node object
     * @param   string  $teacherID     the teacher's id
     * 
     * @return  mixed  string surname if valid, otherwise false
     */
    private function validateSurname(&$teacherNode, $teacherID)
    {
        $surname = trim((string) $teacherNode->surname);
        if (empty($surname))
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_TR_SN_MISSING', $teacherID);
            return false;
        }
        $this->_scheduleModel->schedule->teachers->$teacherID->surname = $surname;
        return $surname;
    }

    /**
     * Validates the teacher's forename attribute
     * 
     * @param   object  &$teacherNode    the teacher node object
     * @param   string  $teacherID       the teacher's id
     * @param   string  &$warningString  a string with missing fields
     * 
     * @return  void
     */
    private function validateForename(&$teacherNode, $teacherID, &$warningString)
    {
        $forename = trim((string) $teacherNode->forename);
        if (empty($forename))
        {
            $warningString .= empty($warningString)? '' : ', ';
            $warningString .= JText::_('COM_THM_ORGANIZER_FORENAME');
        }
        $this->_scheduleModel->schedule->teachers->$teacherID->forename = empty($forename)? '' : $forename;
    }

    /**
     * Validates the teacher's description attribute
     * 
     * @param   object  &$teacherNode    the teacher node object
     * @param   string  $teacherID       the teacher's id
     * @param   string  &$warningString  a string with missing fields
     * 
     * @return  void
     */
    private function validateDescription(&$teacherNode, $teacherID, &$warningString)
    {
        $descriptionID = str_replace('DS_', '', trim($teacherNode->teacher_description[0]['id']));
        if (empty($descriptionID)
         OR empty($this->_scheduleModel->schedule->fields->$descriptionID))
        {
            $warningString .= empty($warningString)? '' : ', ';
            $warningString .= JText::_('COM_THM_ORGANIZER_DESCRIPTION_PROPERTY');
        }
        $this->_scheduleModel->schedule->teachers->$teacherID->description
            = empty($descriptionID)? '' : $descriptionID;
    }
}
