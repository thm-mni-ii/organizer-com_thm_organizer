<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelMonitor_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');

define('DAILY', 1);
define('MIXED', 2);
define('CONTENT', 3);
define('LESSON_LIST', 4);

/**
 * Class compiling a list of saved monitors 
 * 
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelMonitor_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 'r.longname';

    protected $defaultDirection = 'ASC';

    public $displayBehaviour = array();

    /**
     * constructor
     * 
     * @param   array  $config  configurations parameter
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array('r.longname', 'm.ip', 'm.useDefaults', 'm.display', 'm.content');
        }
        $this->displayBehaviour[DAILY] = JText::_('COM_THM_ORGANIZER_DAILY_PLAN');
        $this->displayBehaviour[MIXED] = JText::_('COM_THM_ORGANIZER_MIXED_PLAN');
        $this->displayBehaviour[CONTENT] = JText::_('COM_THM_ORGANIZER_CONTENT_DISPLAY');
        $this->displayBehaviour[LESSON_LIST] = JText::_('COM_THM_ORGANIZER_LESSON_LIST');
        parent::__construct($config);
    }

    /**
     * builds the query used to compile the items for the lsit ouput
     * 
     * @return  object
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $select = "m.id, r.longname, m.ip, m.useDefaults, m.display, m.content, ";
        $parts = array("'index.php?option=com_thm_organizer&view=monitor_edit&id='","m.id");
        $select .= $query->concatenate($parts, "") . " AS link ";
        $query->select($this->state->get("list.select", $select));
        $query->from("#__thm_organizer_monitors AS m");
        $query->leftjoin("#__thm_organizer_rooms AS r ON r.id = m.roomID");

        $this->setSearchFilter($query, array('r.longname', 'm.ip'));
        $this->setValueFilters($query, array('longname', 'ip', 'useDefaults'));
        $this->addDisplayFilter($query);
        $this->addContentFilter($query);

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Adds the filter settings for display behaviour
     *
     * @param   object  &$query  the query object
     *
     * @return  void
     */
    private function addDisplayFilter(&$query)
    {
        $requestDisplay = $this->state->get('filter.display', '');

        if ($requestDisplay === '')
        {
            return;
        }

        $where = "m.display ='$requestDisplay'";

        $params = JComponentHelper::getParams('com_thm_organizer');
        $defaultDisplay = $params->get('display', '');
        $useComponentDisplay = (!empty($defaultDisplay) AND $requestDisplay == $defaultDisplay);
        if ($useComponentDisplay)
        {
            $query->where("( $where OR useDefaults = '1')");
            return;
        }

        $query->where($where);
    }

    /**
     * Adds the filter settings for displayed content
     *
     * @param   object  &$query  the query object
     *
     * @return  void
     */
    private function addContentFilter(&$query)
    {
        $params = JComponentHelper::getParams('com_thm_organizer');
        $requestContent = $this->state->get('filter.content', '');

        if ($requestContent === '')
        {
            return;
        }

        $requestContent = $requestContent == '-1'? '' : $requestContent;
        $where = "m.content ='$requestContent'";

        $defaultContent = $params->get('content', '');
        $useComponentContent = ($requestContent == $defaultContent);
        if ($useComponentContent)
        {
            $query->where("( $where OR useDefaults = '1')");
            return;
        }

        $query->where($where);
    }

    /**
     * Method to overwrite the getItems method in order to set the program name
     *
     * @return  array  an array of objects fulfilling the request criteria
     */
    public function getItems()
    {
        $items = parent::getItems();
        $return = array();
        if (empty($items))
        {
            return $return;
        }

        $params = JComponentHelper::getParams('com_thm_organizer');

        $index = 0;
        foreach ($items as $item)
        {
            // Set default attributes
            if (!empty($item->useDefaults))
            {
                $item->display = $params->get('display');
                $item->content = $params->get('content');
            }

            $return[$index] = array();
            $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['longname'] = JHtml::_('link', $item->link, $item->longname);
            $return[$index]['ip'] = JHtml::_('link', $item->link, $item->ip);
            $tip = JText::_('COM_THM_ORGANIZER_TOGGLE_COMPONENT_SETTINGS');
            $return[$index]['useDefaults'] = $this->getToggle($item->id, $item->useDefaults, 'monitor', $tip);
            $return[$index]['display'] = JHtml::_('link', $item->link, $this->displayBehaviour[$item->display]);
            $return[$index]['content'] = JHtml::_('link', $item->link, $item->content);
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
        $headers['longname'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_ROOM', 'r.longname', $direction, $ordering);
        $headers['ip'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_IP', 'm.ip', $direction, $ordering);
        $headers['useDefaults'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DEFAULT_SETTINGS', 'm.useDefault', $direction, $ordering);
        $headers['display'] = JText::_('COM_THM_ORGANIZER_DISPLAY_BEHAVIOUR');
        $headers['content'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DISPLAY_CONTENT', 'm.content', $direction, $ordering);

        return $headers;
    }
}
