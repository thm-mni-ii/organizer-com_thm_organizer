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
use Organizer\Helpers\Input;

/**
 * Class which manages stored (subject) pool data.
 */
class Pool extends BaseModel
{
    /**
     * Attempts to delete the selected subject pool entries and related mappings
     *
     * @return boolean true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function delete()
    {
        if (!Access::allowDocumentAccess()) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $poolIDs = Input::getSelectedIDs();
        if (!empty($poolIDs)) {
            $this->_db->transactionStart();
            foreach ($poolIDs as $poolID) {
                if (!Access::allowDocumentAccess('pool', $poolID)) {
                    $this->_db->transactionRollback();
                    throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
                }

                $deleted = $this->deleteSingle($poolID);

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
     * Removes a single pool and mappings. No access checks because of the contexts in which it is called.
     *
     * @param int $poolID the pool id
     *
     * @return boolean  true on success, otherwise false
     */
    public function deleteSingle($poolID)
    {
        $model           = new Mapping;
        $mappingsDeleted = $model->deleteByResourceID($poolID, 'pool');

        if (!$mappingsDeleted) {
            return false;
        }

        $table       = $this->getTable();
        $poolDeleted = $table->delete($poolID);

        if (!$poolDeleted) {
            return false;
        }

        return true;
    }

    /**
     * Attempts to save the resource.
     *
     * @param array $data form data which has been preprocessed by inheriting classes.
     *
     * @return mixed int id of the resource on success, otherwise boolean false
     * @throws Exception => unauthorized access
     */
    public function save($data = [])
    {
        $data = empty($data) ? Input::getForm() : $data;

        if (empty($data['id'])) {
            if (!Access::allowDocumentAccess()) {
                throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
            }
        } elseif (is_numeric($data['id'])) {
            if (!Access::allowDocumentAccess('pool', $data['id'])) {
                throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
            }
        } else {
            throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
        }

        if (empty($data['fieldID'])) {
            unset($data['fieldID']);
        }

        $table = $this->getTable();
        $this->_db->transactionStart();

        $success = $table->save($data);

        if (!$success or empty($table->id)) {
            $this->_db->transactionRollback();

            return false;
        }

        $mappingsIrrelevant = (empty($data['programID']) or empty($data['parentID']));

        // Successfully inserted a new pool
        if ($mappingsIrrelevant) {
            $this->_db->transactionCommit();

            return $table->id;
        } // Process mapping information
        else {
            $model      = new Mapping;
            $data['id'] = $table->id;

            // No mappings desired
            if (empty($data['parentID'])) {
                $mappingsDeleted = $model->deleteByResourceID($table->id, 'pool');
                if ($mappingsDeleted) {
                    $this->_db->transactionCommit();

                    return $table->id;
                } else {
                    $this->_db->transactionRollback();

                    return false;
                }
            } else {
                $mappingSaved = $model->savePool($data);
                if ($mappingSaved) {
                    $this->_db->transactionCommit();

                    return $table->id;
                } else {
                    $this->_db->transactionRollback();

                    return false;
                }
            }
        }
    }
}
