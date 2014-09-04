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
jimport('joomla.application.component.modellist');

/**
 * Class compiling a list of saved event categories
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerModelCategory_Manager extends JModelList
{
    /**
     * An associative array containing information about saved categories
     *
     * @var array
     */
    public $contentCategories = null;

    /**
     * sets variables and configuration data
     *
     * @param   array  $config  the configuration parameters
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'title', 'ectitle',
                'global', 'global',
                'reserves', 'reserves',
                'cctitle', 'content_cat'
            );
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
        $parts = array("'index.php?option=com_thm_organizer&view=category_edit&categoryID='", "ec.id");
        $select .= $query->concatenate($parts, "") . " AS link";
        $query->select($this->getState("list.select", $select));
        $query->from('#__thm_organizer_categories AS ec');
        $query->innerJoin('#__categories AS cc ON ec.contentCatID = cc.id');

        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            $query->where("(ec.title LIKE '%" . implode("%' OR ec.title LIKE '%", explode(' ', $search)) . "%')");
        }

        $global = $this->getState('filter.global');
        if ($global === '0')
        {
            $query->where("ec.global = 0");
        }
        if ($global === '1')
        {
            $query->where("ec.global = 1");
        }

        $reserves = $this->getState('filter.reserves');
        if ($reserves === '0')
        {
            $query->where("ec.reserves = 0");
        }
        if ($reserves === '1')
        {
            $query->where("ec.reserves = 1");
        }

        $contentCatID = $this->getState('filter.content_cat');
        if (!empty($contentCatID) and $contentCatID != '*')
        {
            $query->where("ec.contentCatID = '$contentCatID'");
        }

        $orderby = $dbo->escape($this->getState('list.ordering', 'ectitle'));
        $direction = $dbo->escape($this->getState('list.direction'));
        $query->order("$orderby $direction");

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
            $url = "index.php?option=com_thm_organizer&view=category_edit&categoryID=$item->id";
            $urlAttribs = array('class' => 'jgrid hasTip');
            $return[$index] = array();
            $return[$index][0] = JHtml::_('grid.id', $index, $item->id);
            $return[$index][1] = JHtml::_('link', $url, $item->ectitle);
            $globalCLass = empty($item->global)? 'unpublish' : 'publish';
            $globalValue = '<span class="state ' . $globalCLass . '"></span>';
            $return[$index][2] = JHtml::_('link', $url, $globalValue, $urlAttribs);
            $reservesCLass = empty($item->reserves)? 'unpublish' : 'publish';
            $reservesValue = '<span class="state ' . $reservesCLass . '"></span>';
            $return[$index][3] = JHtml::_('link', $url, $reservesValue, $urlAttribs);
            $return[$index][4] = JHtml::_('link', $url, $item->cctitle);
            $index++;
        }
        return $return;
    }

    /**
     * Generates a toggle for the attribute in question
     *
     * @param   int     $id         the id of the user
     * @param   bool    $value      the value set for the attribute
     * @param   string  $attribute  the attribute being toggled
     *
     * @return  string  a HTML string
     */
    private function getToggle($id, $value, $attribute)
    {
        $spanClass = empty($value)? 'unpublish' : 'publish';
        $toggle = '<a class="jgrid hasTip" title="' . JText::_('COM_THM_ORGANIZER_USM_ROLE_TOGGLE') . '"';
        $toggle .= 'href="index.php?option=com_thm_organizer&task=category.toggle&attribute=' . $attribute . '&id=' . $id . '&value=' . $value . '">';
        $toggle .= '<span class="state ' . $spanClass . '"></span>';
        $toggle .= '</a>';
        return $toggle;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering = $this->state->get('list.ordering');
        $direction = $this->state->get('list.direction');

        $headers = array();
        $headers[] = '';
        $headers[] = JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_NAME'), 'ectitle', $direction, $ordering);
        $headers[] = JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_CAT_GLOBAL'), 'global', $direction, $ordering);
        $headers[] = JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_CAT_RESERVES'), 'reserves', $direction, $ordering);
        $headers[] = JHtml::_('grid.sort', JText::_('COM_THM_ORGANIZER_CAT_CONTENT_CATEGORY'), 'cctitle', $direction, $ordering);

        return $headers;
    }

    /**
     * takes user filter parameters and adds them to the view state
     *
     * @param   string  $ordering   the filter parameter to be used  for ordering
     * @param   string  $direction  the direction in which results are to be ordered
     *
     * @return void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $dbo = JFactory::getDbo();

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $global = $dbo->escape($this->getUserStateFromRequest($this->context . '.filter.global', 'filter_global'));
        $this->setState('filter.global', $global);

        $reserves = $dbo->escape($this->getUserStateFromRequest($this->context . '.filter.reserves', 'filter_reserves'));
        $this->setState('filter.reserves', $reserves);

        $contentCat = $dbo->escape($this->getUserStateFromRequest($this->context . '.filter.content_cat', 'filter_content_cat'));
        $this->setState('filter.content_cat', $contentCat);

        $orderBy = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'ectitle');
        $this->setState('list.ordering', $orderBy);

        $direction = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC');
        $this->setState('list.direction', $direction);

        parent::populateState($ordering, $direction);
    }

    /**
     * Fills the filter array with filter items
     *
     * @return  array  an array of filters
     */
    public function getFilters()
    {
        $filters = array();
        $filters[] = $this->getCCFilter();
        $filters[] = $this->getGlobalFilter();
        $filters[] = $this->getReservesFilter();
        return $filters;
    }

    /**
     * retrieves an array of associated content categories from the database
     *
     * @return array filled with semester names or empty
     */
    private function getCCFilter()
    {
        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT id AS value, title AS text');
        $query->from('#__categories');
        $query->where("id IN (SELECT DISTINCT contentCatID FROM #__thm_organizer_categories)");
        $query->order('title ASC');
        $this->_db->setQuery((string) $query);

        try
        {
            $cCategories = (array) $this->_db->loadAssocList();
            $defaultOptions = array();
            $defaultOptions[] = array('value' => '*', 'text' => JText::_('COM_THM_ORGANIZER_CAT_SEARCH_CCATS'));
            $defaultOptions[] = array('value' => '*', 'text' => JText::_('COM_THM_ORGANIZER_CAT_ALL_CCATS'));
            $options = array_merge($defaultOptions, $cCategories);
            $attribs = array('onChange' => 'this.form.submit()');
            return JHtml::_('select.genericlist', $options, 'filter_content_cat', $attribs, 'value', 'text', $this->getState('filter.content_cat'));
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return '';
        }
    }

    /**
     * Creates a filter for the global attribute
     *
     * @return  string  a html selection box for global value selection
     */
    private function getGlobalFilter()
    {
        $options = array();
        $options[] = array('value' => '*', 'text' => JText::_('COM_THM_ORGANIZER_CAT_SEARCH_RESERVES'));
        $options[] = array('value' => '*', 'text' => JText::_('COM_THM_ORGANIZER_CAT_ALL_GLOBAL'));
        $options[] = array('value' => '0', 'text' => JText::_('COM_THM_ORGANIZER_CAT_NOT_RESERVES'));
        $options[] = array('value' => '1', 'text' => JText::_('COM_THM_ORGANIZER_CAT_GLOBAL'));
        $attribs = array('onChange' => 'this.form.submit()');
        return JHtml::_('select.genericlist', $options, 'filter_global', $attribs, 'value', 'text', $this->getState('filter.global'));
    }

    /**
     * Creates a filter for the reserves attribute
     *
     * @return  string  a html selection box for reserves value selection
     */
    private function getReservesFilter()
    {
        $options = array();
        $options[] = array('value' => '*', 'text' => JText::_('COM_THM_ORGANIZER_CAT_SEARCH_GLOBAL'));
        $options[] = array('value' => '*', 'text' => JText::_('COM_THM_ORGANIZER_CAT_ALL_RESERVES'));
        $options[] = array('value' => '0', 'text' => JText::_('COM_THM_ORGANIZER_CAT_NOT_RESERVES'));
        $options[] = array('value' => '1', 'text' => JText::_('COM_THM_ORGANIZER_CAT_RESERVES'));
        $attribs = array('onChange' => 'this.form.submit()');
        return JHtml::_('select.genericlist', $options, 'filter_reserves', $attribs, 'value', 'text', $this->getState('filter.reserves'));
    }
}
