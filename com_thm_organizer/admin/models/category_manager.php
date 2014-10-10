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
class THM_OrganizerModelCategory_Manager extends THM_CoreListModel
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
                'ectitle', 'ec.title',
                'ec.global',
                'ec.reserves',
                'cc.title', 'cctitle'
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
        $parts = array("'index.php?option=com_thm_organizer&view=category_edit&id='", "ec.id");
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

        $orderby = $dbo->escape($this->state->get('list.ordering', 'ec.title'));
        $direction = $dbo->escape($this->state->get('list.direction', 'ASC'));
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
            $return[$index] = array();
            $return[$index][0] = JHtml::_('grid.id', $index, $item->id);
            $return[$index][1] = JHtml::_('link', $item->link, $item->ectitle);
            $return[$index][2] = $this->getToggle($item->id, $item->global, 'global');
            $return[$index][3] = $this->getToggle($item->id, $item->reserves, 'reserves');
            $return[$index][4] = JHtml::_('link', $item->link, $item->cctitle);
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
        $iconClass = empty($value)? 'unpublish' : 'publish';
        $aClass = empty($value)? 'inactive' : '';
        $textConstant = 'COM_THM_ORGANIZER_CATEGORY_MANAGER_TOGGLE_' . strtoupper($attribute);
        $toggle = '<div class="button-grp">';
        $toggle .= '<a class="btn btn-micro ' . $aClass . ' hasTooltip" title="' . JText::_($textConstant) . '"';
        $toggle .= 'href="index.php?option=com_thm_organizer&task=category.toggle&attribute=' . $attribute . '&id=' . $id . '&value=' . $value . '">';
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
        $ordering = $this->state->get('list.ordering');
        $direction = $this->state->get('list.direction');

        $headers = array();
        $headers[] = '';
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'ec.title', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_GLOBAL', 'ec.global', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_RESERVES', 'ec.reserves', $direction, $ordering);
        $headers[] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_CONTENT_CATEGORY', 'cc.title', $direction, $ordering);

        return $headers;
    }
}
