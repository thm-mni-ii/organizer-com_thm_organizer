<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSubject_List
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');
require_once JPATH_COMPONENT . '/helper/teacher.php';
define('NONE', 0);
define('POOL', 1);
define('TEACHER', 2);
define('FIELD', 3);

/**
 * Class creates a model
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSubject_List extends JModelList
{
    public $menuID = null;

    public $program = null;

    public $language = 'de';

    public $subjects = null;

    public $groups = array();

    private $_poolIDs = array();

    private $_uniqueItems = array();

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     */
    public function getItems()
    {
        $programInformation = $this->getProgramInformation();
        $this->programName = $programInformation['name'];

        $subjectEntries = parent::getItems();

        foreach ($subjectEntries AS $index => $entry)
        {
            if (!empty($entry->groupID))
            {
                $this->ensureUnique($subjectEntries, $index, $entry);
            }
            $this->setTeacherProperties($subjectEntries, $index, $entry);
            switch ($this->state->get('groupBy', '0'))
            {
                case POOL:
                    $this->processPoolGroup($entry->groupID);
                    break;
                case TEACHER:
                    $this->processTeacherGroup($entry->groupID);
                    break;
                case FIELD:
                    $this->processFieldGroup($entry->groupID);
                    break;
                default :
                    break;
            }
        }

        if (!empty($this->groups))
        {
            foreach ($this->groups AS $key => $group)
            {
                $this->groups[$key]['bgColor'] = $this->getBGColors($group['bgColor']);
                $this->groups[$key]['textColor'] = $this->getTextColorClass($this->groups[$key]['bgColor']);
            }
            ksort($this->groups);
        }

        return $subjectEntries;
    }

    /**
     * Ensures subject entries only occur once per group
     * 
     * @param   array   &$subjectEntries  the subject entries
     * @param   int     $index            the index being currently iterated
     * @param   object  $entry            the subect entry at the specified index
     * 
     * @return  void
     */
    private function ensureUnique(&$subjectEntries, $index, $entry)
    {
        $item = array('id' => $entry->id, 'groupID' => $entry->groupID);
        if (in_array($item, $this->_uniqueItems))
        {
            unset($subjectEntries[$index]);
            return;
        }
        $this->_uniqueItems[] = $item;
    }

    /**
     * Creates a string of integer values seperated by commas corresponding to
     * the hexadecimal color code
     * 
     * @param   string  $hexCode  the hexidecimal field color
     * 
     * @return  string a comma seperated list of lolor integer values
     */
    private function getBGColors($hexCode)
    {
        if (empty($hexCode))
        {
            $hexCode = 'ffffff';
        }

        $red = hexdec(substr($hexCode, 0, 2));
        $green = hexdec(substr($hexCode, 2, 2));
        $blue = hexdec(substr($hexCode, 4, 2));

        return "$red,$green,$blue";
    }

    /**
     * Gets a text class which states the recommended color brightness the
     * actual value for this class should be set in the included css file.
     * 
     * @param   string  $colorString  a string containing color values seperated
     *                                by commas
     * 
     * @return  'light-text' for dark colors, 'dark-text' for light ones
     */
    private function getTextColorClass($colorString)
    {
        $colorValues = explode(',', $colorString);

        $brightness = (($colorValues[0] * 299) + ($colorValues[1] * 587) + ($colorValues[2] * 114)) / 255000;
        if ($brightness >= 0.6)
        {
            return "dark-text";
        }
        else 
        {
            return "light-text";
        }
    }

    /**
     * Sets teacher properties for subjects
     * 
     * @param   array   &$subjects  the subjects for the given degree program
     * @param   int     $index      the current index being iterated
     * @param   object  $subject    the subject object at the given index
     * 
     * @return  void
     */
    private function setTeacherProperties(&$subjects, $index, $subject)
    {
        $teacherData = THM_OrganizerHelperTeacher::getDataBySubject($subject->id, 1);
        if (empty($teacherData))
        {
            $subjects[$index]->teacherName = '';
            return;
        }

        $defaultName = THM_OrganizerHelperTeacher::getDefaultName($teacherData);
        $groupsName = THM_OrganizerHelperTeacher::getNameFromTHMGroups($teacherData['userID']);
        if (!$groupsName)
        {
            $subjects[$index]->teacherName = $defaultName;
            return;
        }

        $subjects[$index]->teacherName = $groupsName;
        $subjects[$index]->groupsLink
            = THM_OrganizerHelperTeacher::getLink($teacherData['userID'], $teacherData['surname'], $this->state->get('menuID'));
    }

    /**
     * Method to cache the last query constructed.
     *
     * This method ensures that the query is constructed only once for a given state of the model.
     *
     * @return  JDatabaseQuery  A JDatabaseQuery object
     */
    protected function getListQuery()
    {
        $programInformation = $this->getProgramInformation();
        if (empty($programInformation))
        {
            return $this->_db->getQuery(true);
        }

        $menuID = $this->state->get('menuID');
        $languageTag = $this->state->get('languageTag');
        $subjectLink = "'index.php?option=com_thm_organizer&view=subject_details&languageTag=$languageTag&Itemid=$menuID&id='";

        $subjectsQuery = $this->_db->getQuery(true);
        $subjectsQuery->from('#__thm_organizer_subjects AS s');

        $select = array();
        $subjectsQuery->innerJoin('#__thm_organizer_mappings AS m1 ON m1.subjectID = s.id');
        switch ($this->state->get('groupBy', '0'))
        {
            case NONE:
                
                // Non-grouped lists should only have distinct subjects
                $select[] = 'DISTINCT s.id AS id';
                break;
            case POOL:
                $select[] = 's.id AS id';
                $select[] = "m2.poolID AS groupID";
                $subjectsQuery->innerJoin('#__thm_organizer_mappings AS m2 ON m1.parentID = m2.id');
                break;
            case TEACHER:
                $select[] = 's.id AS id';
                $select[] = "st.teacherID AS groupID";
                $subjectsQuery->leftJoin('#__thm_organizer_subject_teachers AS st ON s.id = st.subjectID');
                break;
            case FIELD:
                $select[] = 's.id AS id';
                $select[] = "f.id AS groupID";
                $subjectsQuery->leftJoin('#__thm_organizer_fields AS f ON s.fieldID = f.id');
                break;
            default :
                break;
        }
        $select[] = "s.name_$languageTag AS name";
        $select[] = 's.creditpoints';
        $select[] = 's.externalID';
        $select[] = "CONCAT($subjectLink, s.id) AS subjectLink";
        $subjectsQuery->select($select);
        $subjectsQuery->where("m1.lft > '{$programInformation['lft']}' AND  m1.rgt < '{$programInformation['rgt']}'");

        $search = $this->state->get('search');
        if (!empty($search))
        {
            if (!$this->state->get('groupBy') == TEACHER)
            {
                $subjectsQuery->leftJoin('#__thm_organizer_subject_teachers AS st ON s.id = st.subjectID');
            }
            $subjectsQuery->innerJoin('#__thm_organizer_teachers AS t ON st.teacherID = t.id');
            $subjectsQuery->where($this->getSearch());
        }
        $subjectsQuery->order('name ASC');
        return $subjectsQuery;
    }

    /**
     * Retrieves program information (name and nesting values)
     * 
     * @return  mixed  array on success, otherwise false
     */
    private function getProgramInformation()
    {
        $programID = $this->state->get('programID');
        $languageTag = $this->state->get('languageTag');

        $query = $this->_db->getQuery(true);
        $query->select("CONCAT(p.subject_$languageTag, ' (', d.abbreviation, ' ', p.version, ')') AS name, lft, rgt");
        $query->from('#__thm_organizer_programs AS p');
        $query->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.programID = p.id');
        $query->where("p.id = '$programID'");
        $this->_db->setQuery((string) $query);
        return $this->_db->loadAssoc();
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $app = JFactory::getApplication();
        $app->set('list_limit', '0');
        $programID = $app->getUserStateFromRequest($this->context . '.programID', 'programID');
        $search = $app->getUserStateFromRequest($this->context . '.search', 'search', '');
        $limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '0');
        $languageTag = $app->getUserStateFromRequest($this->context . '.languageTag', 'languageTag');
        $groupBy = $app->getUserStateFromRequest($this->context . '.groupBy', 'groupBy');
        $menuID = $app->getUserStateFromRequest($this->context . '.menuID', 'Itemid');
 
        $params = JFactory::getApplication()->getMenu()->getItem($menuID)->params;
        $menuProgramID = $params->get('programID');
        $menuLanguage = ($params->get('language') == '0')? 'en' : 'de';
        $menuGroupBy = $params->get('groupBy');
 
        $this->setState('programID', empty($programID)? $menuProgramID : $programID);
        $this->setState('search', $search);
        $this->setState('list.limit', $limit);
        $this->setState('languageTag', empty($languageTag)? $menuLanguage : $languageTag);
        $this->setState('groupBy', empty($groupBy)? $menuGroupBy : $groupBy);
        $this->setState('menuID', $menuID);
    }

    /**
     * Sets properties for a pool group
     *
     * @param   int  $poolID  the poolID
     *
     * @return  void
     */
    private function processPoolGroup($poolID)
    {
        $programInformation = $this->getProgramInformation();
        $languageTag = $this->state->get('languageTag');
        $query = $this->_db->getQuery(true);
        $query->select("p.id, m.lft, p.name_{$languageTag} AS name, c.color AS bgColor");
        $query->from('#__thm_organizer_pools AS p');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');
        $query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->where("p.id ='$poolID'");
        $query->where("m.lft > '{$programInformation['lft']}' AND  m.rgt < '{$programInformation['rgt']}'");
        $this->_db->setQuery((string) $query);
        $pools = $this->_db->loadAssocList();
        if (empty($pools))
        {
            return;
        }

        foreach ($pools as $pool)
        {
            if (!in_array($pool['id'], $this->_poolIDs))
            {
                $this->_poolIDs[] = $pool['id'];
                $this->groups[$pool['lft']] = $pool;
            }
        }
    }

    /**
     * Retrieves a group which references a subject's teacher
     *
     * @param   array  $teacherID  the ids of the teacher
     *
     * @return  array  the group according to which the subjects will be output
     */
    private function processTeacherGroup($teacherID)
    {
        $query = $this->_db->getQuery(true);
        $query->select("t.id, c.color AS bgColor");
        $query->from('#__thm_organizer_teachers AS t');
        $query->leftJoin('#__thm_organizer_fields AS f ON t.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->where("t.id ='$teacherID'");
        $this->_db->setQuery((string) $query);
        $teacher = $this->_db->loadAssoc();

        if (empty($teacher))
        {
            return;
        }

        $teacherData = THM_OrganizerHelperTeacher::getDataByID($teacher['id']);
        if (empty($teacherData))
        {
            return;
        }

        if (empty($this->groups[$teacher['id']]))
        {
            $sortName = $teacherData['surname'];
            $sortName .= empty($teacherData['forename'])? '' : $teacherData['forename'];
            $this->groups[$sortName] = $teacher;

            $defaultName = THM_OrganizerHelperTeacher::getDefaultName($teacherData);
            $groupsName = THM_OrganizerHelperTeacher::getNameFromTHMGroups($teacherData['userID']);
            if (!$groupsName)
            {
                $this->groups[$sortName]['name'] = $defaultName;
                return;
            }

            $this->groups[$sortName]['name'] = $groupsName;
            $this->groups[$sortName]['groupsLink'] = THM_OrganizerHelperTeacher::getLink($teacherData['userID'], $teacherData['surname']);
        }
    }

    /**
     * Retrieves an array of groups with references to the subjects grouped
     *
     * @param   array  $fieldID  the id of the field of study
     *
     * @return  array  the group according to which the subjects will be output
     */
    private function processFieldGroup($fieldID)
    {
        $query = $this->_db->getQuery(true);
        $query->select("f.id, f.field AS name, c.color AS bgColor");
        $query->from('#__thm_organizer_fields AS f');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->where("f.id ='$fieldID'");
        $this->_db->setQuery((string) $query);
        $field = $this->_db->loadAssoc();

        if (!empty($field))
        {
            $this->groups[$field['name']] = $field;
        }
    }

    /**
     * Builds the search clause based upon user input
     *
     * @return  string
     */
    private function getSearch()
    {
        $search = '%' . $this->_db->escape($this->state->get('search'), true) . '%';
        $where = "(s.name_de LIKE '$search' OR s.name_en LIKE '$search' OR ";
        $where .= "s.short_name_de LIKE '$search' OR s.short_name_en LIKE '$search' OR ";
        $where .= "s.abbreviation_de LIKE '$search' OR s.abbreviation_en LIKE '$search' OR ";
        $where .= "s.externalID LIKE '$search' OR ";
        $where .= "t.surname LIKE '$search')";
        return $where;
    }
}
