<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model class
 * @description data abstraction class for classes
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('JPATH_PLATFORM') or die;
require_once JPATH_COMPONENT.'/models/modelresource.php';
jimport('joomla.application.component.model');
class thm_organizersModelclass extends thm_organizersModelresource
{
    /**
     * validateXML
     *
     * checks whether the classes node is empty and iterates over its childeren
     *
     * @param SimpleXMLNode $classesnode the classes node to be validated
     * @param array $classes a model of the data within the classes node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $teachers contains teacher resource data
     */
    protected function validateXML(&$classesnode, &$classes, &$errors, &$warnings, &$teachers)
    {
        if(empty($classesnode)) $errors[] = JText::_("COM_THM_ORGANIZER_CL_MISSING");
        else foreach( $classesnode->children() as $classnode )
                $this->validateXMLChild ($classnode, $classes, $errors, $warnings, $teachers);
    }

    /**
     * validateXMLChild
     *
     * checks whether class nodes have the expected structure and required
     * information
     *
     * @param SimpleXMLNode $classnode the class node to be validated
     * @param array $classes a model of the data within the classes node
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $teachers contains teacher resource data
     */
    protected function validateXMLChild(&$classnode, &$classes, &$errors, &$warnings, &$teachers)
    {
        $id = trim((string)$classnode[0]['id']);
        if(empty($id))
        {
            if(!in_array(JText::_("COM_THM_ORGANIZER_CL_ID_MISSING"), $errors))
                $errors[] = JText::_("COM_THM_ORGANIZER_CL_ID_MISSING");
            return;
        }
        $longname = trim((string)$classnode->longname);
        if(empty($longname))
        {
            $error = JText::_("COM_THM_ORGANIZER_CL")." $id ";
            $error .= JText::_("COM_THM_ORGANIZER_CL_LN_MISSING");
            $errors[] = $error;
            return;
        }
        else
        {
            $details = explode(",", $longname);
            if(count($details) < 2)
            {
                $error = JText::_("COM_THM_ORGANIZER_CL")." $id ";
                $error .= JText::_("COM_THM_ORGANIZER_CL_LN_LACKING");
                $errors[] = $error;
            }
            else
            {
                $classes[$id]['major'] = $details[0];
                $classes[$id]['semester'] = $details[1];
            }
        }
        $teacherid = trim((string)$classnode->class_teacher[0]['id']);
        if(empty($teacherid))
        {
            $error = JText::_("COM_THM_ORGANIZER_CL")." $id ";
            $error .= JText::_("COM_THM_ORGANIZER_CL_TR_MISSING");
            $errors[] = $error;
        }
        else if(empty($teachers[$teacherid]) or empty($teachers[$teacherid]['userid']))
        {
            $warning = JText::_("COM_THM_ORGANIZER_CL")." $longname ($id) ";
            $warning .= JText::_("COM_THM_ORGANIZER_CL_TR_LACKING");
            $warningss[] = $warning;
        }
        else $classes[$id]['teacher'] = $teacherid;
    }

    /**
     * processData
     *
     * iterates over class nodes, saves/updates class data
     *
     * @param SimpleXMLNode $classesnode
     * @param array $classes models the data contained in $classesnode
     * @param int $semesterID not used
     * @param array $teachers contains teacher data
     */
    public function processData(&$classesnode, &$classes, $semesterID = 0, &$teachers = null)
    {
        foreach($classesnode->children() as $classnode)
            $this->processNode($classnode, $classes, $semesterID, $teachers);
    }

    /**
     * processNode
     *
     * saves/updates class tdata
     *
     * @param SimpleXMLNode $classnode
     * @param array $classes models the data contained in $classesnode
     * @param int $semesterID not used
     * @param array $teachers contains teacher data
     */
    protected function processNode(&$classnode, &$classes, $semesterID = 0, &$teachers = null)
    {
        $gpuntisID = trim((string)$classnode[0]['id']);
        $name = str_replace("CL_", "", $gpuntisID);
        $longname = trim((string)$classnode->longname);
        list($major, $semester) = explode(",", $longname);
        $teacherID = trim((string)$classnode->class_teacher[0]['id']);
        $teacherID = $teachers[$teacherID]['id'];

        $class = JTable::getInstance('classes', 'thm_organizerTable');
        $loadData = array('gpuntisID' => $gpuntisID);
        $data = array('gpuntisID' => $gpuntisID,
                      'name' => $name,
                      'alias' => $longname,
                      'teacherID' => $teacherID,
                      'semester' => $semester,
                      'major' => $major);
        $class->load($loadData);
        $class->save($data);

        $classes[$gpuntisID] = array();
        $classes[$gpuntisID]['id'] = $class->id;
        $classes[$gpuntisID]['name'] = $name;
        $classes[$gpuntisID]['alias'] = $longname;
        $classes[$gpuntisID]['teacherID'] = $teacherID;
        $classes[$gpuntisID]['semester'] = $semester;
        $classes[$gpuntisID]['major'] = $major;
    }
}


