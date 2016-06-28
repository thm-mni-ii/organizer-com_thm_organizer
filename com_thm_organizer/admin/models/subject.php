<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSubject
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
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

        $subjectSuccess = $table->save($data);

        if (!$subjectSuccess)
        {
            $this->_db->transactionRollback();
            return false;
        }

        $new = empty($data['id']);
        $data['id'] = $table->id;

        if (!$this->processFormTeachers($data))
        {
            $this->_db->transactionRollback();
            return false;
        }

        if (!$this->processFormPrerequisites($data))
        {
            $this->_db->transactionRollback();
            return false;
        }

        if (!$new)
        {
            $mappingSuccess = $this->processFormMappings($table->id, $data);
            if (!$mappingSuccess)
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

        if (!$this->removeTeachers($subjectID))
        {
            return false;
        }

        if (!empty($data['responsible']))
        {
            foreach ($data['responsible'] AS $responsibleID)
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
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
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
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return false;
        }

        return true;
    }

    /**
     * Processes the subject pre- & postrequisites selected for the subject
     *
     * @param   array  &$data  the post data
     *
     * @return  bool  true on success, otherwise false
     */
    private function processFormPrerequisites(&$data)
    {
        $subjectID = $data['id'];

        if (!$this->removePrerequisites($subjectID))
        {
            return false;
        }

        if (!empty($data['prerequisites']))
        {
            foreach ($data['prerequisites'] AS $prerequisiteID)
            {
                $preAdded = $this->addPrerequisite($subjectID, $prerequisiteID);
                if (!$preAdded)
                {
                    return false;
                }
            }
        }

        if (!empty($data['postrequisites']))
        {
            foreach ($data['postrequisites'] AS $postrequisiteID)
            {
                $postAdded = $this->addPrerequisite($postrequisiteID, $subjectID);
                if (!$postAdded)
                {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Removes pre- & postrequisite associations for the given subject
     *
     * @param   int  $subjectID       the subject id
     *
     * @return  boolean
     */
    public function removePrerequisites($subjectID)
    {
        $query = $this->_db->getQuery(true);
        $query->delete('#__thm_organizer_prerequisites')->where("subjectID = '$subjectID' OR prerequisite ='$subjectID'");
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return false;
        }

        return true;
    }

    /**
     * Adds a prerequisite association
     *
     * @param   int    $subjectID     the id of the subject
     * @param   array  $prerequisite  the id of the prerequisite
     *
     * @return  bool  true on success, otherwise false
     */
    public function addPrerequisite($subjectID, $prerequisite)
    {
        $query = $this->_db->getQuery(true);
        $query->insert('#__thm_organizer_prerequisites')->columns('subjectID, prerequisite');
        $query->values("'$subjectID', '$prerequisite'");
        $this->_db->setQuery((string) $query);
        try
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
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
}
