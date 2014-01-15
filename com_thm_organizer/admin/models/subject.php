<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSubject
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
defined('RESPONSIBLE') OR define('RESPONSIBLE', 1);
defined('TEACHER') OR define('TEACHER', 2);
/**
 * Provides persistence handling for subjects
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSubject extends JModel
{
    private $_scheduleModel = null;

    /**
     * Attempts to delete the selected subject entries and related mappings
     *
     * @return  boolean true on success, otherwise false
     */
    public function delete()
    {
        $subjectIDs = JRequest::getVar('cid', array(0), 'post', 'array');
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
        $mappingModel = JModel::getInstance('mapping', 'THM_OrganizerModel');
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
     * Method to import data associated with subjects from LSF
     *
     * @return  bool  true on success, otherwise false
     */
    public function importLSFDataBatch()
    {
        $subjectIDs = JRequest::getVar('cid', array(), 'post', 'array');
        $this->_db->transactionStart();
        foreach ($subjectIDs as $subjectID)
        {
            $subjectImported = $this->importLSFDataSingle($subjectID);
            if ($subjectImported == 'error')
            {
                $this->_db->transactionRollback();
                return false;
            }
        }
        $this->_db->transactionCommit();
        return true;
    }

    /**
     * Sets a given value at a given index in the subject array if not empty.
     * This prevents overwrites of local changes to data not existent within LSF.
     *
     * @param   array   &$subject  the subject being filled
     * @param   string  $index     the index at which to set the value
     * @param   mixed   $value     the value to be set at the index
     *
     * @return  void
     */
    private function setSubjectAttribute(&$subject, $index, $value)
    {
        if (!empty($value))
        {
            $subject[$index] = $value;
        }
    }

    /**
     * Sets description attributes
     * 
     * @param   array  &$subject      the subject data
     * @param   array  $descriptions  an array of description objects
     * 
     * @return  void
     */
    private function setDescriptionAttributes(&$subject, $descriptions)
    {
        foreach ($descriptions as $description)
        {
            if ($description->sprache == 'de')
            {
                $this->setSubjectAttribute($subject, 'description_de', (string) $description->txt);
            }
            if ($description->sprache == 'en')
            {
                $this->setSubjectAttribute($subject, 'description_en', (string) $description->txt);
            }
        }
    }

    /**
     * Sets attributes dealing with required student expenditure
     * 
     * @param   array   &$subject  the subject data
     * @param   string  $text      the text of the expenditure node
     * 
     * @return void
     */
    private function setExpenditureAttributes(&$subject, $text)
    {
        $matches = array();
        preg_match_all('/[0-9]+/', $text, $matches, PREG_PATTERN_ORDER);
        if (!empty($matches) AND !empty($matches[0]) AND count($matches[0]) == 3)
        {
            if (empty($subject['creditpoints']))
            {
                $this->setSubjectAttribute($subject, 'creditpoints', $matches[0][0]);
            }
            if (empty($subject['expenditure']))
            {
                $this->setSubjectAttribute($subject, 'expenditure', $matches[0][1]);
            }
            if (empty($subject['present']))
            {
                $this->setSubjectAttribute($subject, 'present', $matches[0][2]);
            }
            if (empty($subject['independent']))
            {
                $this->setSubjectAttribute($subject, 'present', $subject['expenditure'] - $subject['present']);
            }
        }
    }

    /**
     * Attempts to save a subject entry, updating subject-teacher data as
     * necessary.
     *
     * @return true on success, otherwise false
     */
    public function save()
    {
        $data = JRequest::getVar('jform', null, null, null, 4);

        $this->_db->transactionStart();

        $table = JTable::getInstance('subjects', 'thm_organizerTable');
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
     * Processes the teachers selected for the subject
     * 
     * @param   array  &$data  the post data
     * 
     * @return  void
     */
    private function processFormTeachers(&$data)
    {
        $subjectID = $data['id'];
        $this->removeTeachers($subjectID);

        foreach ($data['responsibleID'] AS $responsibleID)
        {
            $respAdded = $this->addTeacher($subjectID, $responsibleID, RESPONSIBLE);
            if (!$respAdded)
            {
                return false;
            }
        }
        foreach ($data['teacherID'] AS $teacherID)
        {
            $teacherAdded = $this->addTeacher($subjectID, $teacherID, TEACHER);
            if (!$teacherAdded)
            {
                return false;
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
            $this->_db->query();
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
            $this->_db->query();
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
        $model = JModel::getInstance('mapping', 'THM_OrganizerModel');
        $mappingsDeleted = $model->deleteByResourceID($subjectID, 'subject');
        if (!$mappingsDeleted)
        {
            return false;
        }

        // No mappings desired
        if (empty($data['parentID']))
        {
            return true;
        }

        $mappingSaved = $model->saveSubject($data);
        if (!$mappingSaved)
        {
            return false;
        }
        return true;
    }

    /**
     * Parces the prerequisites text and replaces subject references with links to the subjects
     * 
     * @param   string  $originalText    the original text of the object
     * @param   string  $languageTag     the desired output language
     * @param   array   &$prerequisites  an array containing prerequisite ids
     * 
     * @return  string  the text for the subject's prereuisites
     */
    private function resolvePrerequisites($originalText, $languageTag, &$prerequisites)
    {
        $modules = array();
        $parts = preg_split('[\,|\ ]', $originalText);
        foreach ($parts as $part)
        {
            if (preg_match('/[0-9]+/', $part))
            {
                $moduleLink = $this->getModuleInformation($part, $languageTag, $prerequisites);
                if (!empty($moduleLink))
                {
                    $modules[$part] = $moduleLink;
                }
            }
        }
        if (!empty($modules))
        {
            foreach ($modules AS $number => $link)
            {
                $originalText = str_replace($number, $link, $originalText);
            }
        }
        return $originalText;
    }

    /**
     * Builds a link to a subject description if available
     * 
     * @param   string  $moduleNumber    the external id of the subject
     * @param   string  $languageTag     the language tag
     * @param   array   &$prerequisites  an array containing prerequisite ids
     * 
     * @return  mixed  html link string on success, otherwise false
     */
    private function getModuleInformation($moduleNumber, $languageTag, &$prerequisites)
    {
        $query = $this->_db->getQuery(true);
        $query->select("id, name_$languageTag AS name");
        $query->from('#__thm_organizer_subjects')->where("externalID = '$moduleNumber'");
        $this->_db->setQuery((string) $query);
        $subjectInfo = $this->_db->loadAssoc();
        if (empty($subjectInfo))
        {
            return false;
        }

        if (!in_array($subjectInfo['id'], $prerequisites))
        {
            $prerequisites[] = $subjectInfo['id'];
        }

        $subjectURL = JURI::root() . 'index.php?option=com_thm_organizer&view=subject_details';
        $subjectURL .= "&languageTag=$languageTag&id={$subjectInfo['id']}";
        
        $itemID = JRequest::getInt('Itemid');
        $subjectURL .= !empty($itemID)? "&Itemid=$itemID" : '';
        $href = JRoute::_($subjectURL);

        return JHtml::link($href, $subjectInfo['name']);
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
            if (!in_array(JText::_("COM_THM_ORGANIZER_SU_ID_MISSING"), $this->scheduleErrors))
            {
                $this->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_SU_ID_MISSING");
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
            $warning = JText::sprintf("COM_THM_ORGANIZER_SU_FIELD_MISSING", $longname, $subjectID, $warningString);
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
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_SU_LN_MISSING', $subjectID);
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
            $warningString .= JText::_('COM_THM_ORGANIZER_SUBJECTNO'); 
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
            $warningString .= JText::_('COM_THM_ORGANIZER_DESCRIPTION_PROPERTY');
        }
        $this->_scheduleModel->schedule->subjects->$subjectIndex->description = empty($descriptionID)? '' : $descriptionID;
    }
}
