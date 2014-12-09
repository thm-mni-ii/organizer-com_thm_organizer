<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSchedule_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/**
 * Class defining functions to be used for lesson resources
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSchedule_Manager extends THM_CoreModelList
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

        $select = "id, departmentname, semestername, active, creationdate, creationtime ";
        $query->select($select)->from("#__thm_organizer_schedules");

        $this->setSearchFilter($query, array('departmentname', 'semestername'));
        $this->setValueFilters($query, array('departmentname', 'semestername', 'active', 'creationdate', 'creationtime'));

        $this->setOrdering($query);

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
            $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['departmentname'] = $item->departmentname;
            $return[$index]['semestername'] = $item->semestername;
            $return[$index]['active'] = $this->getToggle($item->id, $item->active, 'schedule', JText::_('COM_THM_ORGANIZER_TOGGLE_ACTIVE'));
            $return[$index]['creationdate'] = THM_OrganizerHelperComponent::formatDate($item->creationdate);
            $return[$index]['creationtime'] = THM_OrganizerHelperComponent::formatTime($item->creationtime);
            $index++;
        }
        return $return;
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
        $headers['checkbox'] = '';
        $headers['departmentname'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_ORGANIZATION', 'departmentname', $direction, $ordering);
        $headers['semestername'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_PLANNING_PERIOD', 'semestername', $direction, $ordering);
        $headers['active'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_STATE', 'active', $direction, $ordering);
        $headers['creationdate'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_CREATION_DATE', 'creationdate', $direction, $ordering);
        $headers['creationtime'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_CREATION_TIME', 'creationtime', $direction, $ordering);

        return $headers;
    }
}
