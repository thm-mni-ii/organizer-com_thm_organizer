<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        period specific business logic and database abstraction
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('JPATH_PLATFORM') or die;
require_once JPATH_COMPONENT . '/models/resource.php';
/**
 * Class defining functions to be used for period resources
 * 
 * @package  Admin
 * 
 * @since    2.5.4 
 */
class thm_organizersModelperiod extends thm_organizersModelresource
{
    /**
     * validates a set of periods
     *
     * @param   SimpleXMLNode  &$periodsnode  a node containing of resource nodes
     * @param   array          &$periods      models the data contained in the document
     * @param   array          &$errors       contains strings explaining critical data inconsistancies
     * 
     * @return void
     */
    protected function validate(&$periodsnode, &$periods, &$errors)
    {
        if (empty($periodsnode))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_TP_MISSING");
        }
        else
        {
            foreach ($periodsnode->children() as $periodnode)
            {
                $this->validateChild($periodnode, $periods, $errors);
            }
        }
    }

    /**
     * validates an individual period
     *
     * @param   SimpleXMLNode  &$periodnode  a resource node
     * @param   array          &$periods     models the data contained in the periods
     * @param   array          &$errors      contains strings explaining critical data inconsistancies
     * 
     * @return void
     */
    protected function validateChild(&$periodnode, &$periods, &$errors)
    {
        $gpuntisID = trim((string) $periodnode[0]['id']);
        $periodID = str_replace('TP_', '', $gpuntisID);
        if (empty($gpuntisID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_TP_ID_MISSING"), $errors))
            {
                $errors[] = JText::_("COM_THM_ORGANIZER_TP_ID_MISSING");
            }
            return;
        }
        $periods[$periodID] = array();
        $periods[$periodID]['gpuntisID'] = $gpuntisID;

        $day = (int) $periodnode->day;
        if (empty($day))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_TP_DAY_MISSING", $id);
            continue;
        }
        else
        {
            $periods[$periodID]['day'] = $day;
        }

        $period = (int) $periodnode->period;
        if (empty($period))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_TP_PERIOD_MISSING", $id);
            return;
        }
        else
        {
            $periods[$periodID]['period'] = $period;
        }

        $starttime = trim((string) $periodnode->starttime);
        if (empty($starttime))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_TP_STARTTIME_MISSING", $id);
        }
        else
        {
            $periods[$periodID]['starttime'] = $starttime;
        }

        $endtime = trim((string) $periodnode->endtime);
        if (empty($endtime))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_TP_ENDTIME_MISSING", $id);
        }
        else
        {
            $periods[$periodID]['endtime'] = $endtime;
        }
    }
}
