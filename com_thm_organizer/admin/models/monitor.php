<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model monitor
 * @description persistance file for monitors
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
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
        $data['display'] = JRequest::getInt('display');
        if(JRequest::getInt('monitorID')) $data['monitorID'] = JRequest::getInt('monitorID');
        $table = JTable::getInstance('monitors', 'thm_organizerTable');
        return $table->save($data);
    }

    /**
     * delete
     *
     * attempts to delete the selected monitor entries
     *
     * @return
     */
    public function delete()
    {
        $dbo = JFactory::getDbo();
        $monitorIDs = JRequest::getVar( 'cid', array(0), 'post', 'array' );
        $table = JTable::getInstance('monitors', 'thm_organizerTable');
        if(count($monitorIDs) > 0)
            foreach($monitorIDs as $monitorID)$table->delete($monitorID);
        return ($dbo->getErrorNum())? false : true;
    }
}
