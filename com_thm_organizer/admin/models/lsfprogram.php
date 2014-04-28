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
require_once JPATH_COMPONENT . '/assets/helpers/lsfapi.php';

/**
 * Provides persistence handling for degree programs
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelLSFProgram extends JModelLegacy
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

        $client = new THM_OrganizerLSFClient;
        $program = $client->getModules($lsfData['program'], $lsfData['degree'], $lsfData['version']);
        if (empty($program))
        {
            return false;
        }

        if (!empty($program->gruppe))
        {
            $mappingModel = JModelLegacy::getInstance('mapping', 'THM_OrganizerModel');
            $programMappingExists = $this->processProgramMapping($programID, $mappingModel);
            if (!$programMappingExists)
            {
                return false;
            }

            $childrenImported = $this->processChildNodes($program);
            if (!$childrenImported)
            {
                return false;
            }

            $mappingsAdded = $mappingModel->addLSFMappings($programID, $program);
            if (!$mappingsAdded)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Processes the child nodes of the program root node
     *
     * @param   object  &$program  the simplexml object object containing program information
     *
     * @return  boolean  true on success, otherwise false
     */
    private function processChildNodes(&$program)
    {
        $lsfSubjectModel = JModelLegacy::getInstance('LSFSubject', 'THM_OrganizerModel');
        $lsfPoolModel = JModelLegacy::getInstance('LSFPool', 'THM_OrganizerModel');
        foreach ($program->gruppe as $resource)
        {
            $stubProcessed = isset($resource->modulliste->modul)?
                $lsfPoolModel->processStub($resource) : $lsfSubjectModel->processStub($resource);
            if (!$stubProcessed)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks for a program mapping, creating one if non-existant
     *
     * @param   int     $programID      the id of the program
     * @param   object  &$mappingModel  the mapping model
     *
     * @return  boolean  true on existant/created mapping, otherwise false
     */
    private function processProgramMapping($programID, &$mappingModel)
    {
        $mappingExists = $mappingModel->checkForMapping($programID, 'program');
        if (empty($mappingExists))
        {
            $mappingCreated = $mappingModel->saveProgram($programID);
            if (empty($mappingCreated))
            {
                return false;
            }
        }
        return true;
    }
}
