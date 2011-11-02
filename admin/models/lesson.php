<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model lesson
 * @description data abstraction class for lessons
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('JPATH_PLATFORM') or die;
require_once JPATH_COMPONENT.'/models/modelresource.php';
jimport('joomla.application.component.model');
class thm_organizersModellesson extends thm_organizersModelresource
{
    /**
     * validateXML
     *
     * checks whether the parent node is empty and iterates over its childeren
     *
     * @param SimpleXMLNode $lessonsnode the lessons node to be validated
     * @param array $lessons not used
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $resources arrays containing resource data
     */
    protected function validateXML(&$lessonsnode, &$lessons, &$errors, &$warnings, &$resources)
    {
        if(empty($lessonsnode)) $errors[] = JText::_("COM_THM_ORGANIZER_SCH_LS_MISSING");
        else foreach( $lessonsnode->children() as $lessonnode )
                $this->validateXMLChild($lessonnode, $lessons, $errors, $warnings, $helpers);
    }

    /**
     * validateXMLChild
     *
     * checks whether lesson nodes have the expected structure and required
     * information
     *
     * @param SimpleXMLNode $lessonnode the lesson node to be validated
     * @param array $lessons not used
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param array $warnings contains strings explaining minor data inconsistancies
     * @param array $resources arrays containing resource data
     */
    protected function validateXMLChild(&$lessonnode, &$lessons, &$errors, &$warnings, &$resources)
    {
        $subjects = $resources['subjects'];
        $teachers = $resources['teachers'];
        $classes = $resources['classes'];
        $periods = $resources['periods'];
        $id = trim((string)$lessonnode[0]['id']);
        if(empty($id))
        {
            if(!in_array(JText::_("COM_THM_ORGANIZER_SCH_LS_ID_MISSING"), $errors))
                $errors[] = JText::_("COM_THM_ORGANIZER_SCH_LS_ID_MISSING");
            return;
        }
        $error_start = JText::_("COM_THM_ORGANIZER_SCH_LS");
        $lesson_name = "";
        $subjectID = (string)$lesson->lesson_subject[0]['id'];
        if(empty($subjectID))
        {
            $error = $error_start." $id ";
            $error .= JText::_("COM_THM_ORGANIZER_SCH_RM_SU_MISSING");
            $errors[] = $error;
            return;
        }
        else if(empty($subjects[$subjectID]))
        {
            $error = $error_start." $id ";
            $error .= JText::_("COM_THM_ORGANIZER_SCH_RM_SU_LACKING")." $subjectID.";
            $errors[] = $error;
            return;
        }
        else $lesson_name = $subjects[$subjectID]['longname'];
        $error_start .= " $lesson_name ($id) ";
        $teacherID = (string)$lesson->lesson_teacher[0]['id'];
        if(empty($teacherID))
            $errors[] = $error_start.JText::_("COM_THM_ORGANIZER_LS_TR_MISSING");
        else if(empty($teachers[$teacherID]))
            $errors[] = $error_start.JText::_("COM_THM_ORGANIZER_LS_TR_LACKING")." $teacherID.";
        $classIDs = (string)$lesson->lesson_classes[0]['id'];
        if(empty($classIDs))
            $errors[] = $error_start.JText::_("COM_THM_ORGANIZER_LS_CL_MISSING");
        else
        {
            $classIDs = explode(" ", $classIDs);
            foreach($classIDs as $classID)
            {
                if(!key_exists($classID, $classes))
                    $errors[] = $error_start.JText::_("COM_THM_ORGANIZER_LS_CL_LACKING")." $classID.";
            }
        }
        $lesson_type = $lesson->text1;
        if(empty($lesson_type))
            $errors[] = $error_start.JText::_("COM_THM_ORGANIZER_LS_TYPE_MISSING");
        $periods = trim($lesson->periods);
        if(empty($periods))
            $errors[] = $error_start.JText::_("COM_THM_ORGANIZER_LS_TP_MISSING");
        $times = $lesson->times;
        $timescount = count($times->children());
        if(isset($periods) and $periods != $timescount)
        {
            $warning = $error_start;
        }
        foreach($times->children() as $instance)
                $this->validateInstance(&$instance, &$periods, &$rooms, &$errors, $error_start);
    }


