<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        teacher specific business logic and database abstraction
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
class thm_organizersModelteacher extends thm_organizersModelresource
{
    /**
     * checks whether the teachers node is empty and iterates over its childeren
     *
     * @param   SimpleXMLNode  &$teachersnode  the teachers node to be validated
     * @param   array          &$teachers      a model of the data within the teachers node
     * @param   array          &$errors        contains strings explaining critical data inconsistancies
     * @param   array          &$warnings      contains strings explaining minor data inconsistancies
     * @param   array          &$descriptions  contains department resource data
     * 
     * @return void
     */
    protected function validate(&$teachersnode, &$teachers, &$errors, &$warnings, &$descriptions)
    {
        if (empty($teachersnode))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_TR_MISSING");
        }
        else
        {
            foreach ($teachersnode->children() as $teachernode)
            {
                $this->validateChild($teachernode, $teachers, $errors, $warnings, $descriptions);
            }
        }
    }

    /**
     * checks whether teacher nodes have the expected structure and required
     * information
     *
     * @param   SimpleXMLNode  &$teachernode   the teacher node to be validated
     * @param   array          &$teachers      a model of the data within the teachers node
     * @param   array          &$errors        contains strings explaining critical data inconsistancies
     * @param   array          &$warnings      contains strings explaining minor data inconsistancies
     * @param   array          &$descriptions  contains department resource data
     * 
     * @return void
     */
    protected function validateChild(&$teachernode, &$teachers, &$errors, &$warnings, &$descriptions)
    {
        $gpuntisID = trim((string) $teachernode[0]['id']);
        if (empty($gpuntisID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_TR_ID_MISSING"), $errors))
            {
                $errors[] = JText::_("COM_THM_ORGANIZER_TR_ID_MISSING");
            }
            return;
        }
        $teacherID = str_replace('TR_', '', $gpuntisID);
        $teachers[$teacherID] = array();
        $teachers[$teacherID]['gpuntisID'] = $gpuntisID;

        $surname = trim((string) $teachernode->surname);
        if (empty($surname))
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_TR_SN_MISSING', $teacherID);
            return;
        }
        $teachers[$teacherID]['surname'] = $surname;

        $firstname = trim((string) $teachernode->surname);
        if (empty($firstname))
        {
            $warnings[] = JText::sprintf('COM_THM_ORGANIZER_TR_FN_MISSING', $teacherID, $surname);
        }
        else
        {
            $teachers[$teacherID]['firstname'] = $firstname;
        }

        $userid = trim((string) $teachernode->payrollnumber);
        if (empty($userid))
        {
            $warnings[] = JText::sprintf("COM_THM_ORGANIZER_TR_PN_MISSING", "$surname ($teacherID) ");
        }
        else
        {
            $teachers[$teacherID]['userid'] = $userid;
        }

        $descriptionID = str_replace('DS_', '', trim($teachernode->teacher_description[0]['id']));
        if (empty($descriptionID))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_TR_DESC_MISSING", "$surname ($teacherID) ");
            return;
        }
        elseif (empty($descriptions[$descriptionID]))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_TR_DESC_LACKING", "$surname ($teacherID) ", $descriptionID);
        }
        else
        {
            $teachers[$teacherID]['description'] = $descriptionID;
        }
    }

}
