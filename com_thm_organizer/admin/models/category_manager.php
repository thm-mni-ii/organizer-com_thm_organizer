<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        category manager model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
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
        $this->contentCategories = $this->getContentCategories();
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

        $orderby = $dbo->getEscaped($this->getState('list.ordering', 'ectitle'));
        $direction = $dbo->getEscaped($this->getState('list.direction'));
        $query->order("$orderby $direction");

        return $query;
    }

    /**
     * Function to feed the data in the table body correctly to the list view
     *
     * @return array consisting of items in the body
     */
    public function getItems2()
    {
        $items = parent::getItems();
        $body_items = array();
        $fields = array('ectitle', 'global', 'reserves', 'cctitle');

        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);

        $query->select($dbo->quoteName(array('c.title', 'c.id')));
        $query->from($dbo->quoteName('#__categories', 'c'));
        $query->join('INNER', $dbo->quoteName('#__viewlevels', 'vl') . 'ON (' . $dbo->quoteName('c.access') . ' = ' . $dbo->quoteName('vl.id') . ')');
        $query->where($dbo->quoteName('c.extension') . ' = ' . $dbo->quote('com_content') . ' AND ' . $dbo->quoteName('published') . ' = 1');
        $query->order($dbo->quoteName('c.title'), ' ASC');

        $dbo->setQuery($query);

        try
        {
            $result = $dbo->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        /*foreach ($items as $item)
        {
            $attributes = array();
            $body_item = array('attributes' => $attributes, 'id' => $item->id);
            foreach ($fields as $field)
            {
                $a = array();
                $td = array();
                $value = array();

                if ($field == 'global' || $field == 'reserves')
                {
                    $a[] = 'class="jgrid" ';

                    if ($item->$field)
                    {
                        $value[] = '<span class="state publish"/> ';
                    }
                    else
                    {
                        $value[] = '<span class="state expired"/> ';
                    }
                }
                else
                {
                    $value[] = $item->$field;
                }
                $a[] = 'href="index.php?option=com_thm_organizer&view=category_edit&categoryID=' . $item->id . '" ';

                $att = array('a' => $a, 'td' => $td, 'value' => $value);
                array_push($body_item['attributes'], $att);

            }
            $body_item['id'] = $item->id;
            $body_items[] = $body_item;
        }*/

        foreach ($items as $item)
        {
            $attributes = array();
            $body_item = array('attributes' => $attributes, 'id' => $item->id);

            foreach ($fields as $field)
            {
                $a = '<a ';

                if ($field == 'global' || $field == 'reserves')
                {
                    $a .= 'class="jgrid" ';
                    if ($item->$field)
                    {
                        $val = '<span class="state publish"/> ';
                    }
                    else
                    {
                        $val = '<span class="state expired"/> ';
                    }

                }
                elseif($field == 'cctitle')
                {
                    $val = '<select id="jform_contentCatID" name="jform[contentCatID]" onchange="Joomla.submitbutton' . "('category.update_category', " . "'" . $item->id . "')" . '">';
                    foreach ($result as $r)
                    {
                        $val .= '<option value="' . $r->id . '"';
                        if ($r->title == $item->$field)
                            $val .= ' selected';
                        $val .= ' name="option' . $r->id . '"';
                        $val .= '>';
                        $val .= $r->title . ' - ' .$r->id . '</option>';
                    }
                    $val .= '</select>';
                }
                else
                {
                    $val = $item->$field;
                }
                $a .= 'href="index.php?option=com_thm_organizer&view=category_edit&categoryID=' . $item->id . '" ';
                $a .= '>';
                // "SELECT c.id, c.title AS contentCatID FROM #__categories AS c INNER JOIN #__viewlevels AS vl ON c.access = vl.id WHERE
                // c.extension = 'com_content' AND published = '1' ORDER BY c.title ASC"


                /*echo '<select>';
                foreach($result as $r)
                {
                    echo '<option>' . $r->title . " - " . $r->id . '</option>';
                }
                echo '</select>';*/
                $value = array();
                if ($field != 'cctitle')
                    $value['value'] = $a . $val . '</a>';
                else
                    $value['value'] = $val;

                array_push($body_item['attributes'], $value);
            }
            $body_items[] = $body_item;
        }

        /*foreach ($items as $item)
        {
            $body_item = array();
            $body_item['id'] = $item->id;
            foreach ($fields as $field)
            {
                $a = '<a ';

                if ($field == 'global' || $field == 'reserves')
                {
                    if ($item->$field)
                    {
                        $val = '<span class="state publish"/> ';
                    }
                    else
                    {
                        $val = '<span class="state expired"/> ';
                    }
                }
                else
                {
                    $val = $item->$field;
                }

                $a .= 'href="index.php?option=com_thm_organizer&view=category_edit&categoryID=' . $item->id . '" ';
                $a .= '>';
                $body_item['attributes']['value'] = $a . $val . '</a>';
            }
            $body_items[] = $body_item;
        }*/

        return $body_items;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        /*$headers[] = array('name' => JText::_('COM_THM_ORGANIZER_NAME'),
            'field' => 'ectitle', 'sortable' => true);
        $headers[] = array('name' => JText::_('COM_THM_ORGANIZER_CAT_GLOBAL'),
            'field' => 'global', 'sortable' => true);
        $headers[] = array('name' => JText::_('COM_THM_ORGANIZER_CAT_RESERVES'),
            'field' => 'reserves', 'sortable' => true);
        $headers[] = array('name' => JText::_('COM_THM_ORGANIZER_CAT_CONTENT_CATEGORY'),
            'field' => 'cctitle', 'sortable' => true);*/
        $ordering = $this->state->get('list.ordering');
        $direction = $this->state->get('list.direction');

        $headers = array();
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
     * retrieves an array of associated content categories from the database
     *
     * @return array filled with semester names or empty
     */
    private function getContentCategories()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT id, title');
        $query->from('#__categories');
        $query->where("id IN (SELECT DISTINCT contentCatID FROM #__thm_organizer_categories)");
        $query->order('title ASC');
        $dbo->setQuery((string) $query);

        try
        {
            $contentCategories = $dbo->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        return (count($contentCategories))? $contentCategories : array();
    }
}
