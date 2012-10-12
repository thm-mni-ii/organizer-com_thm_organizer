<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        monitor model
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 *@author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
/**
 * Class performing monitor modification actions
 * 
 * @package  Admin
 * 
 * @since    2.5.4
 */
class thm_organizersModelmonitor extends JModel
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
        $table = JTable::getInstance('monitors', 'thm_organizerTable');
        return $table->save($data);
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
        $monitorIDs = JRequest::getVar('cid', array(0), 'post', 'array');
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