    /**
     * validateInstance
     *
     * checks whether lesson instance nodes have the expected structure and
     * required information
     *
     * @param SimpleXMLNode $instance the lesson instance node to be validated
     * @param array $periods array containing period data
     * @param array $rooms array containing room data
     * @param array $errors contains strings explaining critical data inconsistancies
     * @param string $error_start string used for error texts
     */
    private function validateInstance(&$instance, &$periods, &$rooms, &$errors, $error_start)
    {
        $day = (string)$instance->assigned_day;
        if(empty($day))
        {
            $error = $error_start.JText::_("COM_THM_ORGANIZER_LS_TP_DAY_MISSING");
            if(!in_array($error, $errors))$errors[] = $error;
        }
        $period = (string)$instance->assigned_period;
        if(empty($period))
        {
            $error = $error_start.JText::_("COM_THM_ORGANIZER_LS_TP_PERIOD_MISSING");
            if(!in_array($error, $errors))$errors[] = $error;
        }
        if(isset($day) and isset($period) and empty($periods[$day][$period]))
        {
            $error = $error_start.JText::_("COM_THM_ORGANIZER_LS_TP_LACKING");
            $error .= JText::_("COM_THM_ORGANIZER_TP_DAY").": $day ";
            $error .= JText::_("COM_THM_ORGANIZER_TP").": $period";
            $errors[] = $error;
        }
        $roomID = (string)$instance->assigned_room[0]['id'];
        if(empty($roomID))
        {
            $error = $error_start.JText::_("COM_THM_ORGANIZER_LS_TP_ROOM_MISSING");
            if(!in_array($error, $errors))$errors[] = $error;
        }
        else if(!key_exists($roomID, $rooms))
        {
            $error = $error_start.JText::_("COM_THM_ORGANIZER_LS_TP_ROOM_LACKING")." $roomID.";
            if(!in_array($error, $errors))$errors[] = $error;
        }
    }


    /**
     * processData
     *
     * iterates over lesson nodes, saves/updates lesson data
     *
     * @param SimpleXMLNode $lessonsnode
     * @param array $lessons models the data contained in $lessonsnode
     * @param int $semesterID the id of the relevant planning period
     * @param array $resources contains resource data
     */
    public function processData(&$lessonsnode, &$lessons, $semesterID, &$resources)
    {
        foreach($lessonsnode->children() as $lessonnode)
            $this->processNode($lessonnode, $lessons, $semesterID, $resources);
    }

    /**
     * processNode
     *
     * saves/updates lesson data
     *
     * @param SimpleXMLNode $lessonnode
     * @param array $lessons models the data contained in $lessonsnode
     * @param int $semesterID the id of the relevant planning period
     * @param array $resources contains resource data
     */
    protected function processNode(&$lessonnode, &$lessons, $semesterID, &$resources)
    {
        $dbo = JFactory::getDbo();
        $teachers = $resources['teachers'];
        $classes = $resources['classes'];
        $subjects = $resources['subjects'];

        $gpuntisID = trim((string)$lessonnode[0]['id']);
        $gpuntisID = substr($gpuntisID, 0, strlen($gpuntisID) - 2);
        $subjectID = trim((string)$lessonnode->lesson_subject[0]['id']);
        $subjectID = $subjects[$subjectID]['id'];
        $periodCount = trim((string)$lessonnode->periods);
        $lessontype = substr(trim((string)$lessonnode->text1), 0, 32);
        $comment = substr(trim((string)$lessonnode->text2), 0, 256);
        $comment = ($comment)? $comment : '';

        $lesson = JTable::getInstance('lessons', 'thm_organizerTable');
        $loadData = array('gpuntisID' => $gpuntisID,
                          'semesterID' => $semesterID,
                          'plantypeID' => '1');
        $data = array('gpuntisID' => $gpuntisID,
                      'subjectID' => $subjectID,
                      'periods' => $periodCount,
                      'semesterID' => $semesterID,
                      'plantypeID' => '1',
                      'type' => $lessontype,
                      'comment' => $comment);
        $lesson->load($loadData);
        $lesson->save($data);

        if(!isset($lessons[$gpuntisID]))
        {
            $lessons[$gpuntisID] = array();
            $lessons[$gpuntisID]['subjectID'] = $subjectID;
            $lessons[$gpuntisID]['type'] = $lessontype;
            $lessons[$gpuntisID]['comment'] = $comment;
            $lessons[$gpuntisID]['classIDs'] = array();
            $lessons[$gpuntisID]['teacherIDs'] = array();
            $lessons[$gpuntisID]['periods'] = array();
        }

        $teacherID = trim((string)$lessonnode->lesson_teacher[0]['id']);
        $teacherID = $teachers[$teacherID]['id'];
        $this->saveRelation($lesson->id, 'teacherID', $teacherID, "#__thm_organizer_lesson_teachers");
        if(!in_array($teacherID, $lessons[$gpuntisID]['teacherIDs']))
            $lessons[$gpuntisID]['teacherIDs'][] = $teacherID;

        $classIDs = trim((string)$lessonnode->lesson_classes[0]['id']);
        $classIDs = explode(" ", $classIDs);
        foreach($classIDs as $classID)
        {
            $this->saveRelation($lesson->id, 'classID', $classes[$classID]['id'], "#__thm_organizer_lesson_classes");
            if(!in_array($classID, $lessons[$gpuntisID]['classIDs']))
                $lessons[$gpuntisID]['classIDs'][] = $classes[$classID]['id'];
        }
        foreach($lessonnode->times->children() as $instance)
            $this->processInstance($lesson, $instance, $lessons, $resources);
    }

