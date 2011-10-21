<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model period
 * @description data abstraction class for periods
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('JPATH_PLATFORM') or die;
require_once JPATH_COMPONENT.'/models/modelresource.php';
jimport('joomla.application.component.model');
class thm_organizersModelperiod extends thm_organizersModelresource
{
    /**
     * validateXML
     *
     * checks whether the periods node is empty and iterates over its childeren
     *
     * @param SimpleXMLNode $periodsnode the periods node to be validated
     * @param array $periods a model of the data within the periods node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $helper not used
     */
    protected function validateXML(&$periodsnode, &$periods, &$errors, &$warnings, &$helper = null)
    {
        if(empty($periodsnode)) $errors[] = JText::_("COM_THM_ORGANIZER_TP_MISSING");
        else foreach( $periodsnode->children() as $periodnode )
                $this->validateXMLChild ($periodnode, $periods, $errors, $warnings, $helper);
    }

    /**
     * validateXMLChild
     *
     * checks whether period nodes have the expected structure and required
     * information
     *
     * @param SimpleXMLNode $periodnode the period node to be validated
     * @param array $periods a model of the data within the periods node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $helper not used
     */
    protected function validateXMLChild(&$periodnode, &$periods, &$errors, &$warnings, &$helper = null)
    {
        $id = trim((string)$periodnode[0]['id']);
        if(empty($id))
        {
            if(!in_array(JText::_("COM_THM_ORGANIZER_TP_ID_MISSING"), $errors))
                    $errors[] = JText::_("COM_THM_ORGANIZER_TP_ID_MISSING");
            return;
        }
        $day = (int)$periodnode->day;
        if(empty($day))
        {
            $error = JText::_("COM_THM_ORGANIZER_TP");
            $error .= " $id ";
            $error .= JText::_("COM_THM_ORGANIZER_TP_DAY_MISSING");
            $errors[] = $error;
            continue;
        }
        $period = (int)$periodnode->period;
        if(empty($period))
        {
            $error = JText::_("COM_THM_ORGANIZER_TP");
            $error .= " $id ";
            $error .= JText::_("COM_THM_ORGANIZER_TP_PERIOD_MISSING");
            $errors[] = $error;
            return;
        }
        $periods[$day][$period]['id'] = $id;
        $starttime = trim((string)$periodnode->starttime);
        if(empty($starttime))
        {
            $error = JText::_("COM_THM_ORGANIZER_TP");
            $error .= " $id ";
            $error .= JText::_("COM_THM_ORGANIZER_TP_STARTTIME_MISSING");
            $errors[] = $error;
        }
        else $periods[$day][$period]['starttime'] = $starttime;
        $endtime = trim((string)$periodnode->endtime);
        if(empty($endtime))
        {
            $error = JText::_("COM_THM_ORGANIZER_TP");
            $error .= " $id ";
            $error .= JText::_("COM_THM_ORGANIZER_TP_ENDTIME_MISSING");
            $errors[] = $error;
        }
        else $periods[$day][$period]['endtime'] = $endtime;
    }

    /**
     * processData
     *
     * iterates over period nodes, saves/updates periods
     *
     * @param SimpleXMLNode $periodsnode
     * @param array $periods array modeling period information in the Node
     */
    public function processData(&$periodsnode, &$periods)
    {
        foreach($periodsnode->children() as $periodnode)
            $this->processNode($periodnode, $periods);
    }

    /**
     * processNode
     *
     * saves/updates resource data
     *
     * @param SimpleXMLNode $periodnode
     * @param array $periods models the data contained in $element
     */
    protected function processNode(&$periodnode, &$periods)
    {
        $gpuntisID = trim((string)$periodnode[0]['id']);
        $day = (int)$periodnode->day;
        $period = (int)$periodnode->period;
        $starttime = trim((string)$periodnode->starttime);
        $starttime = substr($starttime, 0, 2).":".substr($starttime, 2, 2).":00";
        $endtime = trim((string)$periodnode->endtime);
        $endtime = substr($endtime, 0, 2).":".substr($endtime, 2, 2).":00";

        $periodTable = JTable::getInstance('periods', 'thm_organizerTable');
        $loadData = array('gpuntisID' => $gpuntisID);
        $data = array('gpuntisID' => $gpuntisID,
                      'day' => $day,
                      'period' => $period,
                      'starttime' => $starttime,
                      'endtime' => $endtime);
        $periodTable->load($loadData);
        $periodTable->save($data);

        if(!isset($periods[$day])) $periods[$day] = array();
        $periods[$day][$period] = array();
        $periods[$day][$period]['id'] = $periodTable->id;
        $periods[$day][$period]['gpuntisID'] = $gpuntisID;
        $periods[$day][$period]['starttime'] = $starttime;
        $periods[$day][$period]['endtime'] = $endtime;
    }
}
