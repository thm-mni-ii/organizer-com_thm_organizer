<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        module specific business logic and database abstraction
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
 * Class defining functions to be used for module resources
 * 
 * @package  Admin
 * 
 * @since    2.5.4 
 */
class thm_organizersModelmodule extends thm_organizersModelresource
{
    /**
     * checks whether the classes node is empty and iterates over its childeren
     *
     * @param   SimpleXMLNode  &$classesnode  the classes node to be validated
     * @param   array          &$modules      a model of the data within the classes node
     * @param   array          &$errors       contains strings explaining critical data inconsistancies
     * @param   array          &$warnings     contains strings explaining minor data inconsistancies
     * @param   array          &$teachers     contains teacher resource data
     * 
     * @return void
     */
    protected function validate(&$classesnode, &$modules, &$errors, &$warnings, &$teachers)
    {
        if (empty($classesnode))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_CL_MISSING");
        }
        else
        {
            foreach ($classesnode->children() as $classnode)
            {
                $this->validateChild($classnode, $modules, $errors, $warnings, $teachers);
            }
        }
    }

    /**
     * checks whether class nodes have the expected structure and required
     * information
     *
     * @param   SimpleXMLNode  &$classnode  the class node to be validated
     * @param   array          &$modules    a model of the data within the classes node
     * @param   array          &$errors     contains strings explaining critical data inconsistancies
     * @param   array          &$warnings   contains strings explaining minor data inconsistancies
     * @param   array          &$resources  contains degree and description resource data
     * 
     * @return void
     */
    protected function validateChild(&$classnode, &$modules, &$errors, &$warnings, &$resources)
    {
        $degrees = $resources['degrees'];
        $descriptions = $resources['descriptions'];

        $gpuntisID = trim((string) $classnode[0]['id']);
        if (empty($gpuntisID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_CL_ID_MISSING"), $errors))
            {
                $errors[] = JText::_("COM_THM_ORGANIZER_CL_ID_MISSING");
            }
            return;
        }
        $moduleID = str_replace('CL_', '', $gpuntisID);
        $modules[$moduleID] = array();
        $modules[$moduleID]['gpuntisID'] = $gpuntisID;
        $modules[$moduleID]['name'] = $moduleID;

        $longname = trim((string) $classnode->longname);
        if (empty($longname))
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_CL_LN_MISSING', $moduleID);
            return;
        }
        $modules[$moduleID]['longname'] = $moduleID;

        $restriction = trim((string) $classnode->classlevel);
        if (empty($restriction))
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_CL_RESTRICTION_MISSING', $moduleID);
            return;
        }
        $modules[$moduleID]['restriction'] = $restriction;

        $degreeID = str_replace('DP_', '', trim((string) $classnode->class_department[0]['id']));
        if (empty($degreeID))
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_CL_DEGREE_MISSING', $moduleID);
            return;
        }
        elseif (empty($degrees[$degreeID]))
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_CL_DEGREE_LACKING', $moduleID, $degreeID);
            return;
        }
        $modules[$moduleID]['degree'] = $degreeID;

        $descriptionID = str_replace('DS_', '',trim((string) $classnode->class_description[0]['id']));
        if (empty($descriptionID))
        {
            $warnings[] = JText::sprintf('COM_THM_ORGANIZER_CL_DESC_MISSING', $moduleID);
            $descriptionID = '';
        }
        $modules[$moduleID]['description'] = $descriptionID;
    }

}
