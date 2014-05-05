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
require_once JPATH_COMPONENT . '/assets/helpers/lsfapi.php';

/**
 * Provides persistence handling for degree programs
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelProgram extends JModelLegacy
{
    /**
     * Attempts to delete the selected degree program entries and related mappings
     *
     * @return  boolean  True if successful, false if an error occurs.
     */
    public function delete()
    {
           $resourceIDs = JFactory::getApplication()->input->post->get('cid', array(), 'array');
        if (!empty($resourceIDs))
        {
            $this->_db->transactionStart();
            $table = JTable::getInstance('programs', 'thm_organizerTable');
            $model = JModelLegacy::getInstance('mapping', 'THM_OrganizerModel');
            foreach ($resourceIDs as $resourceID)
            {
                $mappingDeleted = $model->deleteByResourceID($resourceID, 'program');
                if (!$mappingDeleted)
                {
                    $this->_db->transactionRollback();
                    return false;
                }

                $resourceDeleted = $table->delete($resourceID);
                if (!$resourceDeleted)
                {
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
     * @return  Boolean
     */
    public function save()
    {
        $data = JRequest::getVar('jform', null, null, null, 4);
        $this->_db->transactionStart();
        $table = JTable::getInstance('programs', 'thm_organizerTable');
        $dpSuccess = $table->save($data);
        if ($dpSuccess)
        {
            $model = JModelLegacy::getInstance('mapping', 'THM_OrganizerModel');
            $mappingSuccess = $model->saveProgram($table->id);
            if ($mappingSuccess)
            {
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
     * @return  Boolean
     */
    public function save2copy()
    {
        $data = JRequest::getVar('jform', null, null, null, 4);
        if (isset($data['id']))
        {
            unset($data['id']);
        }
        $this->_db->transactionStart();
        $table = JTable::getInstance('programs', 'thm_organizerTable');
        $dpSuccess = $table->save($data);
        if ($dpSuccess)
        {
            $model = JModelLegacy::getInstance('mapping', 'THM_OrganizerModel');
            $mappingSuccess = $model->saveProgram($table->id);
            if ($mappingSuccess)
            {
                $this->_db->transactionCommit();
                return true;
            }
        }
        $this->_db->transactionRollback();
        return false;
    }
}
