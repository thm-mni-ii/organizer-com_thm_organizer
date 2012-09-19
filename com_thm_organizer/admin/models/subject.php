<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        subject specific business logic and database abstraction
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
 * Class defining functions to be used for teacher resources
 * 
 * @package  Admin
 * 
 * @since    2.5.4 
 */
class thm_organizersModelsubject extends thm_organizersModelresource
{
    /**
     * checks whether the subjects node is empty and iterates over its childeren
     *
     * @param   SimpleXMLNode  &$subjectsnode  the subjects node to be validated
     * @param   array          &$subjects      a model of the data within the subjects node
     * @param   array          &$errors        contains strings explaining critical data inconsistancies
     * @param   array          &$warnings      contains strings explaining minor data inconsistancies
     * @param   array          &$descriptions  array containing description data
     * 
     * @return void
     */
    protected function validate(&$subjectsnode, &$subjects, &$errors, &$warnings, &$descriptions)
    {
        if (empty($subjectsnode))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_SU_MISSING");
        }
        else
        {
            foreach ($subjectsnode->children() as $subjectnode)
            {
                $this->validateChild($subjectnode, $subjects, $errors, $warnings, $descriptions);
            }
        }
    }

    /**
     * checks whether subject nodes have the expected structure and required
     * information
     *
     * @param   SimpleXMLNode  &$subjectnode   the subject node to be validated
     * @param   array          &$subjects      a model of the data within the subjects node
     * @param   array          &$errors        contains strings explaining critical data inconsistancies
     * @param   array          &$warnings      contains strings explaining minor data inconsistancies
     * @param   array          &$descriptions  not used
     * 
     * @return void
     */
    protected function validateChild(&$subjectnode, &$subjects, &$errors, &$warnings, &$descriptions = null)
    {
        $gpuntisID = trim((string) $subjectnode[0]['id']);
        if (empty($gpuntisID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_SU_ID_MISSING"), $errors))
            {
                $errors[] = JText::_("COM_THM_ORGANIZER_SU_ID_MISSING");
            }
            return;
        }
        $subjectID = str_replace('SU_', '', $gpuntisID);
        $subjects[$subjectID] = array();
        $subjects[$subjectID]['gpuntisID'] = $gpuntisID;
        $subjects[$subjectID]['name'] = $subjectID;

        $longname = trim((string) $subjectnode->longname);
        if (empty($longname))
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_SU_LN_MISSING', $subjectID);
            return;
        }
        else
        {
            $subjects[$subjectID]['longname'] = $longname;
        }

        $subjectNo = trim((string) $subjectnode->text);
        if (empty($subjectNo))
        {
            $warnings[] = JText::sprintf('COM_THM_ORGANIZER_SU_MN_MISSING', $subjectID);
        }
        else
        {
            $subjects[$subjectID]['subjectNo'] = $subjectgroup;
        }

        $descriptionID = str_replace('DS_', '', trim($subjectnode->subject_description[0]['id']));
        if (empty($descriptionID))
        {
            $warnings[] = JText::sprintf('COM_THM_ORGANIZER_SU_AREA_MISSING', $subjectID);
        }
        elseif (empty($descriptions[$descriptionID]))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_SCH_SU_DESC_MISSING", $subjectID, $descriptionID);
            return;
        }
        else
        {
            $subjects[$subjectID]['description'] = $descriptionID;
        }
    }
}
