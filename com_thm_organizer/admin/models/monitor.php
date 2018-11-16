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
 * Class which manages stored monitor data.
 */
class THM_OrganizerModelMonitor extends JModelLegacy
{
    /**
     * save
     *
     * attempts to save the monitor form data
     *
     * @return bool true on success, otherwise false
     * @throws Exception
     */
    public function save()
    {
        if (!THM_OrganizerHelperAccess::allowFMAccess()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $data = JFactory::getApplication()->input->get('jform', [], 'array');

        if (empty($data['roomID'])) {
            unset($data['roomID']);
        }

        $data['content'] = $data['content'] == '-1' ? '' : $data['content'];
        $table           = JTable::getInstance('monitors', 'thm_organizerTable');

        return $table->save($data);
    }

    /**
     * Saves the default behaviour as chosen in the monitor manager
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception
     */
    public function saveDefaultBehaviour()
    {
        if (!THM_OrganizerHelperAccess::isAdmin()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $input       = JFactory::getApplication()->input;
        $monitorID   = $input->getInt('id', 0);
        $plausibleID = ($monitorID > 0);

        if ($plausibleID) {
            $table = JTable::getInstance('monitors', 'thm_organizerTable');
            $table->load($monitorID);
            $table->set('useDefaults', $input->getInt('useDefaults', 0));

            return $table->store();
        }

        return false;
    }

    /**
     * delete
     *
     * attempts to delete the selected monitor entries
     *
     * @return boolean true on success otherwise false
     * @throws Exception
     */
    public function delete()
    {
        if (!THM_OrganizerHelperAccess::allowFMAccess()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $success    = true;
        $monitorIDs = JFactory::getApplication()->input->get('cid', [], 'array');
        $table      = JTable::getInstance('monitors', 'thm_organizerTable');

        if (isset($monitorIDs) and count($monitorIDs) > 0) {
            $dbo = JFactory::getDbo();
            $dbo->transactionStart();

            foreach ($monitorIDs as $monitorID) {
                $success = $table->delete($monitorID);

                if (!$success) {
                    $dbo->transactionRollback();

                    return $success;
                }
            }

            $dbo->transactionCommit();
        }

        return $success;
    }

    /**
     * Toggles the monitor's use of default settings
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception
     */
    public function toggle()
    {
        if (!THM_OrganizerHelperAccess::allowFMAccess()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $input     = JFactory::getApplication()->input;
        $monitorID = $input->getInt('id', 0);
        if (empty($monitorID)) {
            return false;
        }

        $value = $input->getInt('value', 1) ? 0 : 1;

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_monitors');
        $query->set("useDefaults = '$value'");
        $query->where("id = '$monitorID'");
        $this->_db->setQuery($query);
        try {
            return (bool)$this->_db->execute();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return false;
        }
    }
}
