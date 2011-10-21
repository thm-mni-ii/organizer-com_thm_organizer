<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model teacher
 * @description data abstraction class for teachers
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('JPATH_PLATFORM') or die;
require_once JPATH_COMPONENT.'/models/modelresource.php';
jimport('joomla.application.component.model');
class thm_organizersModelteacher extends thm_organizersModelresource
{
    /**
     * validateXML
     *
     * checks whether the teachers node is empty and iterates over its childeren
     *
     * @param SimpleXMLNode $teachersnode the teachers node to be validated
     * @param array $teachers a model of the data within the teachers node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $departments contains department resource data
     */
    protected function validateXML(&$teachersnode, &$teachers, &$errors, &$warnings, &$departments)
    {
        if(empty($teachersnode)) $errors[] = JText::_("COM_THM_ORGANIZER_TR_MISSING");
        else foreach( $teachersnode->children() as $teachernode )
                $this->validateXMLChild ($teachernode, $teachers, $errors, $warnings, $departments);
    }

    /**
     * validateXMLChild
     *
     * checks whether teacher nodes have the expected structure and required
     * information
     *
     * @param SimpleXMLNode $teachernode the teacher node to be validated
     * @param array $teachers a model of the data within the teachers node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $departments contains department resource data
     */
    protected function validateXMLChild(&$teachernode, &$teachers, &$errors, &$warnings, &$departments)
    {
        $id = trim((string)$teachernode[0]['id']);
        if(empty($id))
        {
            if(!in_array(JText::_("COM_THM_ORGANIZER_TR_ID_MISSING"), $errors))
                $errors[] = JText::_("COM_THM_ORGANIZER_TR_ID_MISSING");
            return;
        }
        $surname = trim((string)$teachernode->surname);
        if(empty($surname))
        {
            $error = JText::_("COM_THM_ORGANIZER_TR");
            $error .= " $name ($id) ";
            $error .= JText::_("COM_THM_ORGANIZER_TR_SN_MISSING");
            $errors[] = $error;
        }
        else $teachers[$id]['surname'] = $surname;
        $userid = trim((string)$teachernode->payrollnumber);
        if(empty($userid))
        {
            $warning = JText::_("COM_THM_ORGANIZER_TR");
            $warning .= " $surname ($id) ";
            $warning .= JText::_("COM_THM_ORGANIZER_TR_PN_MISSING");
            $warnings[] = $warning;
        }
        else $teachers[$id]['userid'] = $userid;
        $dptid = trim($teachernode->teacher_department[0]['id']);
        if(empty($dptid))
        {
            $error = JText::_("COM_THM_ORGANIZER_TR");
            $error .= " $surname ($id) ";
            $error .= JText::_("COM_THM_ORGANIZER_TR_DEPT_MISSING");
            $errors[] = $error;
        }
        else if(empty($departments[$dptid]) or empty($departments[$dptid]['subdepartment']))
        {
            $error = JText::_("COM_THM_ORGANIZER_TR")." $surname ($id) ";
            $error .= JText::_("COM_THM_ORGANIZER_TR_DEPT_LACKING")." $dptid.";
            $errors[] = $error;
        }
        else $teachers[$id]['department'] = $departments[$dptid];
    }

    /**
     * processData
     *
     * iterates over subject nodes, saves/updates subject data
     *
     * @param SimpleXMLNode $teachersnode
     * @param array $teachers models the data contained in $teachersnode
     * @param int $semesterID the id of the relevant planning period
     * @param array $departments contains department data
     */
    public function processData(&$teachersnode, &$teachers, $semesterID = 0, &$departments = null)
    {
        foreach($teachersnode->children() as $teachernode)
            $this->processNode($teachernode, $teachers, $semesterID, $departments);
    }

    /**
     * processNode
     *
     * saves/updates subjectdata
     *
     * @param SimpleXMLNode $teachernode
     * @param array $teachers models the data contained in $teachersnode
     * @param int $semesterID the id of the relevant planning period
     * @param array $departments contains department data
     */
    protected function processNode(&$teachernode, &$teachers, $semesterID = 0, &$departments = null)
    {
        $gpuntisID = trim((string)$teachernode[0]['id']);
        $name = trim((string)$teachernode->surname);
        $departmentID = trim((string)$teachernode->teacher_department[0]['id']);
        $departementID = $departments[$departmentID]['id'];
        $username = ($teachernode->payrollnumber)? trim($teachernode->payrollnumber) : "";

        $teacher = JTable::getInstance('teachers', 'thm_organizerTable');
        $loadData = array('gpuntisID' => $gpuntisID);
        $data = array('gpuntisID' => $gpuntisID,
                      'name' => $name,
                      'username' => $username,
                      'departmentID' => $departmentID);
        $teacher->load($loadData);
        $teacher->save($data);

        $teachers[$gpuntisID] = array();
        $teachers[$gpuntisID]['id'] = $teacher->id;
        $teachers[$gpuntisID]['name'] = $name;
        $teachers[$gpuntisID]['username'] = $username;
        $teachers[$gpuntisID]['departmentID'] = $departementID;
    }
}

