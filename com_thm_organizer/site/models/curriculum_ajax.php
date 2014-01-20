<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelCurriculum
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT . DS . 'helper' . DS . 'teacher.php';

/**
 * Class provides methods for building a model of the curriculum in JSON format
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelCurriculum_Ajax extends JModel
{
    private $_scheduleID;

    private $_scheduleLink;

    private $_schedule;

    /**
     * Constructor to set up the class variables and call the parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
 
    /**
     * Method to select the Tree of the current major
     *
     * @return  string  the json encoded string modeling the curriculum
     */
    public function getCurriculum()
    {
        $programID = JRequest::getInt('programID');

        if (empty($programID))
        {
            return '';
        }

        $languageTag = JRequest::getString('languageTag', 'de');

        // Get the major in order to build the complete label of a given major/curriculum
        $program = $this->getProgramData($programID);
        $this->setScheduleData($program->name);
        $program->children = $this->getChildren($program->lft, $program->rgt, $languageTag);

        if (empty($program->children))
        {
            return '';
        }
        else
        {
            return json_encode($program);
        }
    }

    /**
     * Retrieves pool specific information
     *
     * @param   int     $poolID   the id of the pool being sought
     * @param   string  $langTag  the current display language
     *
     * @return  mixed  The return value or null if the query failed.
     */
   private function getPoolData($poolID, $langTag)
   {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $select = "p.id, lsfID, hisID, externalID, abbreviation_$langTag AS abbreviation, ";
        $select .= "name_$langTag AS name, minCrP, maxCrP, color";
        $query->select($select);
        $query->from('#__thm_organizer_pools AS p');
        $query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->where("p.id = '$poolID'");
        $dbo->setQuery((string) $query);
        $poolData = $dbo->loadObject();
        if (empty($poolData->color))
        {
            $poolData->color = 'ffffff';
        }
        $poolData->children = array();
        return $poolData;
   }

    /**
     * Method to get program information
     *
     * @param   int  $programID  the id of the program being modelled
     *
     * @return  array
     */
    private function getProgramData($programID)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $select = "CONCAT(p.subject, ' (', d.abbreviation, ' ', p.version, ')') AS name, ";
        $select .= "m.id AS mappingID, m.lft, m.rgt";
        $query->select($select);
        $query->from('#__thm_organizer_programs AS p');
        $query->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON p.id = m.programID');
        $query->where("p.id = '$programID'");
        $dbo->setQuery((string) $query);
        return $dbo->loadObject();
    }

    /**
     * Retrieves subject specific information
     *
     * @param   int     $subjectID  the id of the subject being sought
     * @param   string  $langTag    the current display language
     *
     * @return  mixed  The return value or null if the query failed.
     */
    private function getSubjectData($subjectID, $langTag)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $select = "s.id, lsfID, hisID, externalID, abbreviation_$langTag AS abbreviation, ";
        $select .= "name_$langTag AS name, creditpoints AS maxCrP, color, ";
        $select .= "CONCAT('index.php?option=com_thm_organizer&view=subject_details&languageTag=', ";
        $select .= "'$langTag', '&id=', s.id) AS link";
        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_fields AS f ON s.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->where("s.id = '$subjectID'");
        $dbo->setQuery((string) $query);
        $subjectData = $dbo->loadObject();
        if (empty($subjectData))
        {
            return null;
        }
        if (empty($subjectData->color))
        {
            $subjectData->color = 'ffffff';
        }
        $subjectData->link = JRoute::_($subjectData->link);
        if (!empty($subjectData->externalID) AND !empty($this->_schedule))
        {
            foreach ($this->_schedule->subjects AS $subjectID => $subject)
            {
                if ($subject->subjectNo == $subjectData->externalID)
                {
                    $subjectData->scheduleLink = $this->_scheduleLink . "&subjectID=$subjectID";
                    break;
                }
            }
        }
        $this->setTeacherProperties($subjectData);
        return $subjectData;
    }

    /**
     * Retrieves program children recursively
     *
     * @param   int     $lft      the left boundary of the program in the nested table
     * @param   int     $rgt      the right boundary of the program in the nested table
     * @param   string  $langTag  the current display language
     *
     * @return  array  empty if no child data exists
     */
    public function getChildren($lft, $rgt, $langTag = 'de')
    {
        $dbo = JFactory::getDbo();
        $children = array();

        $mappingsQuery = $dbo->getQuery(true);
        $mappingsQuery->select('*')->from('#__thm_organizer_mappings');
        $mappingsQuery->where("lft > '$lft'");
        $mappingsQuery->where("rgt < '$rgt'");
        $mappingsQuery->order('lft');
        $dbo->setQuery((string) $mappingsQuery);
        $mappings = $dbo->loadAssocList();
        if (empty($mappings))
        {
            return $children;
        }

        $nodes = array();
        foreach ($mappings AS $mapping)
        {
            $parent =& $children;
            if ($mapping['level'] > 1)
            {
                for ($i = 1; $i < $mapping['level']; $i++)
                {
                    $parent =& $parent[$nodes[$i]]->children;
                }
            }
            if (isset($mapping['poolID']))
            {
                $nodes[(int) $mapping['level']] = (int) $mapping['ordering'];
                $poolData = $this->getPoolData($mapping['poolID'], $langTag);
                $poolData->mappingID = $mapping['id'];
                $poolData->lastChildOrder = $this->lastChildOrder($poolData->mappingID);
                $parent[(int) $mapping['ordering']] = $poolData;
            }
            elseif (isset($mapping['subjectID']))
            {
                $subjectData = $this->getSubjectData($mapping['subjectID'], $langTag);
                $subjectData->mappingID = $mapping['id'];
                $parent[(int) $mapping['ordering']] = $subjectData;
            }
 
        }
        return $children;
    }

    /**
     * Retrieves the ordering of the last direct child element
     * 
     * @param   int  $mappingID  the id of the mapped element
     * 
     * @return  int  the last child element's ordering value
     */
    private function lastChildOrder($mappingID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('MAX(ordering)')->from('#__thm_organizer_mappings')->where("parentID = '$mappingID'");
        $dbo->setQuery((string) $query);
        return $dbo->loadResult();
    }

    /**
     * Checks for and sets schedule data if an applicable schedule is found
     *
     * @param   string  $programName  the name of the program being modelled
     *
     * @return  void
     */
    private function setScheduleData($programName)
    {
        $date = date('Y-m-d');
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, schedule')->from('#__thm_organizer_schedules');
        $query->where("startdate <= '$date'")->where("enddate >= '$date'")->where("active = '1'");
        $dbo->setQuery((string) $query);
        $currentSchedules = $dbo->loadAssocList();

        foreach ($currentSchedules as $currentSchedule)
        {
            $schedule = json_decode($currentSchedule['schedule']);
            foreach ((array) $schedule->degrees as $program)
            {
                if ($program->name == $programName)
                {
                    $this->_scheduleID = $currentSchedule['id'];
                    $this->_scheduleLink = "index.php?option=com_thm_organizer&view=scheduler";
                    $this->_scheduleLink .= "&scheduleID={$currentSchedule['id']}";
                    $this->_schedule = $schedule;
                    return;
                }
            }
        }
    }

    /**
     * Sets subject properties relating to the responsible teacher
     *
     * @param   object  &$subjectData  an object containing subject data
     *
     * @return  void
     */
    private function setTeacherProperties(&$subjectData)
    {
        $teacherData = THM_OrganizerHelperTeacher::getData($subjectData->id, 1);
        if (empty($teacherData))
        {
            return;
        }

        $defaultName = THM_OrganizerHelperTeacher::getDefaultName($teacherData);
        if (!empty($teacherData['userID']))
        {
            $subjectData->teacherID = $teacherData['userID'];
            $teacherName = THM_OrganizerHelperTeacher::getNameFromTHMGroups($teacherData['userID']);
            if (empty($teacherName))
            {
                $subjectData->teacherName = $defaultName;
                return;
            }
            $subjectData->teacherPicture = THM_OrganizerHelperTeacher::getPicture($teacherData['userID']);
            $subjectData->teacherName = $teacherName;
            $subjectData->teacherProfileLink = THM_OrganizerHelperTeacher::getLink($teacherData['userID'], $teacherData['surname']);
        }
        else
        {
            $subjectData->teacherName = $defaultName;
        }
        if (!empty($teacherData['gpuntisID']) AND !empty($this->_scheduleLink))
        {
            $subjectData->teacherScheduleLink
                = $this->_scheduleLink . "&teacherID={$teacherData['gpuntisID']}";
        }
    }
}
