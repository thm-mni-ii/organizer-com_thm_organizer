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

use Exception;
use Organizer\Helpers\Access;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class which manages stored (degree) program data.
 */
class Program extends BaseModel
{
    /**
     * Attempts to delete the selected degree program entries and related mappings
     *
     * @return boolean  True if successful, false if an error occurs.
     * @throws Exception => unauthorized access
     */
    public function delete()
    {
        if (!Access::allowDocumentAccess()) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $programIDs = OrganizerHelper::getSelectedIDs();
        if (!empty($programIDs)) {
            $this->_db->transactionStart();
            $table = $this->getTable();
            $model = new Mapping;
            foreach ($programIDs as $programID) {
                if (!Access::allowDocumentAccess('program', $programID)) {
                    $this->_db->transactionRollback();
                    throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
                }
                $mappingDeleted = $model->deleteByResourceID($programID, 'program');
                if (!$mappingDeleted) {
                    $this->_db->transactionRollback();

                    return false;
                }

                $resourceDeleted = $table->delete($programID);
                if (!$resourceDeleted) {
                    $this->_db->transactionRollback();

                    return false;
                }
            }
            $this->_db->transactionCommit();
        }

        return true;
    }

    /**
     * Method to save degree programs
     *
     * @param array $data the data to be used to create the program when called from the program helper
     *
     * @return Boolean
     * @throws Exception => invalid request / unauthorized access
     */
    public function save($data = [])
    {
        $data = empty($data) ? OrganizerHelper::getFormInput() : $data;

        if (empty($data['id'])) {
            $documentationAccess = Access::allowDocumentAccess();

            // New Programs often are introduced through schedules.
            $schedulingAccess = Access::allowSchedulingAccess();
            if (!($documentationAccess or $schedulingAccess)) {
                throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
            }
        } elseif (is_numeric($data['id'])) {
            if (!Access::allowDocumentAccess('program', $data['id'])) {
                throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
            }
        } else {
            throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
        }

        $this->_db->transactionStart();
        $table     = $this->getTable();
        $dpSuccess = $table->save($data);
        if ($dpSuccess) {
            $model = new Mapping;

            $mappingSuccess = $model->saveProgram($table->id);
            if ($mappingSuccess) {
                $this->_db->transactionCommit();

                return $table->id;
            }
        }
        $this->_db->transactionRollback();

        return false;
    }

    /**
     * Method to save existing degree programs as copies
     *
     * @param array $data the data to be used to create the program when called from the program helper
     *
     * @return Boolean
     * @throws Exception => unauthorized access
     */
    public function save2copy($data = [])
    {
        if (!Access::allowDocumentAccess()) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $data = empty($data) ? OrganizerHelper::getFormInput() : $data;
        if (isset($data['id'])) {
            unset($data['id']);
        }

        $this->_db->transactionStart();
        $table     = $this->getTable();
        $dpSuccess = $table->save($data);
        if ($dpSuccess) {
            $model = new Mapping;

            $mappingSuccess = $model->saveProgram($table->id);
            if ($mappingSuccess) {
                $this->_db->transactionCommit();

                return true;
            }
        }
        $this->_db->transactionRollback();

        return false;
    }
}
