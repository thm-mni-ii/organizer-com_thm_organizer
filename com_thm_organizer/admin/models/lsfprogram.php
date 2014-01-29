<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelLSFProgram
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT . DS . 'assets' . DS . 'helpers' . DS . 'lsfapi.php';

/**
 * Provides persistence handling for degree programs
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelLSFProgram extends JModel
{
    /**
     * Retrieves program information relevant for soap queries to the LSF system.
     *
     * @param   int  $programID  the id of the degree program
     *
     * @return  array  empty if the program could not be found
     */
    private function getLSFQueryData($programID)
    {
        $lsfDataQuery = $this->_db->getQuery(true);
        $lsfDataQuery->select("lsfFieldID AS program, lsfDegree AS degree, version");
        $lsfDataQuery->from('#__thm_organizer_programs AS p');
        $lsfDataQuery->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
        $lsfDataQuery->where("p.id = '$programID'");
        $this->_db->setQuery((string) $lsfDataQuery);
        $lsfData = $this->_db->loadAssoc();
        return empty($lsfData)? array() : $lsfData;
    }

    /**
     * Method to import data associated with degree programs from LSF
     *
     * @return  bool  true on success, otherwise false
     */
    public function importBatch()
    {
        $this->_db->transactionStart();
        $programIDs = JRequest::getVar('cid', array(), 'post', 'array');
        foreach ($programIDs as $programID)
        {
            $programImported = $this->importSingle($programID);
            if (!$programImported)
            {
                $this->_db->transactionRollback();
                return false;
            }
        }
        $this->_db->transactionCommit();
        return true;
    }

    /**
     * Method to import data associated with a degree program from LSF
     *
     * @param   int  $programID  the id of the program to be imported
     *
     * @return  boolean  true on success, otherwise false
     */
    public function importSingle($programID)
    {
        $lsfData = $this->getLSFQueryData($programID);
        if (empty($lsfData))
        {
            JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_LSFDATA_MISSING', 'error');
            return false;
        }

        $mappingModel = JModel::getInstance('mapping', 'THM_OrganizerModel');
        $mappingExists = $mappingModel->checkForMapping($programID, 'program');
        if (empty($mappingExists))
        {
            $mappingCreated = $mappingModel->saveProgram($programID);
            if (empty($mappingCreated))
            {
                return false;
            }
        }
 
        $client = new THM_OrganizerLSFClient;
        $lsfProgram = $client->getModules($lsfData['program'], $lsfData['degree'], $lsfData['version']);
        if (empty($lsfProgram))
        {
            return false;
        }
 
        if (isset($lsfProgram->gruppe) AND count($lsfProgram->gruppe))
        {
            // Iterate over the entire over each course-group of the returned xml structure
            $lsfSubjectModel = JModel::getInstance('LSFSubject', 'THM_OrganizerModel');
            $lsfPoolModel = JModel::getInstance('LSFPool', 'THM_OrganizerModel');
            foreach ($lsfProgram->gruppe as $resource)
            {
                $stubProcessed = isset($resource->modulliste->modul)?
                    $lsfPoolModel->processStub($resource) : $lsfSubjectModel->processStub($resource);
                if (!$stubProcessed)
                {
                    return false;
                }
            }
 
            $mappingsAdded = $mappingModel->addLSFMappings($programID, $lsfProgram);
            if (!$mappingsAdded)
            {
                return false;
            }
        }
        return true;
    }

}
