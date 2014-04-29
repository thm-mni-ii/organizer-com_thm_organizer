<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        monitor model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Class performing monitor modification actions
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelmonitor extends JModelLegacy
{
    /**
     * save
     *
     * attempts to save the monitor form data
     *
     * @return bool true on success, otherwise false
     */
    public function save()
    {
        $data = JRequest::getVar('jform', null, null, null, 4);
        $data['content'] = $data['content'] == '-1'? '' : $data['content'];
        $table = JTable::getInstance('monitors', 'thm_organizerTable');
        return $table->save($data);
    }

    /**
     * Saves the default behaviour as chosen in the monitor manager
     * 
     * @return  boolean  true on success, otherwise false
     */
    public function saveDefaultBehaviour()
    {
        $input = JFactory::getApplication()->input;
        $monitorID = $input->getInt('id');
        $plausibleID = (!empty($monitorID) AND is_numeric($monitorID));
        if ($plausibleID)
        {
            $useDefaults = $input->getInt('useDefaults');
            $table = JTable::getInstance('monitors', 'thm_organizerTable');
            $table->load($monitorID);
            $table->useDefaults = $useDefaults? '0' : '1';
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
     */
    public function delete()
    {
        $success = true;
        $monitorIDs = JFactory::getApplication()->input->post->get('cid', array(0), 'array');
        $table = JTable::getInstance('monitors', 'thm_organizerTable');
        if (isset($monitorIDs) and count($monitorIDs) > 0)
        {
            $dbo = JFactory::getDbo();
            $dbo->transactionStart();
            foreach ($monitorIDs as $monitorID)
            {
                $success = $table->delete($monitorID);
                if (!$success)
                {
                    $dbo->transactionRollback();
                    return $success;
                }
            }
            $dbo->transactionCommit();
        }
        return $success;
    }
}
