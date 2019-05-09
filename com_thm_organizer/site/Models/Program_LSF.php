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
use Organizer\Helpers\LSF;
use Organizer\Helpers\Mappings;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class used to import lsf program data.
 */
class Program_LSF extends BaseModel
{
    /**
     * Retrieves program information relevant for soap queries to the LSF system.
     *
     * @param int $programID the id of the degree program
     *
     * @return array  empty if the program could not be found
     */
    private function getSavedProgramData($programID)
    {
        $query = $this->_db->getQuery(true);
        $query->select('p.code AS program, d.code AS degree, version, departmentID');
        $query->from('#__thm_organizer_programs AS p');
        $query->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
        $query->where("p.id = '$programID'");
        $this->_db->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssoc', []);
    }

    /**
     * Retrieves the distinct subject ids associated with the program
     *
     * @param int $programID the program's id
     *
     * @return array|mixed the subject ids
     */
    private function getSubjectIDs($programID)
    {
        $borders = Mappings::getBoundaries('program', $programID);

        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT subjectID')
            ->from('#__thm_organizer_mappings')
            ->where('subjectID IS NOT NULL')
            ->where("lft > '{$borders[0]['lft']}'")
            ->where("rgt < '{$borders[0]['rgt']}'");
        $this->_db->setQuery($query);

        return OrganizerHelper::executeQuery('loadColumn', []);
    }

    /**
     * Method to import data associated with degree programs from LSF
     *
     * @return bool  true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function importBatch()
    {
        $programIDs = OrganizerHelper::getSelectedIDs();

        $this->_db->transactionStart();
        foreach ($programIDs as $programID) {

            if (!Access::allowDocumentAccess('program', $programID)) {
                $this->_db->transactionRollback();
                throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
            }

            $programImported = $this->importSingle($programID);
            if (!$programImported) {
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
     * @param int $programID the id of the program to be imported
     *
     * @return boolean  true on success, otherwise false
     */
    private function importSingle($programID)
    {
        $programData = $this->getSavedProgramData($programID);
        if (empty($programData)) {
            OrganizerHelper::message('THM_ORGANIZER_MESSAGE_LSFDATA_MISSING', 'error');

            return false;
        }

        $client  = new \LSF;
        $program = $client->getModules($programData['program'], $programData['degree'], $programData['version']);
        if (empty($program)) {
            return false;
        }

        if (!empty($program->gruppe)) {
            $mappingModel = new Mapping;

            $programMappingExists = $this->processProgramMapping($programID, $mappingModel);
            if (!$programMappingExists) {
                return false;
            }

            $childrenImported = $this->processChildNodes($program, $programData['departmentID']);
            if (!$childrenImported) {
                return false;
            }

            $mappingsAdded = $mappingModel->addLSFMappings($programID, $program);
            if (!$mappingsAdded) {
                return false;
            }

            $subjectIDs = $this->getSubjectIDs($programID);

            foreach ($subjectIDs as $subjectID) {
                $subjectModel = new Subject_LSF;

                $dependenciesResolved = $subjectModel->resolveDependencies($subjectID);
                if (!$dependenciesResolved) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Processes the child nodes of the program root node
     *
     * @param object &$program      the simplexml object object containing program information
     * @param int     $departmentID the id of the department to which this data belongs
     *
     * @return boolean  true on success, otherwise false
     */
    private function processChildNodes(&$program, $departmentID)
    {
        $lsfSubjectModel = new Subject_LSF;
        $lsfPoolModel    = new Pool_LSF;

        foreach ($program->gruppe as $resource) {
            $type    = LSF::determineType($resource);
            $success = true;

            if ($type == 'subject') {
                $success = $lsfSubjectModel->processStub($resource, $departmentID);
            } elseif ($type == 'pool') {
                $success = $lsfPoolModel->processStub($resource, $departmentID);
            }

            // Malformed xml, invalid/incomplete data, database errors
            if (!$success) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks for a program mapping, creating one if non-existant
     *
     * @param int     $programID    the id of the program
     * @param object &$mappingModel the mapping model
     *
     * @return boolean  true on existant/created mapping, otherwise false
     */
    private function processProgramMapping($programID, &$mappingModel)
    {
        $mappingExists = $mappingModel->checkForMapping($programID, 'program');
        if (empty($mappingExists)) {
            $mappingCreated = $mappingModel->saveProgram($programID);
            if (empty($mappingCreated)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Method to update subject data associated with degree programs from LSF
     *
     * @return bool  true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function updateBatch()
    {
        $programIDs = OrganizerHelper::getSelectedIDs();

        if (empty($programIDs)) {
            return false;
        }

        $this->_db->transactionStart();
        $subjectModel = new Subject_LSF;

        foreach ($programIDs as $programID) {

            if (!Access::allowDocumentAccess('program', $programID)) {
                $this->_db->transactionRollback();
                throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
            }

            $subjectIDs = $this->getSubjectIDs($programID);

            if (empty($subjectIDs)) {
                continue;
            }

            foreach ($subjectIDs as $subjectID) {
                $success = $subjectModel->importSingle($subjectID);

                if (!$success) {
                    $this->_db->transactionRollback();

                    return false;
                }
            }
        }

        $this->_db->transactionCommit();

        return true;
    }
}
