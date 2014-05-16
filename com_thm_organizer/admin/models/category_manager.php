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
        $select .= "CONCAT('index.php?option=com_thm_organizer&view=category_edit&categoryID=', ec.id) AS link";
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
     * takes user filter parameters and adds them to the view state
     *
     * @param   string  $ordering   the filter parameter to be used for ordering
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
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_CONTENT_CATEGORIES"), 500);
        }
        
        return (count($contentCategories))? $contentCategories : array();
    }
}
