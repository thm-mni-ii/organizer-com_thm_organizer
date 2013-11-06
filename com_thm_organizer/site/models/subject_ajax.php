<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSubject_Ajax
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Class provides methods for building a model of the curriculum in JSON format
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSubject_Ajax extends JModel
{
    /**
     * Constructor to set up the class variables and call the parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Call DB and select all pool names.
     * 
     * @return  echo  json array
     */
    public function loadpoolnames()
    {
        $sql = "SELECT p.`id`, p.`subject`, p.`version`, p.`lsfFieldID`, m.`lft`, m.`rgt`, p.`degreeID`
                  FROM jos_thm_organizer_mappings m, jos_thm_organizer_programs p
                  WHERE m.programID IS NOT NULL 
                  AND m.programID = p.id
                  ORDER BY p.`subject`";

        $result = mysql_query($sql);

        if (!$result) 
        {
            echo 'Query error : ' . mysql_error();
        }

        $pools = array();
        while ($row = mysql_fetch_object($result)) 
        {
            $pools[] = array(
                'id' => $row->id,
                'subject' => $row->subject,
                'version' => $row->version,
                'lsfFieldID' => $row->lsfFieldID,
                'lft' => $row->lft,
                'rgt' => $row->rgt,
                'degreeID' => $row->degreeID
            );
        }

        echo json_encode($pools);
    }

    /**
     * Call DB and select teachers.
     * 
     * @return  echo  json array
     */
    public function loadTeacherNames()
    {
       $sql = "SELECT `id`, `forename`, `surname` 
               FROM `jos_thm_organizer_teachers`
               ORDER BY `surname`";

       $result = mysql_query($sql);

        if (!$result) 
        {
            echo 'Query error : ' . mysql_error();
        }

        $teachers = array();
        while ($row = mysql_fetch_object($result)) 
        {
            $teachers[] = array(
                'id' => $row->id,
                'name' => $row->surname . ' ' . $row->forename
            );
        }

        echo json_encode($teachers);
    }

    /**
     * Call DB and select subjects with lft and rgt.
     *  
     * @param   string  $lft  lft
     * @param   string  $rgt  rgt
     * 
     * @return  echo  json array
     */
    public function loadSubjects($lft, $rgt)
    {
        $sql = "SELECT DISTINCT s.`id`, s.`name_de`, s.`name_en`, s.`abbreviation_de`, s.`abbreviation_en`, s.`externalID` 
                  FROM jos_thm_organizer_mappings m, jos_thm_organizer_subjects s  
                  WHERE m.lft >= " . $lft . " AND m.rgt <= " . $rgt . "
                  AND m.subjectID = s.id
                  ORDER BY s.`name_de`";

        $result = mysql_query($sql);

        if (!$result) 
        {
            echo 'Query error : ' . mysql_error();
        }

        $subjects = array();
        while ($row = mysql_fetch_object($result)) 
        {
            $subjects[] = array(
                'id' => $row->id,
                'name_de' => $row->name_de,
                'name_en' => $row->name_en,
                'abbreviation_de' => $row->abbreviation_de,
                'abbreviation_en' => $row->abbreviation_en,
                'externalID' => $row->externalID
            );
        }

        echo json_encode($subjects);
    }

    /**
     * Call DB and select subjects with teacher id.
     *  
     * @param   string  $teacherID  ID from teacher.
     * 
     * @return  echo  json array
     */
    public function loadSubjectsFromTeacher($teacherID)
    {
        $sql = "SELECT DISTINCT s.`id`, s.`name_de`, s.`name_en`, s.`abbreviation_de`, s.`abbreviation_en`, s.`externalID`
                FROM jos_thm_organizer_subject_teachers s_t, jos_thm_organizer_subjects s 
                WHERE s_t.teacherID = " . $teacherID . "
                AND s_t.subjectID = s.id";

        $result = mysql_query($sql);

        if (!$result) 
        {
            echo 'Query error : ' . mysql_error();
        }

        $subjects = array();
        while ($row = mysql_fetch_object($result)) 
        {
            $subjects[] = array(
                'id' => $row->id,
                'name_de' => $row->name_de,
                'name_en' => $row->name_en,
                'abbreviation_de' => $row->abbreviation_de,
                'abbreviation_en' => $row->abbreviation_en,
                'externalID' => $row->externalID
            );
        }

        echo json_encode($subjects);
    }

    public function getSubjects($resourceID, $byResource = 'program')
    {
        $lang = explode('-', JFactory::getLanguage()->getTag());
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $select = "DISTINCT s.id, s.name_{$lang[0]} AS abbreviation, s.abbreviation_{$lang[0]}, s.externalID";
        $query->select($select)->from('#__thm_organizer_subjects AS s');
        switch ($byResource)
        {
            case 'teacher':
                $query->innerJoin
                
        }
        $sql = "SELECT 
                FROM jos_thm_organizer_subject_teachers s_t, jos_thm_organizer_subjects s 
                WHERE s_t.teacherID = " . $teacherID . "
                AND s_t.subjectID = s.id";
        
    }
}
