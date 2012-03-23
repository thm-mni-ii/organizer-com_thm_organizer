<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model subject
 * @description data abstraction class for subjects
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('JPATH_PLATFORM') or die;
require_once JPATH_COMPONENT.'/models/modelresource.php';
jimport('joomla.application.component.model');
class thm_organizersModelsubject extends thm_organizersModelresource
{
    /**
     * validateXML
     *
     * checks whether the subjects node is empty and iterates over its childeren
     *
     * @param SimpleXMLNode $subjectsnode the subjects node to be validated
     * @param array $subjects a model of the data within the subjects node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $helper not used
     */
    protected function validateXML(&$subjectsnode, &$subjects, &$errors, &$warnings, &$helper = null)
    {
        if(empty($subjectsnode)) $errors[] = JText::_("COM_THM_ORGANIZER_SU_MISSING");
        else foreach( $subjectsnode->children() as $subjectnode )
                $this->validateXMLChild ($subjectnode, $subjects, $errors, $warnings, $helper);
    }

    /**
     * validateXMLChild
     *
     * checks whether subject nodes have the expected structure and required
     * information
     *
     * @param SimpleXMLNode $subjectnode the subject node to be validated
     * @param array $subjects a model of the data within the subjects node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $helper not used
     */
    protected function validateXMLChild(&$subjectnode, &$subjects, &$errors, &$warnings, &$helper = null)
    {
        $id = trim((string)$subjectnode[0]['id']);
        if(empty($id))
        {
            if(!in_array(JText::_("COM_THM_ORGANIZER_SU_ID_MISSING"), $errors))
                $errors[] = JText::_("COM_THM_ORGANIZER_SU_ID_MISSING");
            return;
        }
        $longname = trim((string)$subjectnode->longname);
        if(empty($longname))
        {
            $error = JText::_("COM_THM_ORGANIZER_SU");
            $error .= " $id ";
            $error .= JText::_("COM_THM_ORGANIZER_SU_LN_MISSING");
            $errors[] = $error;
            return;
        }
        else $subjects[$id]['longname'] = $longname;
        $subjectgroup = trim($subjectnode->subjectgroup);
        if(!empty($subjectgroup)) $subjects[$id]['subjectgroup'] = $subjectgroup;
        else
        {
            $warning = JText::_("COM_THM_ORGANIZER_SU");
            $warning .= " $longname ($id) ";
            $warning .= JText::_("COM_THM_ORGANIZER_SU_MN_MISSING");
            $warnings[] = $warning;
        }
    }

    /**
     * processData
     *
     * iterates over subject nodes, saves/updates subject data
     *
     * @param SimpleXMLNode $subjectsnode
     * @param array $subjects models the data contained in $subjectsnode
     * @param int $semesterID not used
     * @param array $helper not used
     */
    public function processData(&$subjectsnode, &$subjects, $semesterID = 0, &$helper = null)
    {
        foreach($subjectsnode->children() as $subjectnode)
            $this->processNode($subjectnode, $subjects);
    }

    /**
     * processNode
     *
     * saves/updates subjectdata
     *
     * @param SimpleXMLNode $subjectnode
     * @param array $subjects models the data contained in $subjectsnode
     * @param int $semesterID not used
     * @param array $helper not used
     */
    protected function processNode(&$subjectnode, &$subjects, $semesterID = 0, &$helper = null)
    {
        $gpuntisID = trim((string)$subjectnode[0]['id']);
        $name = str_replace("SU_","",$gpuntisID);
        $longname = trim((string)$subjectnode->longname);
        $moduleID = ($subjectnode->subjectgroup)? trim($subjectnode->subjectgroup) : "";

        $subject = JTable::getInstance('subjects', 'thm_organizerTable');
        $loadData = array('gpuntisID' => $gpuntisID);
        $data = array('gpuntisID' => $gpuntisID,
                      'name' => $name,
                      'alias' => $longname,
                      'moduleID' => $moduleID);
        $subject->load($loadData);
        $subject->save($data);
        
        $subjects[$gpuntisID] = array();
        $subjects[$gpuntisID]['id'] = $subject->id;
        $subjects[$gpuntisID]['name'] = $name;
        $subjects[$gpuntisID]['longname'] = $longname;
        $subjects[$gpuntisID]['moduleID'] = $moduleID;
    }
}

