<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelCategory_Manager
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

        $this->setSearchFilter($query, array('ec.title', 'ec.description'));
        $this->setIDFilter($query, 'ec.id', array('title'));
        $this->setValueFilters($query, array('global', 'reserves', 'ec.contentCatID'));

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
            $return[$index]['title'] = JHtml::_('link', $item->link, $item->ectitle);
            $globalTip = JTEXT::_('COM_THM_ORGANIZER_TOGGLE_GLOBAL');
            $return[$index]['global'] = $this->getToggle($item->id, $item->global, 'category', $globalTip, 'global');
            $reservesTip = JTEXT::_('COM_THM_ORGANIZER_TOGGLE_RESERVES');
            $return[$index]['reserves'] = $this->getToggle($item->id, $item->reserves, 'category', $reservesTip, 'reserves');
            $return[$index]['ec.contentCatID'] = JHtml::_('link', $item->link, $item->cctitle);
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
        $headers['title'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'ec.title', $direction, $ordering);
        $headers['global'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_GLOBAL', 'ec.global', $direction, $ordering);
        $headers['reserves'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_RESERVES', 'ec.reserves', $direction, $ordering);
        $headers['ec.contentCatID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_CONTENT_CATEGORY', 'cc.title', $direction, $ordering);

        return $headers;
    }
}
