<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        model schedule_manager
 * @description database abstraction file for the schedule manager view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.modellist');
class thm_organizersModelschedule_manager extends JModelList
{
    public $semesterName = '';
    public $semesters = null;

    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] =
                array(
                    'filename', 'sch.filename',
                    'state', 'sch.active',
                    'semester', 'sch.sid',
                    'plantype', 'p.plantype',
                    'creationdate', 'sch.creationdate',
                );
        }
        parent::__construct($config);
        $this->semesters = $this->getSemesters();
        $this->plantypes = $this->getPlantypes();
    }

    protected function getListQuery()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);

        $select = "sch.id, sch.filename, sch.active, sch.description, p.plantype, ";
        $select .= "DATE_FORMAT(sch.startdate, '%d.%m.%Y') AS startdate, ";
        $select .= "DATE_FORMAT(sch.enddate, '%d.%m.%Y') AS enddate, ";
        $select .= "DATE_FORMAT(sch.creationdate, '%d.%m.%Y') AS creationdate, ";
        $select .= "sch.sid, CONCAT( sem.organization, ' - ', sem.semesterDesc ) AS semester";
        $query->select($this->getState("list.select", $select));
        $query->from("#__thm_organizer_schedules AS sch");
        $query->innerJoin("#__thm_organizer_plantypes AS p ON p.id = sch.plantypeID");
        $query->leftjoin('#__thm_organizer_semesters AS sem ON sem.id = sch.sid');

        $search = $this->getState('filter.search');
        if($search & $search != JText::_('COM_THM_ORGANIZER_SEARH_CRITERIA'))
        {
            $search = $dbo->Quote("%{$dbo->escape($search, true)}%");
            $query->where('sch.filename LIKE '.$search);
        }

        $state = $this->getState('filter.state');
        if($state === '0') $query->where("sch.active IS NULL");
        if($state === '1') $query->where("sch.active IS NOT NULL");

        $semester = $this->getState('filter.semester');
        if(is_numeric($semester)) $query->where("sch.sid = $semester");

        $plantype = $this->getState('filter.type');
        if(is_numeric($plantype)) $query->where("sch.plantypeID = $plantype");

        $orderby = $dbo->getEscaped($this->getState('list.ordering', 'sch.creationdate'));
        $direction = $dbo->getEscaped($this->getState('list.direction', 'ASC'));
        $query->order("$orderby $direction");

        return $query;
    }

    /**
     *
     * @param string $ordering
     * @param string $direction
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $state);

        $referred = strpos($_SERVER['HTTP_REFERER'], 'view=semester_manager');
        $semesterID = (is_numeric($referred))?
            JRequest::getCmd('semesterID') : $this->getUserStateFromRequest($this->context.'.filter.semester', 'filter_semester');
        $this->setState('filter.semester', $semesterID);
        $this->setState('semesterName', $this->getSemesterName($semesterID));

        $type = $this->getUserStateFromRequest($this->context.'.filter.type', 'filter_type');
        $this->setState('filter.type', $type);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * getPlantypes
     *
     * retrieves an array of saved plantypes from the database
     *
     * @return array
     */
    private function getPlantypes()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("id, plantype AS name");
        $query->from("#__thm_organizer_plantypes");
        $dbo->setQuery((string)$query);
        $plantypes = $dbo->loadAssocList();
        if(count($plantypes))
        {
            foreach($plantypes as $k => $type)
                $plantypes[$k]['name'] = JText::_($type['name']);
            return $plantypes;
        }
        else return array();
    }

    /**
     * getSemesters
     *
     * retrieves an array of saved semesters from the database
     *
     * @return array
     */
    private function getSemesters()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("id, CONCAT( organization, ' - ', semesterDesc ) AS name");
        $query->from("#__thm_organizer_semesters");
        $dbo->setQuery((string)$query);
        $semesters = $dbo->loadAssocList();
        return (count($semesters))? $semesters : array();
    }

    /**
     * getSemesterName
     *
     * retrieves the name of a given semester
     *
     * @return string the name of the semester
     */
    private function getSemesterName($semesterID)
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("CONCAT( organization, ' - ', semesterDesc ) AS semesterName");
        $query->from("#__thm_organizer_semesters");
        $query->where("id = '$semesterID'");
        $dbo->setQuery((string)$query);
        return $dbo->loadResult();
    }
}