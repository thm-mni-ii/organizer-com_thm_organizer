<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        category manager model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Melih Cakir, <melih.cakir@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');

/**
 * Class compiling a list of saved event categories
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerModelCategory_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 'ec.title';

    protected $defaultDirection = 'ASC';

    /**
     * sets variables and configuration data
     *
     * @param   array  $config  the configuration parameters
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array('ec.title','ec.global','ec.reserves','cc.title');
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

        $select = 'ec.id AS id, ec.title AS ectitle, ec.global, ec.reserves, cc.title AS cctitle, ';
        $parts = array("'index.php?option=com_thm_organizer&view=category_edit&id='", "ec.id");
        $select .= $query->concatenate($parts, "") . " AS link";
        $query->select($select);
        $query->from('#__thm_organizer_categories AS ec');
        $query->innerJoin('#__categories AS cc ON ec.contentCatID = cc.id');

        $search = $this->state->get('filter.search');
        if (!empty($search))
        {
            $query->where("(ec.title LIKE '%" . implode("%' OR ec.title LIKE '%", explode(' ', $search)) . "%')");
        }

        $global = $this->state->get('filter.global');
        if ($global === '0')
        {
            $query->where("ec.global = 0");
        }
        if ($global === '1')
        {
            $query->where("ec.global = 1");
        }

        $reserves = $this->state->get('filter.reserves');
        if ($reserves === '0')
        {
            $query->where("ec.reserves = 0");
        }
        if ($reserves === '1')
        {
            $query->where("ec.reserves = 1");
        }

        $contentCatID = $this->state->get('filter.content_cat');
        if (!empty($contentCatID) and $contentCatID != '*')
        {
            $query->where("ec.contentCatID = '$contentCatID'");
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
            $return[$index][1] = JHtml::_('link', $item->link, $item->ectitle);
            $globalTip = JTEXT::_('COM_THM_ORGANIZER_CATEGORY_MANAGER_TOGGLE_GLOBAL');
            $return[$index][2] = $this->getToggle($item->id, $item->global, 'category', $globalTip, 'global');
            $reservesTip = JTEXT::_('COM_THM_ORGANIZER_CATEGORY_MANAGER_TOGGLE_RESERVES');
            $return[$index][3] = $this->getToggle($item->id, $item->reserves, 'category', $reservesTip, 'reserves');
            $return[$index][4] = JHtml::_('link', $item->link, $item->cctitle);
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
        $headers[] = '';
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'ec.title', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_GLOBAL', 'ec.global', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_RESERVES', 'ec.reserves', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_CONTENT_CATEGORY', 'cc.title', $direction, $ordering);

        return $headers;
    }
}
