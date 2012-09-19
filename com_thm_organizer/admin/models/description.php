<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        description specific business logic and database abstraction
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
 * Class defining functions to be used for resource descriptions
 * 
 * @package  Admin
 * 
 * @since    2.5.4 
 */
class thm_organizersModeldescription extends thm_organizersModelresource
{
    /**
     * checks whether the descriptions node is empty and iterates over its childeren
     *
     * @param   SimpleXMLNode  &$descriptionsnode  the descriptions node to be validated
     * @param   array          &$descriptions      a model of the data within the descriptions node
     * @param   array          &$errors            contains strings explaining critical data inconsistancies
     * 
     * @return void
     */
    protected function validate(&$descriptionsnode, &$descriptions, &$errors)
    {
        if (empty($descriptionsnode))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_DSM_MISSING");
        }
        else
        {
            foreach ($descriptionsnode->children() as $descriptionnode)
            {
                $this->validateChild($descriptionnode, $descriptions, $errors);
            }
        }
    }

    /**
     * checks whether department nodes have the expected structure and required
     * information
     *
     * @param   SimpleXMLNode  &$descriptionnode  the description node to be validated
     * @param   array          &$descriptions     a model of the data within the descriptions node
     * @param   array          &$errors           contains strings explaining critical data inconsistancies
     * 
     * @return void
     */
    protected function validateChild(&$descriptionnode, &$descriptions, &$errors)
    {
        $gpuntisID = trim((string) $descriptionnode[0]['id']);
        if (empty($gpuntisID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_DSM_ID_MISSING."), $errors))
            {
                    $errors[] = JText::_("COM_THM_ORGANIZER_DSM_ID_MISSING.");
            }
            return;
        }
        $descriptionID = str_replace('DS_', '', $gpuntisID);
        $descriptions[$descriptionID] = array();
        $descriptions[$descriptionID]['gpuntisID'] = $gpuntisID;

        $longname = trim((string) $descriptionnode->longname);
        if (empty($longname))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_DSM_DESC_MISSING", $descriptionID);
            return;
        }
        else
        {
            $descriptions[$descriptionID]['name'] = $longname;
        }
    }
}
