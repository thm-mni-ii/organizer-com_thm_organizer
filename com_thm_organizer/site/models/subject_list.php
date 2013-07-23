<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelIndex
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');
require_once JPATH_COMPONENT . DS . 'helper' . DS . 'teacher.php';
define('NONE', 0);
define('POOL', 1);
define('TEACHER', 2);
define('FIELD', 3);

/**
 * Class creates a model
 *
 * @category    Joomla.Component.Site
 * @package     thm_urriculum
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSubject_List extends JModelList
{
    public $menuID = null;

    public $program = null;

    public $language = 'de';

    public $subjects = null;
    
    public $groupBy = NONE;
    
    public $groups = null;

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     */
    public function getItems()
    {
        $items = parent::getItems();
        foreach ($items AS $key => $value)
        {
            $forename = empty($value->forename)? '' : ", $value->forename";
            $items[$key]->teacherName = $value->surname . $forename;
        }

        // Get reference information before changing the query
        $dbo = JFactory::getDbo();
        $subjectIDs = $dbo->loadResultArray(0);
        $programs = $dbo->loadResultArray(3);
        $pools = $dbo->loadResultArray(4);
        $surnames = $dbo->loadResultArray(5);
        $forenames = $dbo->loadResultArray(6);
        $fields = $dbo->loadResultArray(7);
        $THMGroupLinks = $dbo->loadResultArray(9);
        $fieldColors = $dbo->loadResultArray(11);
        $teacherColors = $dbo->loadResultArray(11);
        $poolColors = $dbo->loadResultArray(12);
        $programColors = $dbo->loadResultArray(13);

        switch ($this->state->get('groupBy', '0'))
        {
            case POOL:
                $this->groups = $this->getPoolGroups($subjectIDs, $programs, $pools, $poolColors, $programColors);
                break;
            case TEACHER:
                $this->groups = $this->getTeacherGroups($subjectIDs, $surnames, $forenames, $THMGroupLinks, $teacherColors);
                break;
            case FIELD:
                $this->groups = $this->getFieldGroups($subjectIDs, $fields, $fieldColors);
                break;
            default:
                $this->groups = array();
                break;
        }
        
        $this->programName = $this->getProgramName();

        return $items;
    }

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  array  An array of results.
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
        $dbo = JFactory::getDbo();
		$dbo->setQuery($query, $limitstart, $limit);
		$result = $dbo->loadObjectList('id');
		return $result;
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
        $languageTag = $this->state->get('languageTag');
        $menuID = $this->state->get('menuID');
        $programID = $this->state->get('programID');
        $search = $this->state->get('search');

        $dbo = JFactory::getDbo();

        $nestedQuery = $dbo->getQuery(true);
        $nestedQuery->select("lft, rgt")->from('#__thm_organizer_mappings')->where("programID = '$programID'");
        $dbo->setQuery((string) $nestedQuery);
        $boundaries = $dbo->loadAssoc();
        if (empty($boundaries))
        {
            return $dbo->getQuery(true);
        }

        $subjectLink = "'index.php?option=com_thm_organizer&view=subject_details&languageTag=$languageTag&Itemid=$menuID&id='";
        $groupsLink = "'index.php?option=com_thm_groups&view=profile&Itemid=$menuID&layout=default&gsuid='";

        $select = "DISTINCT s.id, s.name_$languageTag AS name, creditpoints, ";
        $select .= "CONCAT(dp.subject, ' (', d.abbreviation, ' ', dp.version, ')') AS program, p.name_$languageTag AS pool, ";
        $select .= "surname, forename, sf.field, ";
        $select .= "CONCAT($subjectLink, s.id) AS subjectLink, CONCAT($groupsLink, u.id, '&name=', t.surname) AS groupsLink, ";
        $select .= "sc.color AS fieldColor, tc.color AS teacherColor, pc.color AS poolColor, dpc.color AS programColor ";
        $subjectQuery = $dbo->getQuery(true);
        $subjectQuery->select($select);
        $subjectQuery->from('#__thm_organizer_subjects AS s');
        $subjectQuery->innerJoin('#__thm_organizer_mappings AS m1 ON m1.subjectID = s.id');
        $subjectQuery->innerJoin('#__thm_organizer_mappings AS m2 ON m1.parentID = m2.id');
        $subjectQuery->leftJoin('#__thm_organizer_subject_teachers AS st ON s.id = st.subjectID');
        $subjectQuery->leftJoin('#__thm_organizer_teachers AS t ON st.teacherID = t.id');
        $subjectQuery->leftJoin('#__users AS u ON t.username = u.username');
        $subjectQuery->leftJoin('#__thm_organizer_programs AS dp ON m2.programID = dp.id');
        $subjectQuery->leftJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $subjectQuery->leftJoin('#__thm_organizer_pools AS p ON m2.poolID = p.id');
        $subjectQuery->leftJoin('#__thm_organizer_fields AS sf ON s.fieldID = sf.id');
        $subjectQuery->leftJoin('#__thm_organizer_colors AS sc ON sf.colorID = sc.id');
        $subjectQuery->leftJoin('#__thm_organizer_fields AS tf ON t.fieldID = tf.id');
        $subjectQuery->leftJoin('#__thm_organizer_colors AS tc ON tf.colorID = tc.id');
        $subjectQuery->leftJoin('#__thm_organizer_fields AS pf ON p.fieldID = pf.id');
        $subjectQuery->leftJoin('#__thm_organizer_colors AS pc ON pf.colorID = pc.id');
        $subjectQuery->leftJoin('#__thm_organizer_fields AS dpf ON dp.fieldID = dpf.id');
        $subjectQuery->leftJoin('#__thm_organizer_colors AS dpc ON dpf.colorID = dpc.id');
        $subjectQuery->where("m1.lft BETWEEN {$boundaries['lft']} AND {$boundaries['rgt']}");
        $subjectQuery->where("m1.rgt BETWEEN {$boundaries['lft']} AND {$boundaries['rgt']}");
        $subjectQuery->where("st.teacherResp = '1'");
        if (!empty($search))
        {
            $subjectQuery->where($this->getSearch());
        }
        $subjectQuery->order($this->state->get('groupBy') == POOL? 'm2.lft' : 'name');
        return $subjectQuery;
    }

    /**
     * Gets the name of the program selected
     * 
     * @return  string  the name of the program
     */
    private function getProgramName()
    {
        $dbo = JFactory::getDbo();
        $programID = $this->state->get('programID');

        $query = $dbo->getQuery(true);
        $query->select("CONCAT(p.subject, ' (', d.abbreviation, ' ', p.version, ')')");
        $query->from('#__thm_organizer_programs AS p')->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
        $query->where("p.id = '$programID'");
        $dbo->setQuery((string) $query);
        $programName = $dbo->loadResult();
        return empty($programName)? '' : $programName;
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
        
		$params = JFactory::getApplication()->getMenu()->getActive()->params;
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
     * Retrieves an array of groups with references to the subjects grouped
     * 
     * @param   array  $subjectIDs     the ids of the program subjects
     * @param   array  $programs       the program when subjects are directly
     *                                 subordinate in the hierarchy
     * @param   array  $pools          the parent pool
     * @param   array  $poolColors     the hexidecimal code of the pool's color
     * @param   array  $programColors  the hexidecimal code of the program's color
     * 
     * @return  array  the groups according to which the subjects will be output
     */
    private function getPoolGroups($subjectIDs, $programs, $pools, $poolColors, $programColors)
    {        
        $poolGroups = array();
        foreach (array_keys($subjectIDs) as $key)
        {
            if (!empty($pools[$key]))
            {
                $name = $pools[$key];
                if (!isset($poolGroups[$name]))
                {
                    $poolGroups[$name] = array();
                    if (!empty($poolColors[$key]))
                    {
                        $poolGroups[$name]['bgColor'] = $poolColors[$key];
                        $poolGroups[$name]['textColor'] = $this->textColor($poolColors[$key]);
                    }
                }
                $poolGroups[$pools[$key]][] = $subjectIDs[$key];
            }
            elseif (!empty($programs[$key]))
            {
                $name = $programs[$key];
                if (!isset($poolGroups[$name]))
                {
                    $poolGroups[$name] = array();
                    if (!empty($programColors[$key]))
                    {
                        $poolGroups[$name]['bgColor'] = $programColors[$key];
                        $poolGroups[$name]['textColor'] = $this->textColor($programColors[$key]);
                    }
                }
                $poolGroups[$name][] = $subjectIDs[$key];
            }
            if (empty($poolGroups[$name]['bgColor']))
            {
                $poolGroups[$name]['bgColor'] = 'b7bec2';
                $poolGroups[$name]['textColor'] = 'ffffff';
            }
        }
        return $poolGroups;
    }

    /**
     * Retrieves an array of groups with references to the subjects grouped
     * 
     * @param   array  $subjectIDs      the ids of the program subjects
     * @param   array  $surnames        the surnames of responsible teachers
     * @param   array  $forenames       the forenames of responsible teachers
     * @param   array  $THMGroupsLinks  the links to the THM Groups profiles of
     *                                  the responsible teachers
     * @param   array  $teacherColors   the hexidecimal code of the teacher's color
     * 
     * @return  array  the groups according to which the subjects will be output
     */
    private function getTeacherGroups($subjectIDs, $surnames, $forenames, $THMGroupsLinks, $teacherColors)
    {        
        $languageTag = $this->state->get('languageTag');
        $undefined = $languageTag == 'en'? 'No teacher registered as responsible.' : 'Kein Dozent als Verantwortliche eingetragen.';
        $teacherGroups = array();
        foreach ($subjectIDs as $key => $value)
        {
            if (!empty($surnames[$key]))
            {
                $forename = empty($forenames[$key])? '' : ", {$forenames[$key]} ";
                $name = $surnames[$key] . $forename;

                if (!isset($teacherGroups[$name]))
                {
                    $teacherGroups[$name] = array();
                    if (!isset($teacherGroups[$name]['link']))
                    {
                        $teacherGroups[$name]['link'] = $THMGroupsLinks[$key];
                    }
                    if ($teacherColors[$key])
                    {
                        $teacherGroups[$name]['bgColor'] = $teacherColors[$key];
                        $teacherGroups[$name]['textColor'] = $this->textColor($teacherColors[$key]);
                    }
                }
                $teacherGroups[$name][] = $value;
            }
            else
            {
                $name = $undefined;
                if (!isset($teacherGroups[$name]))
                {
                    $teacherGroups[$name] = array();
                }
                $teacherGroups[$name][] = $value;
            }
            if (empty($teacherGroups[$name]['bgColor']))
            {
                $teacherGroups[$name]['bgColor'] = 'b7bec2';
                $teacherGroups[$name]['textColor'] = 'FFFFFF';
            }
        }
        ksort($teacherGroups);
        return $teacherGroups;
    }

    /**
     * Retrieves an array of groups with references to the subjects grouped
     * 
     * @param   array  $subjectIDs   the ids of the program subjects
     * @param   array  $fields       the names of the subject fields
     * @param   array  $fieldColors  the hexidecimal code of the field's color
     * 
     * @return  array  the groups according to which the subjects will be output
     */
    private function getFieldGroups($subjectIDs, $fields, $fieldColors)
    {
        $languageTag = $this->state->get('languageTag');
        $undefined = $languageTag == 'en'? 'Undefined' : 'Nicht festgelegt';
        $fieldGroups = array();
        foreach ($subjectIDs as $key => $value)
        {
            if (!empty($fields[$key]))
            {
                $name = $fields[$key]; 
                if (!isset($fieldGroups[$name]))
                {
                    $fieldGroups[$name] = array();
                    if (!empty($fieldColors[$key]))
                    {
                        $fieldGroups[$name]['bgColor'] = $fieldColors[$key];
                        $fieldGroups[$name]['textColor'] = $this->textColor($fieldColors[$key]);
                    }
                }
                $fieldGroups[$fields[$key]][] = $value;
            }
            else
            {
                $name = $undefined;
                if (!isset($fieldGroups[$name]))
                {
                    $fieldGroups[$name] = array();
                }
                $fieldGroups[$undefined][] = $value;
            }
            if (empty($fieldGroups[$name]['bgColor']))
            {
                $fieldGroups[$name]['bgColor'] = 'b7bec2';
                $fieldGroups[$name]['textColor'] = 'ffffff';
            }
        }
        ksort($fieldGroups);
        return $fieldGroups;
    }

    /**
     * Method to get an appropriate text color based upon the background color
     *
     * @param   string  $color  the background color of the item in hex
     *
     * @return  mixed  JHTML image or an empty string
     */
    private function textColor($color)
    {
        $red = hexdec(substr($color, 0, 2));
        $green = hexdec(substr($color, 2, 2));
        $blue = hexdec(substr($color, 4, 2));
        $median = ($red + $green + $blue) / 3;
        if ($median <= 127)
        {
            return 'FFFFFF';
        }
        else
        {
            return '3d494f';
        }
    }

    /**
     * Builds the search clause based upon user input
     * 
     * @return  string
     */
    private function getSearch()
    {
        $dbo = JFactory::getDbo();
		$search = '%' . $dbo->getEscaped($this->state->get('search'), true) . '%';
        $where = "(s.name_de LIKE '$search' OR s.name_en LIKE '$search' OR ";
        $where .= "s.short_name_de LIKE '$search' OR s.short_name_en LIKE '$search' OR ";
        $where .= "s.abbreviation_de LIKE '$search' OR s.abbreviation_en LIKE '$search' OR ";
        $where .= "s.description_de LIKE '$search' OR s.description_en LIKE '$search' OR ";
        $where .= "s.content_de LIKE '$search' OR s.content_en LIKE '$search' OR ";
        $where .= "s.objective_de LIKE '$search' OR s.objective_en LIKE '$search' OR ";
        $where .= "p.name_de LIKE '$search' OR p.name_en LIKE '$search' OR ";
        $where .= "p.short_name_de LIKE '$search' OR p.short_name_en LIKE '$search' OR ";
        $where .= "p.abbreviation_de LIKE '$search' OR p.abbreviation_en LIKE '$search' OR ";
        $where .= "t.surname LIKE '$search')";
        return $where;
    }
}
