<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        lesson specific business logic and database abstraction
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('JPATH_PLATFORM') or die;
require_once JPATH_COMPONENT . '/models/resource.php';

/**
 * Class defining functions to be used for lesson resources
 * 
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerModellesson extends THM_OrganizerModelresource
{
    /**
     * checks whether the parent node is empty and iterates over its childeren
     *
     * @param   SimpleXMLNode  &$lessonsnode  the lessons node to be validated
     * @param   array          &$lessons      models the data in $lessonsnode
     * @param   array          &$errors       contains strings explaining critical data inconsistancies
     * @param   array          &$warnings     contains strings explaining minor data inconsistancies
     * @param   array          &$resources    arrays containing resource data
     * @param   array          &$calendar     array containing lesson instance data
     * 
     * @return void
     */
    protected function validate(&$lessonsnode, &$lessons, &$errors, &$warnings, &$resources, &$calendar)
    {
        if (empty($lessonsnode))
        {
            $errors[] = JText::_("COM_THM_ORGANIZER_LS_MISSING");
        }
        else
        {
            foreach ( $lessonsnode->children() as $lessonnode )
            {
                $this->validateChild($lessonnode, $lessons, $errors, $warnings, $resources, $calendar);
            }
        }
    }

    /**
     * checks whether lesson nodes have the expected structure and required
     * information
     *
     * @param   SimpleXMLNode  &$lessonnode  the lesson node to be validated
     * @param   array          &$lessons     not used
     * @param   array          &$errors      contains strings explaining critical data inconsistancies
     * @param   array          &$warnings    contains strings explaining minor data inconsistancies
     * @param   array          &$resources   arrays containing resource data
     * @param   array          &$calendar    array containing lesson instance data
     * 
     * @return void
     */
    protected function validateChild(&$lessonnode, &$lessons, &$errors, &$warnings, &$resources, &$calendar)
    {
        $descriptions = $resources['descriptions'];
        $subjects     = $resources['subjects'];
        $teachers     = $resources['teachers'];
        $modules      = $resources['modules'];
        $periods      = $resources['periods'];
        $rooms        = $resources['rooms'];

        $gpuntisID = trim((string) $lessonnode[0]['id']);
        if (empty($gpuntisID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_LS_ID_MISSING"), $errors))
            {
                $errors[] = JText::_("COM_THM_ORGANIZER_LS_ID_MISSING");
            }
            return;
        }
        $lessonID = str_replace('LS_', '', $gpuntisID);
        $lessonID = substr($lessonID, 0, strlen($lessonID) - 2);
        if (!isset($lessons[$lessonID]))
        {
            $lessons[$lessonID] = array();
        }
        $lessons[$lessonID]['gpuntisID'] = $gpuntisID;

        $subjectID = str_replace('SU_', '', trim((string) $lessonnode->lesson_subject[0]['id']));
        if (empty($subjectID))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_LS_SU_MISSING", $lessonID);
            return;
        }
        elseif (empty($subjects[$subjectID]))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_LS_SU_LACKING", $lessondID, $subjectID);
            return;
        }
        if (!isset($lessons[$lessonID]['subjects']))
        {
            $lessons[$lessonID]['subjects'] = array();
        }
        if (!key_exists($subjectID, $lessons[$lessonID]['subjects']))
        {
            $lessons[$lessonID]['subjects'][$subjectID] = $subjects[$subjectID]['longname'];
        }
        $lessonName = implode(' / ', $lessons[$lessonID]['subjects']);

        /*$descriptionID = str_replace('DS_', '', trim((string) $lessonnode->lesson_description[0]['id']));
        if (empty($descriptionID))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_LS_DESC_MISSING", $lessonName, $lessonID);
            return;
        }
        elseif (empty($descriptions[$descriptionID]))
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_LS_DESC_LACKING', $lessonName, $lessondID, $subjectID);
            return;
        }
        if (!isset($lessons[$lessonID]['description']))
        {
            $lessons[$lessonID]['description'] = $descriptionID;
        }
        $lessonName = " - $descriptionID";
        $lessons[$lessonID]['name'] = $lessonName;*/

        $teacherID = str_replace('TR_', '', trim((string) $lessonnode->lesson_teacher[0]['id']));
        if (empty($teacherID))
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_LS_TR_MISSING', $lessonName, $lessonID);
            return;
        }
        elseif (empty($teachers[$teacherID]))
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_LS_TR_LACKING', $lessonName, $lessonID, $teacherID);
            return;
        }
        $teacherName = $teachers[$teacherID]['surname'];
        if (!isset($lessons[$lessonID]['teachers']))
        {
            $lessons[$lessonID]['teachers'] = array();
        }
        if (!key_exists($teacherID, $lessons[$lessonID]['teachers']))
        {
            $lessons[$lessonID]['teachers'][$teacherID] = $teachers[$teacherID]['surname'];
        }

        $moduleIDs = (string) $lessonnode->lesson_classes[0]['id'];
        if (empty($moduleIDs))
        {
            $errors[] = JText::sprintf("COM_THM_ORGANIZER_LS_CL_MISSING", $lessonName, $lessonID);
        }
        else
        {
            $moduleIDs = explode(" ", $moduleIDs);
            foreach ($moduleIDs as $key => $moduleIDs)
            {
                $moduleID = str_replace('CL_', '', $moduleID);
                if (!key_exists($moduleID, $modules))
                {
                    $errors[] = JText::sprintf("COM_THM_ORGANIZER_LS_CL_LACKING", $lessonName, $lessonID, $moduleID);
                    return;
                }
                $lessons[$lessonID]['modules'][$moduleID] = $modules[$moduleID]['longname'];
            }
        }

        $lessonStartDate = trim((string) $lessonnode->effectivebegindate);
        if (empty($lessonStartDate))
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_LS_SD_MISSING', $lessonName, $lessonID);
            return;
        }
        $lessonStartDate = strtotime(substr($lessonStartDate, 0, 4) . '-' . substr($lessonStartDate, 4, 2) . '-' . substr($lessonStartDate, 6, 2)); 
        $startDateExists = array_key_exists($lessonStartDate, $calendar);
        if (!$startDateExists)
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_LS_SD_OOB', $lessonName, $lessonID);
            return;
        }

        $lessonEndDate = trim((string) $lessonnode->effectiveenddate);
        if (empty($lessonEndDate))
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_LS_ED_MISSING', $lessonName, $lessonID);
            return;
        }
        $lessonEndDate = strtotime(substr($lessonEndDate, 0, 4) . '-' . substr($lessonEndDate, 4, 2) . '-' . substr($lessonEndDate, 6, 2)); 
        $endDateExists = array_key_exists($lessonStartDate, $calendar);
        if (!$endDateExists)
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_LS_ED_OOB', $lessonName, $lessonID);
            return;
        }

        // Checks if startdate is before enddate
        $startDT = strtotime($lessonStartDate);
        $endDT = strtotime($lessonEndDate);
        if ($endDT <= $startDT )
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_LS_SDED_INCONSISTANT', $lessonName, $lessonID);
            return;
        }

        $occurences = trim((string) $lessonnode->occurence);
        if (empty($occurences))
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_LS_OCC_MISSING', $lessonName, $lessonID);
            return;
        }
        elseif (strlen($occurences) !== $calendar['sylength'])
        {
            $errors[] = JText::sprintf('COM_THM_ORGANIZER_LS_OCC_LEN_BAD', $lessonName, $lessonID);
            return;
        }
        $occurences = substr($occurences, $calendar['frontoffset'], $calendar['termlength']);
        $occurences = str_split($occurences);
        
        $comment = trim((string) $lessonnode->text);
        $lessons[$lessonID]['comment'] = empty($comment)? '' : $comment;
        
        $periodsleaf = trim($lessonnode->periods);
        if (empty($periodsleaf))
        {
            $warnings[] = JText::sprintf("COM_THM_ORGANIZER_LS_TP_MISSING", $lessonName, $lessonID);
        }
        $times = $lessonnode->times;
        $timescount = count($times->children());
        if (isset($periods) and $periods != $timescount)
        {
            $warnings[] = JText::sprintf('COM_THM_ORGANIZER_LS_TP_UNPLANNED', $lessonName, $lessonID);
        }
        
        $currentDT = $startDT;
        foreach ($occurences as $occurence)
        {
            if ($occurence == 1)
            {
                $currentDate = date('Y-m-d', $currentDT);
                if (!isset($calendar[$currentDate]))
                {
                    $errors[] = JText::sprintf('COM_THM_ORGANIZER_LS_OCC_INDEX_BAD', $lessonName, $lessonID);
                    return;
                }
                foreach ($times->children() as $instance)
                {
                    $day = trim((string) $instance->assigned_day);
                    if (empty($day))
                    {
                        $error = JText::sprintf('COM_THM_ORGANIZER_LS_TP_DAY_MISSING', $lessonName, $lessonID);
                        if (!in_array($error, $errors))
                        {
                            $errors[] = $error;
                        }
                    }
                    if ($day != date('w', $currentDT))
                    {
                        continue;
                    }

                    $period = trim((string) $instance->assigned_period);
                    if (empty($period))
                    {
                        $error = JText::sprintf('COM_THM_ORGANIZER_LS_TP_PERIOD_MISSING', $lessonName, $lessonID);
                        if (!in_array($error, $errors))
                        {
                            $errors[] = $error;
                        }
                    }
                    if (!isset($calendar[$currentDate][$period]))
                    {
                        $error = JText::sprintf('COM_THM_ORGANIZER_LS_TP_LACKING', $lessonName, $lessonID, date('l', $currentDT), $period);
                        if (!in_array($error, $errors))
                        {
                            $errors[] = $error;
                        }
                    }

                    $roomID = str_replace('RM_', '', trim((string) $instance->assigned_room[0]['id']));
                    if (empty($roomID))
                    {
                        $error = JText::sprintf('COM_THM_ORGANIZER_LS_TP_ROOM_MISSING', $lessonName, $lessonID, date('l', $currentDT), $period);
                        if (!in_array($error, $errors))
                        {
                            $errors[] = $error;
                        }
                    }
                    elseif (!key_exists($roomID, $rooms))
                    {
                        $error = JText::sprintf('COM_THM_ORGANIZER_LS_TP_ROOM_LACKING', $lessonName, $lessonID,
                         date('l', $currentDT), $period, $roomID
                        );
                        if (!in_array($error, $errors))
                        {
                            $errors[] = $error;
                        }
                    }
                    else
                    {
                        if (!isset($calendar[$currentDate][$period][$lessonID]))
                        {
                            $calendar[$currentDate][$period][$lessonID] = array();
                        }
                        if (!in_array($roomID, $calendar[$currentDate][$period][$lessonID]))
                        {
                            $calendar[$currentDate][$period][$lessonID][] = $roomID;
                        }
                    }
                }
            }
            $currentDT = strtotime('+1 day', $currentDT);                
        }
    }
}