    /**
     * processRelation
     *
     * a generic function for saving lesson - resource relation if non-existant
     *
     * @param int $lessonID
     * @param string $resourceName the column name of the resource in the
     *                             relation table
     * @param int $resourceID the id of the resource in its resource table
     * @param string $tablename the name of the relation table
     */
    private function saveRelation($lessonID, $resourceName, $resourceID, $tablename)
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("COUNT(*)");
        $query->from("$tablename");
        $query->where("lessonID = '$lessonID'");
        $query->where("$resourceName = '$resourceID'");
        $dbo->setQuery((string)$query);
        $count_relations = $dbo->loadResult();
        if(!$count_relations)
        {
            $query = $dbo->getQuery(true);
            $statement = "$tablename ";
            $statement .= "( lessonID, $resourceName ) ";
            $statement .= "VALUES ";
            $statement .= "( '$lessonID', '$resourceID' ) ";
            $query->insert($statement);
            $dbo->setQuery((string)$query);
            $dbo->query();
        }
    }

    /**
     * processInstance
     *
     * saves a lesson instance and models instance data in $lessons
     *
     * @param JTableObject $lesson
     * @param SimpleXMLNode $instance contains data about a module instance
     * @param array $lessons models the data contained in $lessonsnode
     * @param array $resources contains resource data
     */
    private function processInstance(&$lesson, &$instance, &$lessons, &$resources)
    {
        $periods = $resources['periods'];
        $rooms = $resources['rooms'];

        $day = (int)$instance->assigned_day;
        $period = (int)$instance->assigned_period;
        $periodID = $periods[$day][$period]['id'];
        $roomID = trim((string)$instance->assigned_room[0]['id']);
        $roomID = $rooms[$roomID]['id'];
        $this->saveInstance($lesson->id, $roomID, $periodID);

        if(!isset($lessons[$lesson->gpuntisID]['periods'][$periodID]))
            $lessons[$lesson->gpuntisID]['periods'][$periodID] = array();
        if(!isset($lessons[$lesson->gpuntisID]['periods'][$periodID]['roomIDs']))
            $lessons[$lesson->gpuntisID]['periods'][$periodID]['roomIDs'] = array();
        if(!in_array($roomID, $lessons[$lesson->gpuntisID]['periods'][$periodID]['roomIDs']))
            $lessons[$lesson->gpuntisID]['periods'][$periodID]['roomIDs'][] = $roomID;
    }

    /**
     * saveInstance
     *
     * inserts lesson/period/room relation if not already existant
     *
     * @param int $lessonID
     * @param int $roomID
     * @param int $periodID
     */
    private function saveInstance($lessonID, $roomID, $periodID)
    {

        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("COUNT(*)");
        $query->from("#__thm_organizer_lesson_times");
        $query->where("lessonID = '$lessonID'");
        $query->where("roomID = '$roomID'");
        $query->where("periodID = '$periodID'");
        $dbo->setQuery((string)$query);
        $count_times = $dbo->loadResult();

        if(!$count_times)
        {
            $query = $dbo->getQuery(true);
            $statement = "#__thm_organizer_lesson_times ";
            $statement .= "( lessonID, roomID, periodID ) ";
            $statement .= "VALUES ";
            $statement .= "( '$lessonID', '$roomID', '$periodID' ) ";
            $query->insert($statement);
            $dbo->setQuery((string)$query);
            $dbo->query();
        }
    }

}

