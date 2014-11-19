<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSubject
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
defined('RESPONSIBLE') OR define('RESPONSIBLE', 1);
defined('TEACHER') OR define('TEACHER', 2);
/**
 * Provides persistence handling for subjects
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSubject extends JModelLegacy
{
    private $_scheduleModel = null;

    /**
     * Attempts to delete the selected subject entries and related mappings
     *
     * @return  boolean true on success, otherwise false
     */
    public function delete()
    {
        $subjectIDs = JFactory::getApplication()->input->get('cid', array(), 'array');
        if (!empty($subjectIDs))
        {
            $this->_db->transactionStart();
            foreach ($subjectIDs as $subjectID)
            {
                $deleted = $this->deleteEntry($subjectID);
                if (!$deleted)
                {
                    $this->_db->transactionRollback();
                    return false;
                }
            }
            $this->_db->transactionCommit();
        }
        return true;
    }

    /**
     * Deletes an individual subject entry in the mappings and subjects tables
     * 
     * @param   int  $subjectID  the id of the subject to be deleted
     * 
     * @return  boolean  true if successful, otherwise false
     */
    public function deleteEntry($subjectID)
    {
        $table = JTable::getInstance('subjects', 'thm_organizerTable');
        $mappingModel = JModelLegacy::getInstance('mapping', 'THM_OrganizerModel');
        $mappingsDeleted = $mappingModel->deleteByResourceID($subjectID, 'subject');
        if (!$mappingsDeleted)
        {
            return false;
        }

        $subjectDeleted = $table->delete($subjectID);
        if (!$subjectDeleted)
        {
            return false;
        }
        return true;
    }

    /**
     * Attempts to save a subject entry, updating subject-teacher data as
     * necessary.
     *
     * @return true on success, otherwise false
     */
    public function save()
    {
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');

        $this->_db->transactionStart();

        $table = JTable::getInstance('subjects', 'thm_organizerTable');
        if (empty($data['fieldID']))
        {
            $data['fieldID'] = NULL;
        }
        $starProperties = array('expertise', 'self_competence', 'method_competence', 'social_competence');
        foreach ($starProperties as $property)
        {
            $this->cleanStarProperty($data, $property);
        }
        $success = $table->save($data);
 
        // New subjects have no mappings
        if ($success AND empty($data['id']))
        {
            if ($success)
            {
                $this->_db->transactionCommit();
                return $table->id;
            }
            $this->_db->transactionRollback();
            return false;
        }
        else
        {
            try
            {
                $this->processFormTeachers($data);
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                $this->_db->transactionRollback();
                return false;
            }

            $success = $this->processFormMappings($table->id, $data);
            if (!$success)
            {
                $this->_db->transactionRollback();
                return false;
            }
        }
        $this->_db->transactionCommit();
        return $table->id;
    }

    /**
     * Checks if the property should be displayed. Setting it to NULL if not.
     *
     * @param   array   &$data     the form data
     * @param   string  $property  the property name
     *
     * @return  void  can change the &$data value at the property name index
     */
    private function cleanStarProperty(&$data, $property)
    {
        if ($data[$property] == '-1')
        {
            $data[$property] = 'NULL';
        }
    }

    /**
     * Processes the teachers selected for the subject
     * 
     * @param   array  &$data  the post data
     * 
     * @return  bool  true on success, otherwise false
     */
    private function processFormTeachers(&$data)
    {
        $subjectID = $data['id'];
        $this->removeTeachers($subjectID);

        if (!empty($data['responsibleID']))
        {
            foreach ($data['responsibleID'] AS $responsibleID)
            {
                $respAdded = $this->addTeacher($subjectID, $responsibleID, RESPONSIBLE);
                if (!$respAdded)
                {
                    return false;
                }
            }
        }
        if (!empty($data['teacherID']))
        {
            foreach ($data['teacherID'] AS $teacherID)
            {
                $teacherAdded = $this->addTeacher($subjectID, $teacherID, TEACHER);
                if (!$teacherAdded)
                {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Removes teacher associations for the given subject and level of
     * responsibility.
     * 
     * @param   int  $subjectID       the subject id
     * @param   int  $responsibility  the teacher responsibility level (1|2)
     * 
     * @return boolean
     */
    public function removeTeachers($subjectID, $responsibility = null)
    {
        $query = $this->_db->getQuery(true);
        $query->delete('#__thm_organizer_subject_teachers')->where("subjectID = '$subjectID'");
        if (!empty($responsibility))
        {
            $query->where("teacherResp = '$responsibility'");
        }
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
        return true;
    }

    /**
     * Adds a teacher association
     *
     * @param   int    $subjectID       the id of the subject
     * @param   array  $teacherID       the id of the teacher
     * @param   int    $responsibility  the teacher's responsibility for the
     *                                  subject
     *
     * @return  bool  true on success, otherwise false
     */
    public function addTeacher($subjectID, $teacherID, $responsibility)
    {
        $query = $this->_db->getQuery(true);
        $query->insert('#__thm_organizer_subject_teachers')->columns('subjectID, teacherID, teacherResp');
        $query->values("'$subjectID', '$teacherID', '$responsibility'");
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
        return true;
    }

    /**
     * Processes the mappings of the subject selected
     * 
     * @param   int    $subjectID  the id of the subject
     * @param   array  &$data      the post data
     * 
     * @return  boolean  true on success, otherwise false
     */
    private function processFormMappings($subjectID, &$data)
    {
        $model = JModelLegacy::getInstance('mapping', 'THM_OrganizerModel');

        // No mappings desired
        if (empty($data['parentID']))
        {
            return $model->deleteByResourceID($subjectID, 'subject');
        }

        return $model->saveSubject($data);
    }
    
    /**
     * Checks whether subject nodes have the expected structure and required
     * information
     *
     * @param   object  &$scheduleModel  the validating schedule model
     * @param   object  &$subjectNode    the subject node to be validated
     *
     * @return void
     */
    public function validate(&$scheduleModel, &$subjectNode)
    {
        $this->_scheduleModel = $scheduleModel;

        $gpuntisID = trim((string) $subjectNode[0]['id']);
        if (empty($gpuntisID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_SUBJECT_ID_MISSING"), $this->scheduleErrors))
            {
                $this->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_SUBJECT_ID_MISSING");
            }
            return;
        }

        $department = $this->_scheduleModel->schedule->departmentname;
        $subjectID = str_replace('SU_', '', $gpuntisID);
        $subjectIndex = $department . "_" . $subjectID;
        $this->_scheduleModel->schedule->subjects->$subjectIndex = new stdClass;
        $this->_scheduleModel->schedule->subjects->$subjectIndex->gpuntisID = $gpuntisID;
        $this->_scheduleModel->schedule->subjects->$subjectIndex->name = $subjectID;

        
        $longname = $this->validateLongname($subjectNode, $subjectIndex, $subjectID);
        if (!$longname)
        {
            return;
        }

        $warningString = '';
        $this->validateSubjectNo($subjectNode, $subjectIndex, $warningString);
        $this->validateField($subjectNode, $subjectIndex, $warningString);
        if (!empty($warningString))
        {
            $warning = JText::sprintf("COM_THM_ORGANIZER_ERROR_SUBJECT_PROPERTY_MISSING", $longname, $subjectID, $warningString);
            $this->_scheduleModel->scheduleWarnings[] = $warning;
        }
    }

    /**
     * Validates the subject's longname
     * 
     * @param   object  &$subjectNode  the subject node object
     * @param   string  $subjectIndex  the subject's interdepartment unique identifier
     * @param   string  $subjectID     the subject's id
     * 
     * @return  mixed  string longname if valid, otherwise false
     */
    private function validateLongname(&$subjectNode, $subjectIndex, $subjectID)
    {
        $longname = trim((string) $subjectNode->longname);
        if (empty($longname))
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_ERROR_SUBJECT_LONGNAME_MISSING', $subjectID);
            return false;
        }
        $this->_scheduleModel->schedule->subjects->$subjectIndex->longname = $longname;
        return $longname;
    }

    /**
     * Validates the subject's subject number (text) attribute
     * 
     * @param   object  &$subjectNode    the subject node object
     * @param   string  $subjectIndex    the subject's interdepartment unique identifier
     * @param   string  &$warningString  a string with missing fields
     * 
     * @return  void
     */
    private function validateSubjectNo(&$subjectNode, $subjectIndex, &$warningString)
    {
        $subjectNo = trim((string) $subjectNode->text);
        if (empty($subjectNo))
        {
            $warningString .= empty($warningString)? '' : ', ';
            $warningString .= JText::_('COM_THM_ORGANIZER_ERROR_SUBJECT_NUMBER');
        }
        $this->_scheduleModel->schedule->subjects->$subjectIndex->subjectNo = empty($subjectNo)? '' : $subjectNo;
    }

    /**
     * Validates the subject's field (description) attribute
     * 
     * @param   object  &$subjectNode    the subject node object
     * @param   string  $subjectIndex    the subject's interdepartment unique identifier
     * @param   string  &$warningString  a string with missing fields
     * 
     * @return  void
     */
    private function validateField(&$subjectNode, $subjectIndex, &$warningString)
    {
        $descriptionID = str_replace('DS_', '', trim($subjectNode->subject_description[0]['id']));
        if (empty($descriptionID)
         OR empty($this->_scheduleModel->schedule->fields->$descriptionID))
        {
            $warningString .= empty($warningString)? '' : ', ';
            $warningString .= JText::_('COM_THM_ORGANIZER_ERROR_FIELD');
        }
        $this->_scheduleModel->schedule->subjects->$subjectIndex->description = empty($descriptionID)? '' : $descriptionID;
    }
}
