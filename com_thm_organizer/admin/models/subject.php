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
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper' . DS . 'lsfapi.php';

/**
 * Provides persistence handling for subjects
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSubject extends JModel
{
    /**
     * Attempts to delete the selected subject entries and related mappings
     *
     * @return  boolean true on success, otherwise false
     */
    public function delete()
    {
        $resourceIDs = JRequest::getVar('cid', array(0), 'post', 'array');
        if (!empty($resourceIDs))
        {
            $dbo = JFactory::getDbo();
            $dbo->transactionStart();
            $table = JTable::getInstance('subjects', 'thm_organizerTable');
            $model = JModel::getInstance('mapping', 'THM_OrganizerModel');
            foreach ($resourceIDs as $resourceID)
            {
                $mappingsDeleted = $model->deleteByResourceID($resourceID, 'subject');
                if (!$mappingsDeleted)
                {
                    $dbo->transactionRollback();
                    return FALSE;
                }

                $resourceDeleted = $table->delete($resourceID);
                if (!$resourceDeleted)
                {
                    $dbo->transactionRollback();
                    return FALSE;
                }
            }
            $dbo->transactionCommit();
        }
        return TRUE;
    }

    /**
	 * Method to import data associated with subjects from LSF
	 *
	 * @return  bool  true on success, otherwise false
	 */
    public function importLSFDataBatch()
    {
		$resourceIDs = JRequest::getVar('cid', array(), 'post', 'array');
        foreach ($resourceIDs as $resourceID)
        {
            $resourceImported = $this->importLSFDataSingle($resourceID);
            if (!$resourceImported)
            {
                return false;
            }
        }
		return true;
    }

    /**
     * Method to import data associated with a subject from LSF
     * 
     * @param   int  $subjectID  the id opf the subject entry
     * 
     * @return  boolean  true on success, otherwise false
     */
    public function importLSFDataSingle($subjectID)
    {
        $table = JTable::getInstance('subjects', 'thm_organizerTable');
        $loaded = $table->load($subjectID);
        if (!$loaded or empty($table->lsfID))
        {
            return false;
        }

        $client = new THM_OrganizerLSFClient();
        $lsfData = $client->getModuleByModulid($table->lsfID);

        $data = array();
        foreach ($lsfData->modul->children() as $child)
        {
            $name = $child->getName();
            switch ($name)
            {
                case 'nrmni':
                    $data['externalID'] = (string) $child;
                    break;
                case 'kuerzel':
                    $data['abbreviation_de'] = (string) $child;
                    break;
                case 'kuerzelen':
                    $data['abbreviation_en'] = (string) $child;
                    break;
                case 'kurzname':
                    $data['short_name_de'] = (string) $child;
                    break;
                case 'kurznameen':
                    $data['short_name_en'] = (string) $child;
                    break;
                case 'titelde':
                    $data['name_de'] = (string) $child;
                    break;
                case 'titelen':
                    $data['name_en'] = (string) $child;
                    break;
                case 'kurzbeschr':
                    if ($child->sprache == 'de')
                    {
                        $data['description_de'] = $child->txt;
                    }
                    if ($child->sprache == 'en')
                    {
                        $data['description_en'] = $child->txt;
                    }
                    break;
                case 'lernziel':
                    if ($child->sprache == 'de')
                    {
                        $data['objective_de'] = $child->txt;
                    }
                    if ($child->sprache == 'en')
                    {
                        $data['objective_en'] = $child->txt;
                    }
                    break;
                case 'lerninhalt':
                    if ($child->sprache == 'de')
                    {
                        $data['content_de'] = $child->txt;
                    }
                    if ($child->sprache == 'en')
                    {
                        $data['content_en'] = $child->txt;
                    }
                    break;
                case 'vorleistung':
                    if ($child->sprache == 'de')
                    {
                        $data['preliminary_work_de'] = $child->txt;
                    }
                    if ($child->sprache == 'en')
                    {
                        $data['preliminary_work_en'] = $child->txt;
                    }
                    break;
                case 'turnus':
                    $data['frequency'] = (string) $child;
                    break;
                case 'lp':
                    $data['creditpoints'] = (string) $child;
                    break;
                case 'ktextform':
                    $data['method'] = (string) $child;
                    break;
                case 'ktextpart':
                    $data['proof'] = (string) $child;
                    break;
                default:
                    break;
            }
        }

        if (empty($data['abbreviation_en']) AND isset($data['abbreviation_de']))
        {
            $data['abbreviation_en'] = $data['abbreviation_de'];
        }
        if (empty($data['short_name_en']) AND isset($data['short_name_de']))
        {
            $data['short_name_en'] = $data['short_name_de'];
        }
        if (empty($data['name_en']) AND isset($data['name_de']))
        {
            $data['name_en'] = $data['name_de'];
        }
        return $table->save($data);
    }

    /**
     * Creates a subject entry if none exists and imports data to fill it
     * 
     * @param   object  &$stub  a simplexml object containing rudimentary subject data
     * 
     * @return  mixed  int value of subject id on success, otherwise false
     */
    public function processLSFStub(&$stub)
    {
        if ((empty($stub->modulid) AND empty($stub->pordid)) OR (empty($stub->modulnrhis) AND empty($stub->nrhis)))
        {
            return FALSE;
        }
        $lsfID = (string) (empty($stub->modulid)?  $stub->pordid : $stub->modulid);
        $hisID = (string) (empty($stub->modulnrhis)?  $stub->nrhis: $stub->modulnrhis);

        $table = JTable::getInstance('subjects', 'thm_organizerTable');
        $table->load(array('lsfID' => $lsfID));

        if (empty($table->id))
        {
            $data = array('lsfID' => $lsfID, 'hisID' => $hisID);
            $stubSaved = $table->save($data);
            if (!$stubSaved)
            {echo "subject not saved";
                return FALSE;
            }
        }
 
        return $this->importLSFDataSingle($table->id);
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
                $dbo->query();
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
            foreach ($data['teacherID'] AS $teacher)
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
}
