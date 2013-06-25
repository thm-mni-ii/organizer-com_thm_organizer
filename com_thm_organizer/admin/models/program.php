<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelProgram
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper' . DS . 'lsfapi.php';

/**
 * Provides persistence handling for degree programs
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelProgram extends JModel
{
    /**
     * Attempts to delete the selected degree program entries and related mappings
     *
     * @return  boolean  True if successful, false if an error occurs.
     */
    public function delete()
    {
           $resourceIDs = JRequest::getVar('cid', array(), 'post', 'array');
        if (!empty($resourceIDs))
        {
            $dbo = JFactory::getDbo();
            $dbo->transactionStart();
            $table = JTable::getInstance('programs', 'thm_organizerTable');
            $model = JModel::getInstance('mapping', 'THM_OrganizerModel');
            foreach ($resourceIDs as $resourceID)
            {
                $mappingDeleted = $model->deleteByResourceID($resourceID, 'program');
                if (!$mappingDeleted)
                {
                    $dbo->transactionRollback();
                    return false;
                }

                $resourceDeleted = $table->delete($resourceID);
                if (!$resourceDeleted)
                {
                    $dbo->transactionRollback();
                    return false;
                }
            }
            $dbo->transactionCommit();
        }
        return true;
    }

    /**
     * Retrieves program information relevant for soap queries to the LSF system.
     * 
     * @param   int  $programID  the id of the degree program
     * 
     * @return  array  empty if the program could not be found
     */
    private function getLSFQueryData($programID)
    {
        $dbo = JFactory::getDbo();
        $lsfDataQuery = $dbo->getQuery(true);
        $lsfDataQuery->select("'studiengang' AS lsfType, lsfFieldID AS program, lsfDegree AS degree, version");
        $lsfDataQuery->from('#__thm_organizer_programs AS p');
        $lsfDataQuery->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
        $lsfDataQuery->where("p.id = '$programID'");
        $dbo->setQuery((string) $lsfDataQuery);
        $lsfData = $dbo->loadAssoc();
        return empty($lsfData)? array() : $lsfData;
    }

    /**
     * Method to import data associated with degree programs from LSF
     *
     * @return  bool  true on success, otherwise false
     */
    public function importLSFDataBatch()
    {
        $dbo = JFactory::getDbo();
        $dbo->transactionStart();
        $resourceIDs = JRequest::getVar('cid', array(), 'post', 'array');
        foreach ($resourceIDs as $resourceID)
        {
            $resourceImported = $this->importLSFDataSingle($resourceID);
            if (!$resourceImported)
            {
                $dbo->transactionRollback();
                return false;
            }
        }
        $dbo->transactionCommit();
        return true;
    }

    /**
     * Method to import data associated with a degree program from LSF
     * 
     * @param   int  $programID  the id of the program to be imported
     * 
     * @return  boolean  true on success, otherwise false
     */
    public function importLSFDataSingle($programID)
    {
        $client = new THM_OrganizerLSFClient;
        $lsfData = $this->getLSFQueryData($programID);
        if (empty($lsfData))
        {
            return false;
        }
        
        $lsfProgram = $client->getModules($lsfData['lsfType'], $lsfData['program'], $lsfData['degree'], $lsfData['version']);
        if (empty($lsfProgram))
        {
            return false;
        }
        
        if (isset($lsfProgram->gruppe) AND count($lsfProgram->gruppe))
        {
            // Iterate over the entire over each course-group of the returned xml structure
            $subjectModel = JModel::getInstance('subject', 'THM_OrganizerModel');
            $poolModel = JModel::getInstance('pool', 'THM_OrganizerModel');
            foreach ($lsfProgram->gruppe as $resource)
            {
                if ($resource->pordtyp == 'K')
                {
                    $poolProcessed = $poolModel->processLSFStub($resource);
                    if (!$poolProcessed)
                    {
                        return false;
                    }
                }
                else
                {
                    $subjectProcessed = $subjectModel->processLSFStub($resource);
                    if (!$subjectProcessed)
                    {
                        return false;
                    }
                }
            }
            
            $mappingModel = JModel::getInstance('mapping', 'THM_OrganizerModel');
            $mappingsAdded = $mappingModel->addLSFMappings($programID, $lsfProgram);
            if (!$mappingsAdded)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Method to save degree programs
     *
     * @return  Boolean
     */
    public function save()
    {
        $dbo = JFactory::getDbo();
        $data = JRequest::getVar('jform', null, null, null, 4);
        $dbo->transactionStart();
        $table = JTable::getInstance('programs', 'thm_organizerTable');
        $dpSuccess = $table->save($data);
        if ($dpSuccess)
        {
            $model = JModel::getInstance('mapping', 'THM_OrganizerModel');
            $mappingSuccess = $model->saveProgram($table->id);
            if ($mappingSuccess)
            {
                $dbo->transactionCommit();
                return $table->id;
            }
        }
        $dbo->transactionRollback();
        return false;
    }

    /**
     * Method to save existing degree programs as copies
     *
     * @return  Boolean
     */
    public function save2copy()
    {
        $dbo = JFactory::getDbo();
        $data = JRequest::getVar('jform', null, null, null, 4);
        if (isset($data['id']))
        {
            unset($data['id']);
        }
        $dbo->transactionStart();
        $table = JTable::getInstance('programs', 'thm_organizerTable');
        $dpSuccess = $table->save($data);
        if ($dpSuccess)
        {
            $model = JModel::getInstance('mapping', 'THM_OrganizerModel');
            $mappingSuccess = $model->saveProgram($table->id);
            if ($mappingSuccess)
            {
                $dbo->transactionCommit();
                return true;
            }
        }
        $dbo->transactionRollback();
        return false;
    }
}
