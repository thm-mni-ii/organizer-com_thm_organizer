<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model monitor
 * @description persistance file for monitors
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
class thm_organizersModelmonitor extends JModel
{

    public function save()
    {
        $monitorID = JRequest::getVar('monitorID');
        $roomID = JRequest::getVar('room', '');
        $ip = JRequest::getVar('ip', '');

        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        if(empty($monitorID))
        {
            $statement = "#__thm_organizer_monitors ";
            $statement .= "(roomID, ip) ";
            $statement .= "VALUES ";
            $statement .= "( '$roomID', '$ip' ) ";
            $query->insert($statement);
        }
        else
        {
            $query->update("#__thm_organizer_monitors");
            $query->set("roomID = '$roomID', ip = '$ip'");
            $query->where("monitorID = '$monitorID'");
        }
        $dbo->setQuery((string)$query );
        $dbo->query();
        return ($dbo->getErrorNum())? false : true;
    }

    public function delete()
    {
        $monitorIDs = JRequest::getVar( 'cid', array(0), 'post', 'array' );
        if(count($monitorIDs) > 0)
        {
            $dbo = & JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $query->delete("#__thm_organizer_monitors");
            $monitorIDs = "'".implode("', '", $monitorIDs)."'";
            $query->where("monitorID IN ( $monitorIDs )");
            $dbo->setQuery((string)$query);
            $result = $dbo->query();
            if ($dbo->getErrorNum()) return false;
            else return true;
        }
        return true;
    }
}
