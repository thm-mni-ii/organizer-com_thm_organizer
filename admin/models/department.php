<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model department
 * @description data abstraction class for departments
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('JPATH_PLATFORM') or die;
require_once JPATH_COMPONENT.'/models/modelresource.php';
jimport('joomla.application.component.model');
class thm_organizersModeldepartment extends thm_organizersModelresource
{
    /**
     * validateXML
     *
     * checks whether the departments node is empty and iterates over its childeren
     *
     * @param SimpleXMLNode $departmentsnode the departments node to be validated
     * @param array $departments a model of the data within the departments node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $helper not used
     */
    protected function validateXML(&$departmentsnode, &$departments, &$errors, &$warnings, &$helper = null)
    {
        if(empty($departmentsnode)) $errors[] = JText::_("COM_THM_ORGANIZER_DP_MISSING");
        else foreach( $departmentsnode->children() as $departmentnode )
                $this->validateXMLChild ($departmentnode, $departments, $errors, $warnings, $helper);
    }

    /**
     * validateXMLChild
     *
     * checks whether department nodes have the expected structure and required
     * information
     *
     * @param SimpleXMLNode $departmentnode the department node to be validated
     * @param array $departments a model of the data within the departments node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $helper not used
     */
    protected function validateXMLChild(&$departmentnode, &$departments, &$errors, &$warnings, &$helper = null)
    {
        $id = trim((string)$departmentnode[0]['id']);
        if(empty($id))
        {
            if(!in_array(JText::_("COM_THM_ORGANIZER_DP_ID_MISSING"), $errors))
                $errors[] = JText::_("COM_THM_ORGANIZER_DP_ID_MISSING");
            return;
        }
        $details = explode(",",trim((string)$departmentnode->longname));
        if(empty($details) or count($details) == 0)
        {
            $error = JText::_("COM_THM_ORGANIZER_DP");
            $error .= " $id ";
            $error .= JText::_("COM_THM_ORGANIZER_DP_LACKING");
            $errors[] = $error;
            return;
        }
        $departments[$id] = array();
        $departments[$id]['institution'] = trim($details [0]);
        $departments[$id]['campus'] = trim($details [1]);
        if(isset($details [2]))$departments[$id]['department'] = trim($details [2]);
        if(isset($details [3])) $departments[$id]['subdepartment'] = trim($details [3]);
    }

    /**
     * processData
     *
     * iterates over department nodes, saves/updates department data
     *
     * @param SimpleXMLNode $parent
     * @param array $data models the data contained in $element
     * @param int $semesterID the id of the relevant planning period
     * @param array $helper contains optional external resource data as needed
     */
    public function processData(&$departmentsnode, &$departments, $semesterID = 0, &$helper = null)
    {
        foreach($departmentsnode->children() as $departmentnode)
            $this->processNode($departmentnode, $departments);
    }

    /**
     * processNode
     *
     * saves/updates department data
     *
     * @param SimpleXMLNode $child
     * @param array $data models the data contained in $element
     * @param int $semesterID the id of the relevant planning period
     * @param array $helper contains optional external resource data as needed
     */
    protected function processNode(&$departmentnode, &$departments, $semesterID = 0, &$helper = null)
    {
        $gpuntisID = trim((string)$departmentnode[0]['id']);
        $details = explode(', ', trim((string)$departmentnode->longname));
        $name = $details[count($details) - 1];
        $institution = (isset($details [0]))? trim($details [0]) : "";
        $campus = (isset($details [1]))? trim($details [1]) : "";
        $department = (isset($details [2]))? trim($details [2]) : "";
        $subdepartment = (isset($details [3]))? trim($details [2]) : "";

        $department = JTable::getInstance('departments', 'thm_organizerTable');
        $loadData = array('gpuntisID' => $gpuntisID);
        $data = array('gpuntisID' => $gpuntisID,
                      'name' => $name,
                      'institution' => $institution,
                      'campus' => $campus,
                      'department' => $department,
                      'subdepartment' => $subdepartment);
        $department->load($loadData);
        $department->save($data);

        $departments[$gpuntisID] = array();
        $departments[$gpuntisID]['id'] = $department->id;
        $departments[$gpuntisID]['name'] = $name;
        $departments[$gpuntisID]['institution'] = $institution;
        $departments[$gpuntisID]['campus'] = $campus;
        $departments[$gpuntisID]['department'] = $department;
        $departments[$gpuntisID]['subdepartment'] = $subdepartment;
    }
}
