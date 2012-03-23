<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model description
 * @description data abstraction class for descriptions
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('JPATH_PLATFORM') or die;
require_once JPATH_COMPONENT.'/models/modelresource.php';
jimport('joomla.application.component.model');
class thm_organizersModeldescription extends thm_organizersModelresource
{
    /**
     * validateXML
     *
     * checks whether the descriptions node is empty and iterates over its childeren
     *
     * @param SimpleXMLNode $descriptionsnode the descriptions node to be validated
     * @param array $descriptions a model of the data within the descriptions node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $helper not used
     */
    protected function validateXML(&$descriptionsnode, &$descriptions, &$errors, &$warnings, &$helper = null)
    {
        if(empty($descriptionsnode)) $errors[] = JText::_("COM_THM_ORGANIZER_DSM_MISSING");
        else foreach( $descriptionsnode->children() as $descriptionnode )
                $this->validateXMLChild ($descriptionnode, $descriptions, $errors, $warnings, $helper);
    }

    /**
     * validateXMLChild
     *
     * checks whether department nodes have the expected structure and required
     * information
     *
     * @param SimpleXMLNode $descriptionnode the description node to be validated
     * @param array $descriptions a model of the data within the descriptions node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $helper not used
     */
    protected function validateXMLChild(&$descriptionnode, &$descriptions, &$errors, &$warnings, &$helper = null)
    {
        $id = trim((string)$descriptionnode[0]['id']);
        if(empty($id))
        {
            if(!in_array(JText::_("COM_THM_ORGANIZER_DSM_ID_MISSING."), $errors))
                    $errors[] = JText::_("COM_THM_ORGANIZER_DSM_ID_MISSING.");
            return;
        }
        $longname = trim((string)$descriptionnode->longname);
        if(empty($longname))
        {
            $error = JText::_("COM_THM_ORGANIZER_DS");
            $error .= " $id ";
            $error .= JText::_("COM_THM_ORGANIZER_DSM_DESC_MISSING");
            $errors[] = $error;
            return;
        }
        else
        {
            $details = explode(",", $longname);
            if(empty($details) or count($details) == 0)
            {
                $error = JText::_("COM_THM_ORGANIZER_DS");
                $error .= " $id ";
                $error .= JText::_("COM_THM_ORGANIZER_DSM_DESC_LACKING");
                $errors[] = $error;
                return;
            }
            $descriptions[$id]['category'] = $details[0];
            if(isset($details[1]))$descriptions[$id]['description'] = $details[1];
        }
    }

    /**
     * processData
     *
     * iterates over description nodes, saves/updates description data
     *
     * @param SimpleXMLNode $descriptionsnode
     * @param array $descriptions models the data contained in $descriptionsnode
     * @param int $semesterID not used
     * @param array $helper not used
     */
    public function processData(&$descriptionsnode, &$descriptions, $semesterID = 0, &$helper = null)
    {
        foreach($descriptionsnode->children() as $descriptionnode)
            $this->processNode($descriptionnode, $descriptions);
    }

    /**
     * processNode
     *
     * saves/updates description data
     *
     * @param SimpleXMLNode $descriptionnode
     * @param array $data models the data contained in $descriptionsnode
     * @param int $semesterID not used
     * @param array $helper not used
     */
    protected function processNode(&$descriptionnode, &$descriptions, $semesterID = 0, &$helper = null)
    {
        $gpuntisID = trim((string)$descriptionnode[0]['id']);
        $details = explode(', ', trim((string)$descriptionnode->longname));
        $category = $details[0];
        $description = (isset($details[1]))? $details[1] : '';

        $description = JTable::getInstance('descriptions', 'thm_organizerTable');
        $loadData = array('gpuntisID' => $gpuntisID);
        $data = array('gpuntisID' => $gpuntisID,
                      'category' => $category,
                      'description' => $description);
        $description->load($loadData);
        $description->save($data);

        $descriptions[$gpuntisID] = array();
        $descriptions[$gpuntisID]['id'] = $description->id;
        $descriptions[$gpuntisID]['category'] = $details[0];
        $descriptions[$gpuntisID]['description'] = (isset($details[1]))? $details[1] : '';
    }
}
