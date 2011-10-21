<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model room
 * @description data abstraction class for rooms
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('JPATH_PLATFORM') or die;
require_once JPATH_COMPONENT.'/models/modelresource.php';
jimport('joomla.application.component.model');
class thm_organizersModelroom extends thm_organizersModelresource
{
    /**
     * validateXML
     *
     * checks whether the rooms node is empty and iterates over its childeren
     *
     * @param SimpleXMLNode $roomsnode the rooms node to be validated
     * @param array $rooms a model of the data within the rooms node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $descriptions contains description resource data
     */
    protected function validateXML(&$roomsnode, &$rooms, &$errors, &$warnings, &$descriptions)
    {
        if(empty($roomsnode)) $errors[] = JText::_("COM_THM_ORGANIZER_SCH_RM_MISSING");
        else foreach( $roomsnode->children() as $roomnode )
                $this->validateXMLChild ($roomnode, $rooms, $errors, $warnings, $descriptions);
    }

    /**
     * validateXMLChild
     *
     * checks whether room nodes have the expected structure and required
     * information
     *
     * @param SimpleXMLNode $roomnode the room node to be validated
     * @param array $rooms a model of the data within the rooms node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $descriptions contains description resource data
     */
    protected function validateXMLChild(&$roomnode, &$rooms, &$errors, &$warnings, &$descriptions)
    {
        $id = trim((string)$roomnode[0]['id']);
        if(empty($id))
        {
            if(!in_array(JText::_("COM_THM_ORGANIZER_SCH_RM_ID_MISSING"), $errors))
                $errors[] = JText::_("COM_THM_ORGANIZER_SCH_RM_ID_MISSING");
            return;
        }
        $name = str_replace("RM_", "", $id);
        $longname = trim((string)$roomnode->longname);
        if(empty($longname))
        {
            $error = JText::_("COM_THM_ORGANIZER_SCH_RM");
            $error .= " $name ($id) ";
            $error .= JText::_("COM_THM_ORGANIZER_SCH_RM_LN_MISSING");
            $errors[] = $error;
        }
        else $rooms[$id]['longname'] = $longname;
        $capacity = trim((int)$roomnode->capacity);
        if(!empty($capacity)) $rooms[$id]['capacity'] = $capacity;
        $descid = trim($roomnode->room_description[0]['id']);
        if(empty($descid))
        {
            $error = JText::_("COM_THM_ORGANIZER_SCH_RM");
            $error .= " $name ($id) ";
            $error .= JText::_("COM_THM_ORGANIZER_SCH_RM_DESC_MISSING");
            $errors[] = $error;
        }
        else if(empty($descriptions[$descid]))
        {
            $error = JText::_("COM_THM_ORGANIZER_SCH_RM")." $name ($id) ";
            $error .= JText::_("COM_THM_ORGANIZER_SCH_RM_DESC_LACKING")." $descid.";
            $errors[] = $error;
        }
        else $rooms[$id]['description'] = $descriptions[$descid];
    }

    /**
     * processData
     *
     * iterates over room nodes, saves/updates room data
     *
     * @param SimpleXMLNode $roomsnode
     * @param array $rooms models the data contained in $roomsnode
     * @param int $semesterID not used
     * @param array $descriptions contains room description data
     */
    public function processData(&$roomsnode, &$rooms, $semesterID, &$descriptions)
    {
        foreach($roomsnode->children() as $roomnode)
            $this->processNode($roomnode, $rooms, $semesterID , $descriptions);
    }

    /**
     * processNode
     *
     * saves/updates room data
     *
     * @param SimpleXMLNode $roomnode
     * @param array $rooms models the data contained in $roomsnode
     * @param int $semesterID not used
     * @param array $descriptions contains room description data
     */
    protected function processNode(&$roomnode, &$rooms, $semesterID, &$descriptions)
    {
        $gpuntisID = trim((string)$roomnode[0]['id']);
        $name = str_replace("RM_","",$gpuntisID);
        $longname = trim((string)$roomnode->longname);
        $capacity = ((int)$roomnode->capacity)? (int)$roomnode->capacity : 0;
        $descriptionID = trim((string)$roomnode->room_description[0]['id']);
        $descriptionID = $descriptions[$descriptionID]['id'];

        $room = JTable::getInstance('rooms', 'thm_organizerTable');
        $loadData = array('gpuntisID' => $gpuntisID);
        $data = array('gpuntisID' => $gpuntisID,
                      'name' => $name,
                      'longname' => $longname,
                      'capacity' => $capacity,
                      'descriptionID' => $descriptionID);
        $room->load($loadData);
        $room->save($data);

        $rooms[$gpuntisID] = array();
        $rooms[$gpuntisID]['id'] = $room->id;
        $rooms[$gpuntisID]['name'] = $name;
        $rooms[$gpuntisID]['longname'] = $longname;
        $rooms[$gpuntisID]['capacity'] = $capacity;
        $rooms[$gpuntisID]['descriptionID'] = $descriptionID;
    }
}

