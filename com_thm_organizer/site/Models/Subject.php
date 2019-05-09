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

use Exception;
use Organizer\Helpers\Subjects;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class which manages stored subject data.
 */
class Subject extends BaseModel
{
    const RESPONSIBLE = 1;

    const TEACHER = 2;

    /**
     * Adds a prerequisite association. No access checks => this is not directly accessible and requires differing
     * checks according to its calling context.
     *
     * @param int   $subjectID    the id of the subject
     * @param array $prerequisite the id of the prerequisite
     *
     * @return bool  true on success, otherwise false
     */
    private function addPrerequisite($subjectID, $prerequisite)
    {
        $query = $this->_db->getQuery(true);
        $query->insert('#__thm_organizer_prerequisites')->columns('subjectID, prerequisite');
        $query->values("'$subjectID', '$prerequisite'");
        $this->_db->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('execute');
    }

    /**
     * Adds a Subject Plan_Subject association. No access checks => this is not directly accessible and requires
     * differing checks according to its calling context.
     *
     * @param int   $subjectID      the id of the subject
     * @param array $planSubjectIDs the id of the planSubject
     *
     * @return bool  true on success, otherwise false
     */
    private function addSubjectMappings($subjectID, $planSubjectIDs)
    {
        $query = $this->_db->getQuery(true);
        $query->insert('#__thm_organizer_subject_mappings')->columns('subjectID, plan_subjectID');
        foreach ($planSubjectIDs as $planSubjectID) {
            $query->values("'$subjectID', '$planSubjectID'");
        }

        $this->_db->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('execute');
    }

    /**
     * Adds a teacher association. No access checks => this is not directly accessible and requires differing checks
     * according to its calling context.
     *
     * @param int   $subjectID      the id of the subject
     * @param array $teacherID      the id of the teacher
     * @param int   $responsibility the teacher's responsibility for the
     *                              subject
     *
     * @return bool  true on success, otherwise false
     */
    public function addTeacher($subjectID, $teacherID, $responsibility)
    {
        $query = $this->_db->getQuery(true);
        $query->insert('#__thm_organizer_subject_teachers')->columns('subjectID, teacherID, teacherResp');
        $query->values("'$subjectID', '$teacherID', '$responsibility'");
        $this->_db->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('execute');
    }

    /**
     * Checks if the property should be displayed. Setting it to NULL if not.
     *
     * @param array  &$data     the form data
     * @param string  $property the property name
     *
     * @return void  can change the &$data value at the property name index
     */
    private function cleanStarProperty(&$data, $property)
    {
        if (!isset($data[$property])) {
            return;
        }

        if ($data[$property] == '-1') {
            $data[$property] = 'NULL';
        }
    }

    /**
     * Attempts to delete the selected subject entries and related mappings
     *
     * @return boolean true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function delete()
    {
        if (!Access::allowDocumentAccess()) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $subjectIDs = OrganizerHelper::getSelectedIDs();
        if (!empty($subjectIDs)) {
            $this->_db->transactionStart();
            foreach ($subjectIDs as $subjectID) {

                if (!Access::allowDocumentAccess('subject', $subjectID)) {
                    $this->_db->transactionRollback();
                    throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
                }

                $deleted = $this->deleteSingle($subjectID);
                if (!$deleted) {
                    $this->_db->transactionRollback();

                    return false;
                }
            }
            $this->_db->transactionCommit();
        }

        return true;
    }

    /**
     * Deletes an individual subject entry in the mappings and subjects tables. No access checks => this is not directly
     * accessible and requires differing checks according to its calling context.
     *
     * @param int $subjectID the id of the subject to be deleted
     *
     * @return boolean  true if successful, otherwise false
     */
    public function deleteSingle($subjectID)
    {
        $table           = $this->getTable();
        $mappingModel    = new Mapping;
        $mappingsDeleted = $mappingModel->deleteByResourceID($subjectID, 'subject');
        if (!$mappingsDeleted) {
            return false;
        }

        $subjectDeleted = $table->delete($subjectID);
        if (!$subjectDeleted) {
            return false;
        }

        return true;
    }

    /**
     * Processes the mappings of the subject selected
     *
     * @param array &$data the post data
     *
     * @return boolean  true on success, otherwise false
     */
    private function processFormMappings(&$data)
    {
        $model = new Mapping;

        // No mappings desired
        if (empty($data['parentID'])) {
            return $model->deleteByResourceID($data['id'], 'subject');
        }

        return $model->saveSubject($data);
    }

