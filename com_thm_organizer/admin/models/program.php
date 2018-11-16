<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/lsf.php';

/**
 * Class which manages stored (degree) program data.
 */
class THM_OrganizerModelProgram extends JModelLegacy
{
    /**
     * Attempts to delete the selected degree program entries and related mappings
     *
     * @return boolean  True if successful, false if an error occurs.
     * @throws Exception
     */
    public function delete()
    {
        if (!THM_OrganizerHelperComponent::allowDocumentAccess()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $programIDs = JFactory::getApplication()->input->get('cid', [], 'array');
        if (!empty($programIDs)) {
            $this->_db->transactionStart();
            $table = JTable::getInstance('programs', 'thm_organizerTable');
            $model = JModelLegacy::getInstance('mapping', 'THM_OrganizerModel');
            foreach ($programIDs as $programID) {
                if (!THM_OrganizerHelperComponent::allowDocumentAccess('program', $programID)) {
                    $this->_db->transactionRollback();
                    throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
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
     * @throws Exception
     */
    public function save($data = [])
    {
        $data = empty($data) ? JFactory::getApplication()->input->get('jform', [], 'array') : $data;

        if (empty($data['id'])) {
            $documentationAccess = THM_OrganizerHelperComponent::allowDocumentAccess();

            // New Programs often are introduced through schedules.
            $schedulingAccess = THM_OrganizerHelperComponent::allowSchedulingAccess();
            if (!($documentationAccess or $schedulingAccess)) {
                throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
            }
        } elseif (is_numeric($data['id'])) {
            if (!THM_OrganizerHelperComponent::allowDocumentAccess('program', $data['id'])) {
                throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
            }
        } else {
            throw new Exception(JText::_('COM_THM_ORGANIZER_400'), 400);
        }

        $this->_db->transactionStart();
        $table     = JTable::getInstance('programs', 'thm_organizerTable');
        $dpSuccess = $table->save($data);
        if ($dpSuccess) {
            $model          = JModelLegacy::getInstance('mapping', 'THM_OrganizerModel');
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
     * @return Boolean
     * @throws Exception
     */
    public function save2copy()
    {
        if (!THM_OrganizerHelperComponent::allowDocumentAccess()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $data = JFactory::getApplication()->input->get('jform', [], 'array');
        if (isset($data['id'])) {
            unset($data['id']);
        }

        $this->_db->transactionStart();
        $table     = JTable::getInstance('programs', 'thm_organizerTable');
        $dpSuccess = $table->save($data);
        if ($dpSuccess) {
            $model          = JModelLegacy::getInstance('mapping', 'THM_OrganizerModel');
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
