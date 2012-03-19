<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester model
 * @description database persistance file for semesters
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     2.5.0
 */
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelsemester extends JModel
{
    /**
     * save
     *
     * saves the changes to the semester
     *
     * @return int id of the saved semester 0 on failure
     */
    public function save()
    {
        $semesterID = (JRequest::getInt('semesterID'))? JRequest::getInt('semesterID'): 0;
        $organization = addslashes(trim(JRequest::getVar('organization', '', 'post', 'string', JREQUEST_ALLOWRAW )));
        $semester = addslashes(trim(JRequest::getVar('semester', '', 'post', 'string', JREQUEST_ALLOWRAW )));
        if(empty($organization) or empty($semester)) return 0;

        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        if(!$semesterID)
        {
            $insert = "#__thm_organizer_semesters ";
            $insert .= "( organization, semesterDesc) ";
            $insert .= "VALUES ";
            $insert .= "( '$organization', '$semester' ) ";
            $query->insert($insert);
        }
        else
        {
            $query->update("#__thm_organizer_semesters");
            $query->set("organization = '$organization', semesterDesc = '$semester'");
            $query->where("id = '$semesterID';");
        }
        $dbo->setQuery((string)$query);
        $dbo->query();

        $query = $dbo->getQuery(true);
        $query->select("id");
        $query->from("#__thm_organizer_semesters");
        $query->where("organization = '$organization'");
        $query->where("semesterDesc = '$semester'");
        $dbo->setQuery((string)$query);
        $savedID = $dbo->loadResult();

        return (isset($savedID))? $savedID : 0;
    }

    /**
     * delete
     *
     * deletes the given semester and all date which is dependant upon it
     *
     * @return bool true on success false on failure
     */
    public function delete()
    {
        $semesterIDs = JRequest::getVar( 'cid' );
        if(count( $semesterIDs ))
        {
            $dbo = JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $semesterIDs = "'".implode("', '", $semesterIDs)."'";

            $query->delete();
            $query->from("#__thm_organizer_semesters");
            $query->where("id IN ( $semesterIDs )");
            $dbo->setQuery((string)$query);
            $dbo->query();

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_schedules");
            $query->where("sid IN ( $semesterIDs )");
            $dbo->setQuery((string)$query);
            $dbo->query();

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_user_schedules");
            $query->where("sid IN ( $semesterIDs )");
            $dbo->setQuery((string)$query);
            $dbo->query();

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_virtual_schedules");
            $query->where("sid IN ( $semesterIDs )");
            $dbo->setQuery((string)$query);
            $dbo->query();

            $query = $dbo->getQuery(true);
            $query->delete();
            $query->from("#__thm_organizer_virtual_schedules_elements");
            $query->where("sid IN ( $semesterIDs )");
            $dbo->setQuery((string)$query);
            $dbo->query();

            $query = $dbo->getQuery(true);
            $query->select("id");
            $query->from("#__thm_organizer_lessons");
            $query->where("semesterID IN ( $semesterIDs )");
            $dbo->setQuery((string)$query);
            $lessonIDs = $dbo->loadResultArray();

            if(!empty($lessonIDS))
            {
                $lessonIDs = "'".implode("', '", $lessonIDs)."'";

                $query = $dbo->getQuery(true);
                $query->delete();
                $query->from("#__thm_organizer_lessons");
                $query->where("semesterID IN ( $semesterIDs )");
                $dbo->setQuery((string)$query);
                $dbo->query();

                $query = $dbo->getQuery(true);
                $query->delete();
                $query->from("#__thm_organizer_lesson_times");
                $query->where("lessonID IN ( $lessonIDs )");
                $dbo->setQuery((string)$query);
                $dbo->query();

                $query = $dbo->getQuery(true);
                $query->delete();
                $query->from("#__thm_organizer_lesson_teachers");
                $query->where("lessonID IN ( $lessonIDs )");
                $dbo->setQuery((string)$query);
                $dbo->query();
            }
        }
        return true;
    }

}
?>