    /**
     * Processes the subject pre- & postrequisites selected for the subject
     *
     * @param array &$data the post data
     *
     * @return bool  true on success, otherwise false
     */
    private function processFormPrerequisites(&$data)
    {
        if (!isset($data['prerequisites']) and !isset($data['postrequisites'])) {
            return true;
        }

        $subjectID = $data['id'];

        if (!$this->removePrerequisites($subjectID)) {
            return false;
        }

        if (!empty($data['prerequisites'])) {
            foreach ($data['prerequisites'] as $prerequisiteID) {
                $preAdded = $this->addPrerequisite($subjectID, $prerequisiteID);
                if (!$preAdded) {
                    return false;
                }
            }
        }

        if (!empty($data['postrequisites'])) {
            foreach ($data['postrequisites'] as $postrequisiteID) {
                $postAdded = $this->addPrerequisite($postrequisiteID, $subjectID);
                if (!$postAdded) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Processes the subject mappings selected for the subject
     *
     * @param array &$data the post data
     *
     * @return bool  true on success, otherwise false
     */
    private function processFormSubjectMappings(&$data)
    {
        if (!isset($data['planSubjectIDs'])) {
            return true;
        }

        $subjectID = $data['id'];

        if (!$this->removeSubjectMappings($subjectID)) {
            return false;
        }
        if (!empty($data['planSubjectIDs'])) {
            $respAdded = $this->addSubjectMappings($subjectID, $data['planSubjectIDs']);
            if (!$respAdded) {
                return false;
            }
        }

        return true;
    }

    /**
     * Processes the teachers selected for the subject
     *
     * @param array &$data the post data
     *
     * @return bool  true on success, otherwise false
     */
    private function processFormTeachers(&$data)
    {
        if (!isset($data['responsible']) and !isset($data['teacherID'])) {
            return true;
        }

        $subjectID = $data['id'];

        if (!$this->removeTeachers($subjectID)) {
            return false;
        }

        if (!empty($data['responsible'])) {
            foreach ($data['responsible'] as $responsibleID) {
                $respAdded = $this->addTeacher($subjectID, $responsibleID, self::RESPONSIBLE);
                if (!$respAdded) {
                    return false;
                }
            }
        }

        if (!empty($data['teacherID'])) {
            foreach ($data['teacherID'] as $teacherID) {
                $teacherAdded = $this->addTeacher($subjectID, $teacherID, self::TEACHER);
                if (!$teacherAdded) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Removes pre- & postrequisite associations for the given subject. No access checks => this is not directly
     * accessible and requires differing checks according to its calling context.
     *
     * @param int $subjectID the subject id
     *
     * @return boolean
     */
    private function removePrerequisites($subjectID)
    {
        $query = $this->_db->getQuery(true);
        $query->delete('#__thm_organizer_prerequisites')->where("subjectID = '$subjectID' OR prerequisite ='$subjectID'");
        $this->_db->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('execute');
    }

    /**
     * Removes planSubject associations for the given subject. No access checks => this is not directly accessible and
     * requires differing checks according to its calling context.
     *
     * @param int $subjectID the subject id
     *
     * @return boolean
     */
    private function removeSubjectMappings($subjectID)
    {
        $query = $this->_db->getQuery(true);
        $query->delete('#__thm_organizer_subject_mappings')->where("subjectID = '$subjectID'");
        $this->_db->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('execute');
    }

    /**
     * Removes teacher associations for the given subject and level of
     * responsibility. No access checks => this is not directly accessible and requires differing checks according to
     * its calling context.
     *
     * @param int $subjectID      the subject id
     * @param int $responsibility the teacher responsibility level (1|2)
     *
     * @return boolean
     */
    public function removeTeachers($subjectID, $responsibility = null)
    {
        $query = $this->_db->getQuery(true);
        $query->delete('#__thm_organizer_subject_teachers')->where("subjectID = '$subjectID'");
        if (!empty($responsibility)) {
            $query->where("teacherResp = '$responsibility'");
        }

        $this->_db->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('execute');
    }

    /**
     * Attempts to save a subject entry, updating subject-teacher data as
     * necessary.
     *
     * @return true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function save()
    {
        $data = OrganizerHelper::getForm();

        if (!isset($data['id'])) {
            throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
        } elseif (!Subjects::allowEdit($data['id'])) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        if (!Subjects::allowEdit($data['id'])) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $starProperties = ['expertise', 'self_competence', 'method_competence', 'social_competence'];
        foreach ($starProperties as $property) {
            $this->cleanStarProperty($data, $property);
        }

        $table          = $this->getTable();
        $subjectSuccess = $table->save($data);

        if (!$subjectSuccess) {
            return false;
        }

        $processMappings = (!empty($data['id']) and isset($data['parentID']));
        $data['id']      = $table->id;

        if (!$this->processFormTeachers($data)) {
            return false;
        }

        if (!$this->processFormSubjectMappings($data)) {
            return false;
        }

        if (!$this->processFormPrerequisites($data)) {
            return false;
        }

        if ($processMappings and !$this->processFormMappings($data)) {
            return false;
        }

        $lessonID = OrganizerHelper::getInput()->getInt('lessonID', 0);
        if (!empty($lessonID)) {
            Courses::refreshWaitList($lessonID);
        }

        return $table->id;
    }
}
