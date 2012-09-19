<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        department specific business logic and database abstraction
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
 * Class defining functions to be used for degree resources
 * 
 * @package  Admin
 * 
 * @since    2.5.4 
 */
class thm_organizersModeldepartment extends thm_organizersModelresource
{
    /**
     * checks whether the departments node is empty and iterates over its childeren
     *
     * @param   SimpleXMLNode  &$departmentsnode  the departments node to be validated
     * @param   array          &$degrees          a model of the data within the departments node
     * @param   array          &$errors           contains strings explaining critical data inconsistancies
     * 
     * @return void
     */
    protected function validate(&$departmentsnode, &$degrees, &$errors)
    {
        if (empty($departmentsnode))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_DP_MISSING");
        }
        else
        {
            foreach ( $departmentsnode->children() as $departmentnode )
            {
                $this->validateChild($departmentnode, $degrees, $errors);
            }
        }
    }

    /**
     * checks whether department nodes have the expected structure and required
     * information
     *
     * @param   SimpleXMLNode  &$departmentnode  the department node to be validated
     * @param   array          &$degrees         a model of the data within $departmentnode
     * @param   array          &$errors          contains strings explaining critical data inconsistancies
     * 
     * @return void
     */
    protected function validateChild(&$departmentnode, &$degrees, &$errors)
    {
        $gpuntisID = trim((string) $departmentnode[0]['id']);
        if (empty($gpuntisID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_DP_ID_MISSING"), $errors))
            {
                $errors[] = JText::_("COM_THM_ORGANIZER_DP_ID_MISSING");
            }
            return;
        }
        $degreeID = str_replace('DP_', '', $gpuntisID);
        $degrees[$degreeID] = array();
        $degrees[$degreeID]['gpuntisID'] = $gpuntisID;

        $degreeName = (string) $departmentnode->longname;
        if (!isset($degreeName))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_DP_LONGNAME_MISSING", $degreeID);
            return;
        }
        $degrees[$degreeID]['name'] = $degreeName;
    }
}
