<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        business logic and database abstraction specific to listing schedules
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');
/**
 * Class defining functions to be used for lesson resources
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerModelSchedule_Manager extends THM_CoreListModel
{
    protected $defaultOrdering = 'creationdate';

    protected $defaultDirection = 'DESC';

    /**
     * sets variables and configuration data
     *
     * @param   array  $config  the configuration parameters
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array('departmentname','semestername','creationdate','active');
        }
        parent::__construct($config);
    }

    /**
     * generates the query to be used to fill the output list
     *
     * @return JDatabaseQuery
     */
    protected function getListQuery()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);

        $select = "id, departmentname, semestername, active, creationdate, creationtime, ";
        $parts = array("'index.php?option=com_thm_organizer&view=schedule_edit&id='", "id");
        $select .= $query->concatenate($parts, "") . " AS link";
        $query->select($select)->from("#__thm_organizer_schedules");

        $state = $this->state->get('filter.state', false);
        if ($state !== false)
        {
            $query->where("active = '$state''");
        }

        $semester = $this->state->get('filter.semester', false);
        if ($semester !== false)
        {
            $query->where("semestername = '$semester'");
        }

        $department = $this->getState('filter.department', false);
        if ($department !== false)
        {
            $query->where("departmentname = '$department'");
        }

        $ordering = $dbo->escape($this->state->get('list.ordering', $this->defaultOrdering));
        $direction = $dbo->escape($this->state->get('list.direction', $this->defaultDirection));
        $query->order("$ordering $direction");

        return $query;
    }

    /**
     * Function to feed the data in the table body correctly to the list view
     *
     * @return array consisting of items in the body
     */
    public function getItems()
    {
        $items = parent::getItems();
        $return = array();
        if (empty($items))
        {
            return $return;
        }

        $index = 0;
        foreach ($items as $item)
        {
            $return[$index] = array();
            $return[$index][0] = JHtml::_('grid.id', $index, $item->id);
            $return[$index][1] = JHtml::_('link', $item->link, $item->departmentname);
            $return[$index][2] = JHtml::_('link', $item->link, $item->semestername);
            $return[$index][3] = $this->getToggle($item->id, $item->active);
            $return[$index][4] = JHtml::_('link', $item->link, $item->creationdate);
            $return[$index][5] = JHtml::_('link', $item->link, $item->creationtime);
            $index++;
        }
        return $return;
    }

    /**
     * Generates a toggle for the attribute in question
     *
     * @param   int     $id         the id of the user
     * @param   bool    $value      the value set for the attribute
     *
     * @return  string  a HTML string
     */
    private function getToggle($id, $value)
    {
        if (empty($value))
        {
            $iconClass = 'unfeatured';
            $aClass = 'inactive';
            $textConstant = 'COM_THM_ORGANIZER_SCHEDULE_MANAGER_TOGGLE_ACTIVE_NO';
        }
        else
        {
            $iconClass = 'featured';
            $aClass = '';
            $textConstant = 'COM_THM_ORGANIZER_SCHEDULE_MANAGER_TOGGLE_ACTIVE_YES';
        }
        $toggle = '<div class="button-grp">';
        $toggle .= '<a class="btn btn-micro ' . $aClass . ' hasTooltip" title="' . JText::_($textConstant) . '"';
        $toggle .= 'href="index.php?option=com_thm_organizer&task=schedule.activatee&id=' . $id . '&value=' . $value . '">';
        $toggle .= '<i class="icon-' . $iconClass . '"></i>';
        $toggle .= '</a>';
        $toggle .= '</div>';
        return $toggle;
    }


    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction = $this->state->get('list.direction', $this->defaultDirection);

        $headers = array();
        $headers[] = '';
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_ORGANIZATION', 'departmentname', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_PLANNING_PERIOD', 'semestername', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_STATE', 'active', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_CREATION_DATE', 'creationdate', $direction, $ordering);
        $headers[] = JText::_('COM_THM_ORGANIZER_CREATION_TIME');

        return $headers;
    }

    /**
     * retrieves an array of named semesters from the database
     *
     * @return array filled with semester names or empty
     */
    private function getDepartments()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT departmentname AS name");
        $query->from("#__thm_organizer_schedules");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $departments = $dbo->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        return (count($departments))? $departments : array();
    }

    /**
     * Retrieves an array of named semesters from the database
     *
     * @return array filled with semester names or empty
     */
    private function getSemesters()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT semestername AS name");
        $query->from("#__thm_organizer_schedules");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $semesters = $dbo->loadAssocList();
            return (count($semesters))? $semesters : array();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }
}
