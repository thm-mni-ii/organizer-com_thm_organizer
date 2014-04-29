<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.model
 * @name        THM_OrganizerModelVirtual_Schedule_Manager
 * @description Class to handle virtual schedules
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Class THM_OrganizerModelVirtual_Schedule_Manager for component com_thm_organizer
 * Class provides methods display a list of virtual schedules and perform actions on them
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.model
 * @link        www.mni.thm.de
 */
class THM_OrganizerModelVirtual_Schedule_Manager extends JModelList
{
    /**
     * Total records
     *
     * @var    Integer
     * @since  v0.0.1
     */
    private $_total = null;

    /**
     * Pagination object
     *
     * @var    Object
     * @since  v0.0.1
     */
    private $_pagination = null;

    /**
     * Constructor that calls the parent constructor and intialise variables
     *
     * @since   v0.0.1
     *
     */
    public function __construct()
    {
        parent::__construct();

        $mainframe = JFactory::getApplication("administrator");
        $option = $mainframe->scope;
        $view = JFactory::getApplication()->input->getString('view');

        // Get pagination request variables
        $limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
        $limitstart = $mainframe->getUserStateFromRequest($option . $view . '.limitstart', 'limitstart', 0, 'int');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);
    }

    /**
     * Method to build the sql query to get the virtual schedules
     *
     * @return    String    The sql query
     */
    public function _buildQuery()
    {
        $mainframe = JFactory::getApplication("administrator");
        $option = $mainframe->scope;
        $view = JFactory::getApplication()->input->getString('view');

        $filter_order = $mainframe->getUserStateFromRequest(
                "$option.$view.filter_order",
                'filter_order',
                'vs.semesterID, vs.vid',
                'string'
            );
        $filter_order_Dir = $mainframe->getUserStateFromRequest(
                "$option.$view.filter_order_Dir",
                'filter_order_Dir',
                '',
                'string'
            );
        $groupFilter = $mainframe->getUserStateFromRequest(
                "$option.$view.groupFilters",
                'groupFilters',
                '',
                'int'
            );
        $rolesFilter = $mainframe->getUserStateFromRequest(
                "$option.$view.rolesFilters",
                'rolesFilters',
                '',
                'int'
            );
        $search = $this->_db->escape(
            trim(
                JString::strtolower(
                    $mainframe->getUserStateFromRequest(
                            "$option.$view.search",
                            'search',
                            '',
                            'string'
                        )
                    )
                )
            );

        if (!$filter_order_Dir)
        {
            $filter_order_Dir = '';
        }

        $orderby     = "\n ORDER BY $filter_order $filter_order_Dir";

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $select = 'DISTINCT vs.id as id, vs.name, vs.type, u.name as responsible, ';
        $select .= 'department, CONCAT(s.organization, "-",s.semesterDesc ) as semesterID';
        $query->select($select);
        $query->from('#__thm_organizer_virtual_schedules AS vs');
        $query->innerJoin('#__thm_organizer_virtual_schedules_elements AS vse ON vs.id = vse.vid');
        $query->innerJoin('#__thm_organizer_semesters AS s ON vs.semesterID = s.id');
        $query->innerJoin('#__users AS u ON vs.responsible = u.username');
 
        $umlautString = $codeErrString = $search;

        $umlautSearch = array("Ö" => "&Ouml;",
                              "ö" => "&öuml;",
                              "Ä" => "&Auml;",
                              "ä" => "&auml;",
                              "Ü" => "&Uuml;",
                              "ü" => "&uuml;");
        foreach ($umlautSearch as $char => $code)
        {
            str_replace($char, $code, $umlautString);
        }

        $codeErrSearch = array("Ã¶" => "&öuml;",
                               "Ã¤" => "&auml;",
                               "Ã¼" => "&uuml;");
        foreach ($codeErrSearch as $chars => $code)
        {
            str_replace($chars, $code, $codeErrString);
        }
 
        $searchString = '(LOWER(vs.name) LIKE \'%' . $search . '%\' ';
        $searchString .= ' OR LOWER(vs.responsible) LIKE \'%' . $search . '%\' ';
        $searchString .= ' OR LOWER(vs.department) LIKE \'%' . $search . '%\' ';
        $searchString .= ' OR LOWER(vs.name) LIKE \'%' . $umlautString . '%\' ';
        $searchString .= ' OR LOWER(vs.responsible) LIKE \'%' . $umlautString . '%\' ';
        $searchString .= ' OR LOWER(vs.department) LIKE \'%' . $umlautString . '%\' ';
        $searchString .= ' OR LOWER(vs.name) LIKE \'%' . $codeErrString . '%\' ';
        $searchString .= ' OR LOWER(vs.responsible) LIKE \'%' . $codeErrString . '%\' ';
        $searchString .= ' OR LOWER(vs.department) LIKE \'%' . $codeErrString . '%\') ';
        $query->where($searchString);

        if ($groupFilter > 0)
        {
            $query->where("vs.type = '$groupFilter'");
        }
        if ($rolesFilter > 0)
        {
            $query->where("vs.semesterID = '$rolesFilter'");
        }

        $query .= $orderby;

        return $query;
    }

    /**
     * Method to get data
     *
     * @return    Array    An Array with data
     */
    public function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty( $this->_data ))
        {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }
        if (!is_array($this->_data))
        {
            $this->_data = array();
        }
        return $this->_data;
    }

    /**
     * Method to get the total number of records
     *
     * @return    Integer     The total number of records
     */
    public function getTotal()
    {
        if (empty($this->_total))
        {
            $dbo = JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $query->select('count(*)');
            $query->from('#__thm_organizer_virtual_schedules');
            $dbo->setQuery((string) $query);
            return $dbo->loadResult();
        }
    }

    /**
     * Method to get the pagination
     *
     * @return    JPagination     A JPagination Object
     */
    public function getPagination()
    {
        // Load the content if it doesn't already exist
        if (empty($this->_pagination))
        {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }
        return $this->_pagination;
    }

    /**
     * Method to get the elements
     *
     * @return    JPagination     A JPagination Object
     */
    public function getElements()
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_virtual_schedules_elements');
        $dbo->setQuery((string) $query);
        return $dbo->loadObjectList();
    }
}
