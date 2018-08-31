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

/**
 * Class which manages stored department data.
 */
class THM_OrganizerModelDepartment extends JModelLegacy
{
    /**
     * Attempts to save the form data
     *
     * @return mixed  int department id on success, otherwise false
     * @throws Exception
     */
    public function save()
    {
        if (!THM_OrganizerHelperComponent::isAdmin()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $data = JFactory::getApplication()->input->get('jform', [], 'array');

        $this->_db->transactionStart();
        $department = JTable::getInstance('departments', 'thm_organizerTable');
        try {
            $deptSuccess = $department->save($data);
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            $this->_db->transactionRollback();

            return false;
        }

        if (!$deptSuccess) {
            $this->_db->transactionRollback();

            return false;
        }

        return $department->id;
    }

    /**
     * Attempts to save altered form data as a new entry
     *
     * @return mixed  int department id on success, otherwise false
     * @throws Exception
     */
    public function save2copy()
    {
        if (!THM_OrganizerHelperComponent::isAdmin()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $data = JFactory::getApplication()->input->get('jform', [], 'array');
        if (isset($data['id'])) {
            unset($data['id']);
        }

        $this->_db->transactionStart();
        $department = JTable::getInstance('departments', 'thm_organizerTable');
        try {
            $deptSuccess = $department->save($data);
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            $this->_db->transactionRollback();

            return false;
        }

        if (!$deptSuccess) {
            $this->_db->transactionRollback();

            return false;
        }

        return $department->id;
    }

    /**
     * Removes departments entries from the database
     *
     * @return boolean true on success, otherwise false
     */
    public function delete()
    {
        if (!THM_OrganizerHelperComponent::isAdmin()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        return THM_OrganizerHelperComponent::delete('departments');
    }
}
