<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;
define('DAILY', 1);
define('MIXED', 2);
define('CONTENT', 3);
define('LESSON_LIST', 4);

require_once 'list.php';

use THM_OrganizerHelperHTML as HTML;

/**
 * Class retrieves information for a filtered set of monitors.
 */
class THM_OrganizerModelMonitor_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'r.longname';

    protected $defaultDirection = 'asc';

    public $displayBehaviour = [];

    /**
     * constructor
     *
     * @param array $config configurations parameter
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['r.longname', 'm.ip', 'm.useDefaults', 'm.display', 'm.content'];
        }

        $this->displayBehaviour[DAILY]       = Languages::_('THM_ORGANIZER_DAILY_PLAN');
        $this->displayBehaviour[MIXED]       = Languages::_('THM_ORGANIZER_MIXED_PLAN');
        $this->displayBehaviour[CONTENT]     = Languages::_('THM_ORGANIZER_CONTENT_DISPLAY');
        $this->displayBehaviour[LESSON_LIST] = Languages::_('THM_ORGANIZER_LESSON_LIST');
        parent::__construct($config);
    }

    /**
     * builds the query used to compile the items for the lsit ouput
     *
     * @return object
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $select = 'm.id, r.longname, m.ip, m.useDefaults, m.display, m.content, ';
        $parts  = ["'index.php?option=com_thm_organizer&view=monitor_edit&id='", 'm.id'];
        $select .= $query->concatenate($parts, '') . ' AS link ';
        $query->select($this->state->get('list.select', $select));
        $query->from('#__thm_organizer_monitors AS m');
        $query->leftJoin('#__thm_organizer_rooms AS r ON r.id = m.roomID');

        $this->setSearchFilter($query, ['r.longname', 'm.ip']);
        $this->setValueFilters($query, ['longname', 'ip', 'useDefaults']);
        $this->addDisplayFilter($query);
        $this->addContentFilter($query);

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Adds the filter settings for display behaviour
     *
     * @param object &$query the query object
     *
     * @return void
     */
    private function addDisplayFilter(&$query)
    {
        $requestDisplay = $this->state->get('filter.display', '');

        if ($requestDisplay === '') {
            return;
        }

        $where = "m.display ='$requestDisplay'";

        $params              = OrganizerHelper::getParams();
        $defaultDisplay      = $params->get('display', '');
        $useComponentDisplay = (!empty($defaultDisplay) and $requestDisplay == $defaultDisplay);
        if ($useComponentDisplay) {
            $query->where("( $where OR useDefaults = '1')");

            return;
        }

        $query->where($where);
    }

    /**
     * Adds the filter settings for displayed content
     *
     * @param object &$query the query object
     *
     * @return void
     */
    private function addContentFilter(&$query)
    {
        $params         = OrganizerHelper::getParams();
        $requestContent = $this->state->get('filter.content', '');

        if ($requestContent === '') {
            return;
        }

        $requestContent = $requestContent == '-1' ? '' : $requestContent;
        $where          = "m.content ='$requestContent'";

        $defaultContent      = $params->get('content', '');
        $useComponentContent = ($requestContent == $defaultContent);
        if ($useComponentContent) {
            $query->where("( $where OR useDefaults = '1')");

            return;
        }

        $query->where($where);
    }

    /**
     * Method to overwrite the getItems method in order to set the program name
     *
     * @return array  an array of objects fulfilling the request criteria
     */
    public function getItems()
    {
        $items  = parent::getItems();
        $return = [];

        if (empty($items)) {
            return $return;
        }

        $params       = OrganizerHelper::getParams();
        $displayParam = $params->get('display');
        $contentParam = $params->get('content');
        $index        = 0;

        foreach ($items as $item) {
            // Set default attributes
            if (!empty($item->useDefaults)) {
                $item->display = $displayParam;
                $item->content = $contentParam;
            }

            $return[$index]                = [];
            $return[$index]['checkbox']    = HTML::_('grid.id', $index, $item->id);
            $return[$index]['longname']    = HTML::_('link', $item->link, $item->longname);
            $return[$index]['ip']          = HTML::_('link', $item->link, $item->ip);
            $tip                           = Languages::_('THM_ORGANIZER_TOGGLE_COMPONENT_SETTINGS');
            $return[$index]['useDefaults'] = $this->getToggle($item->id, $item->useDefaults, 'monitor', $tip);
            $return[$index]['display']     = HTML::_('link', $item->link, $this->displayBehaviour[$item->display]);
            $return[$index]['content']     = HTML::_('link', $item->link, $item->content);
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
        $ordering  = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction = $this->state->get('list.direction', $this->defaultDirection);
        $headers   = [];

        $headers['checkbox']    = '';
        $headers['longname']    = HTML::sort('ROOM', 'r.longname', $direction, $ordering);
        $headers['ip']          = HTML::sort('IP', 'm.ip', $direction, $ordering);
        $headers['useDefaults'] = HTML::sort('DEFAULT_SETTINGS', 'm.useDefaults', $direction, $ordering);
        $headers['display']     = Languages::_('THM_ORGANIZER_DISPLAY_BEHAVIOUR');
        $headers['content']     = HTML::sort('DISPLAY_CONTENT', 'm.content', $direction, $ordering);

        return $headers;
    }
}
