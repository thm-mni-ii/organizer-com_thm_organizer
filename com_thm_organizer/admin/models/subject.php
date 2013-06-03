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

/**
 * Class THM_OrganizerModelSubject for component com_thm_organizer
 * Class provides methods to deal with asset
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSubject extends JModel
{
    /**
     * Attempts to save a subject entry, updating subject-teacher data as
     * necessary.
     * 
     * @return true on success, otherwise false
     */
	public function save()
	{
        $data = JRequest::getVar('jform', null, null, null, 4);

		$dbo = JFactory::getDbo();
		$dbo->transactionStart();

        $table = JTable::getInstance('subjects', 'thm_organizerTable');
        $success = $table->save($data);
        
        // Successfully inserted a new subject
        if ($success AND empty($data['id']))
        {
            $dbo->transactionCommit();
            return $table->id;
        }

        // New subject unsuccessfully inserted
        elseif (empty($data['id']))
        {
            $dbo->transactionRollback();
            return false; 
        }

        // Process mapping & responsibilities information
		else
		{
            $deleteQuery = $dbo->getQuery(true);
            $deleteQuery->delete('#__thm_organizer_subject_teachers')->where("subjectID = '{$data['id']}'");
            $dbo->setQuery((string) $deleteQuery);
            try
            {
                $responsibilitiesDeleted = $dbo->query();
            }
            catch (Exception $exc)
            {
                $dbo->transactionRollback();
                return false;
            }

            $insertQuery = $dbo->getQuery(true);
            $insertQuery->insert('#__thm_organizer_subject_teachers');
            $insertQuery->columns(array('subjectID', 'teacherID', 'teacherResp'));
            $insertQuery->values("'{$data['id' ]}', '{$data['responsible' ]}', '1'");
            foreach ($data['teacher'] AS $teacher)
            {
                $insertQuery->values("'{$data['id' ]}', '$teacher', '2'");
            }
            $dbo->setQuery((string) $insertQuery);
            try
            {
                $dbo->query();
            }
            catch (Exception $exc)
            {
                $dbo->transactionRollback();
                return false;
            }


            $model = JModel::getInstance('mapping', 'THM_OrganizerModel');
            $mappingsDeleted = $model->deleteByResourceID($table->id, 'subject');

            // No mappings desired
            if (empty($data['parentID']) AND $mappingsDeleted)
            {
                    $dbo->transactionCommit();
                    return $table->id;
            }
            elseif (empty($data['parentID']))
            {
                $dbo->transactionRollback();
                return false;
            }
            else
            {
                $mappingSaved = $model->saveSubject($data);
                if ($mappingSaved)
                {
                    $dbo->transactionCommit();
                    return $table->id;
                }
                else
                {
                    $dbo->transactionRollback();
                    return false;
                }
            }
		}
	}

    /**
     * Updates the entries of the subject teachers table
     * 
     * @param   array  $data  the post data
     * 
     * @return  boolean  true on success, otherwise false
     */
    public function updateSubjectTeachers($data)
    {
        $dbo = JFactory::getDbo();
        $deleteQuery = $dbo->getQuery(true);
        $deleteQuery->delete('#__thm_organizer_subject_teachers');
        $deleteQuery->where("subjectID = '{$data['id']}'");
        $dbo->setQuery((string) $deleteQuery);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            return false;
        }
                
        $teacherValues = $data['teacher'];
        foreach ($teacherValues as $key => $teacherID)
        {
            $teacherValues[$key] = "'{$data['id']}', '$teacherID', '2'";
        }
        $teacherValues[] = "'{$data['id']}', '{$data['responsible']}', '1'";

        $teachersQuery = $dbo->getQuery(true);
        $teachersQuery->insert('#__thm_organizer_subject_teachers');
        $teachersQuery->columns('subjectID, teacherID, teacherResp');
        $teachersQuery->values($teacherValues);
        $dbo->setQuery((string) $teachersQuery);
        try
        {
            $dbo->query();
        }
        catch (Exception $exception)
        {
            return false;
        }
        return true;
    }

    /**
     * Attempts to delete the selected subject entries
     *
     * @return  boolean true on success, otherwise false
     */
    public function delete()
    {
        $success = true;
        $subjectIDs = JRequest::getVar('cid', array(0), 'post', 'array');
        $table = JTable::getInstance('subjects', 'thm_organizerTable');
        if (!empty($subjectIDs))
        {
            $dbo = JFactory::getDbo();
            $dbo->transactionStart();
            foreach ($subjectIDs as $subjectID)
            {
                $success = $table->delete($subjectID);
                if (!$success)
                {
                    $dbo->transactionRollback();
                    return $success;
                }
            }
            $dbo->transactionCommit();
        }
        return $success;
    }
}
