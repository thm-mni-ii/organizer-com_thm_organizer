<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        room specific business logic and database abstraction
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('JPATH_PLATFORM') or die;
require_once JPATH_COMPONENT . '/models/modelresource.php';
/**
 * Class defining functions to be used for room resources
 * 
 * @package  Admin
 * 
 * @since    2.5.4 
 */
class thm_organizersModelroom extends thm_organizersModelresource
{
    /**
     * checks whether the rooms node is empty and iterates over its childeren
     *
     * @param   SimpleXMLNode  &$roomsnode     the rooms node to be validated
     * @param   array          &$rooms         a model of the data within the rooms node
     * @param   array          &$errors        contains strings explaining critical data inconsistancies
     * @param   array          &$warnings      contains strings explaining minor data inconsistancies
     * @param   array          &$descriptions  contains description resource data
     * 
     * @return void
     */
    protected function validate(&$roomsnode, &$rooms, &$errors, &$warnings, &$descriptions)
    {
        if (empty($roomsnode))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_SCH_RM_MISSING");
        }
        else
        {
            foreach ($roomsnode->children() as $roomnode)
            {
                $this->validateChild($roomnode, $rooms, $errors, $warnings, $descriptions);
            }
        }
    }

    /**
     * checks whether room nodes have the expected structure and required
     * information
     *
     * @param   SimpleXMLNode  &$roomnode      the room node to be validated
     * @param   array          &$rooms         a model of the data within the rooms node
     * @param   array          &$errors        contains strings explaining critical data inconsistancies
     * @param   array          &$warnings      contains strings explaining minor data inconsistancies
     * @param   array          &$descriptions  contains description resource data
     * 
     * @return void
     */
    protected function validateChild(&$roomnode, &$rooms, &$errors, &$warnings, &$descriptions)
    {
        $gpuntisID = trim((string) $roomnode[0]['id']);
        if (empty($gpuntisID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_SCH_RM_ID_MISSING"), $errors))
            {
                $errors[] = JText::_("COM_THM_ORGANIZER_SCH_RM_ID_MISSING");
            }
            return;
        }
        $roomID = str_replace('RM_', '', $gpuntisID);
        $rooms[$roomID] = array();
        $rooms[$roomID]['gpuntisID'] = $gpuntisID;
        $rooms[$roomID]['name'] = $roomID;

        $longname = trim((string) $roomnode->longname);
        if (empty($longname))
        {
            $warnings[] = JText::sprintf("COM_THM_ORGANIZER_SCH_RM_LN_MISSING", $roomID);
        }
        else
        {
            $rooms[$roomID]['longname'] = $longname;
        }
        $capacity = trim((int) $roomnode->capacity);
        if (!empty($capacity))
        {
            $rooms[$roomID]['capacity'] = $capacity;
        }
        $descriptionID = str_replace('DS_', '', trim((string) $roomnode->room_description[0]['id']));
        if (empty($descriptionID))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_SCH_RM_DESC_MISSING", $roomID);
            return;
        }
        elseif (empty($descriptions[$descriptionID]))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_SCH_RM_DESC_MISSING", $roomID, $descriptionID);
            return;
        }
        $rooms[$roomID]['description'] = $descriptionID;
    }
}
