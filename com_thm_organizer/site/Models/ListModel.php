<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\ListModel as ParentModel;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class provides a standardized framework for the display of listed resources.
 */
abstract class ListModel extends ParentModel
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'ASC';

    protected $defaultLimit;

    protected $defaultStart = '0';

    protected $option = 'com_thm_organizer';

    /**
     * Constructor.
     *
     * @param array $config An optional associative array of configuration settings.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->defaultLimit   = OrganizerHelper::getApplication()->get('list_limit', '20');
        $this->filterFormName = strtolower(OrganizerHelper::getClass($this));
    }

    /**
     * Method to get the view name
     *
     * The model name by default parsed using the classname, or it can be set
     * by passing a $config['name'] in the class constructor
     *
     * @return  string  The name of the model
     *
     * @throws  Exception
     */
    public function getName()
    {
        if (empty($this->name)) {
            $this->name = OrganizerHelper::getClass($this);
        }

        return $this->name;
    }

    /**
     * Method to get the total number of items for the data set. Joomla erases critical fields for complex data sets.
     * This method fixes the erroneous output of undesired duplicate entries.
     *
     * @param string $idColumn the main id column of the list query
     *
     * @return integer  The total number of items available in the data set.
     */
    public function getTotal($idColumn = null)
    {
        if (empty($idColumn)) {
            return parent::getTotal();
        }

        // Get a storage key.
        $store = $this->getStoreId('getTotal');

        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        // Load the total.
        $query = $this->getListQuery();
        $query->clear('select')->clear('limit')->clear('offset')->clear('order');
        $query->select("COUNT(DISTINCT ($idColumn))");
        $this->_db->setQuery($query);

        $total = (int)OrganizerHelper::executeQuery('loadResult');

        // Add the total to the internal cache.
        $this->cache[$store] = $total;

        return $this->cache[$store];
    }

    /**
     * Method to get a form object.
     *
     * @param string         $name    The name of the form.
     * @param string         $source  The form source. Can be XML string if file flag is set to false.
     * @param array          $options Optional array of options for the form creation.
     * @param boolean        $clear   Optional argument to force load a new form.
     * @param string|boolean $xpath   An optional xpath to search for the fields.
     *
     * @return  Form|boolean  Form object on success, False on error.
     */
    protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
    {
        Form::addFormPath(JPATH_COMPONENT_SITE . '/Forms');
        Form::addFieldPath(JPATH_COMPONENT_SITE . '/Fields');

        return parent::loadForm($name, $source, $options, $clear, $xpath);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed  The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = OrganizerHelper::getApplication()->getUserState($this->context, new \stdClass);

        // Pre-create the list options
        if (!property_exists($data, 'list')) {
            $data->list = [];

        }

        if (!property_exists($data, 'filter')) {
            $data->filter = [];
        }

        // Joomla doesn't fill these correctly but requires some of them
        $data->list['fullordering']
            = $this->state->get('list.fullordering', "$this->defaultOrdering $this->defaultDirection");

        $data->list['ordering']  = $this->state->get('list.ordering', $this->defaultOrdering);
        $data->list['direction'] = $this->state->get('list.direction', $this->defaultDirection);
        $data->list['limit']     = $this->state->get('list.limit', $this->defaultLimit);
        $data->list['start']     = $this->state->get('list.start', $this->defaultStart);

        return $data;
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param string $ordering  An optional ordering field.
     * @param string $direction An optional direction (asc|desc).
     *
     * @return void populates state properties
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);
        $app = OrganizerHelper::getApplication();

        // Receive & set filters
        $filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', [], 'array');
        if (!empty($filters)) {
            foreach ($filters as $name => $value) {
                $this->setState('filter.' . $name, $value);
            }
        }

        $list = $app->getUserStateFromRequest($this->context . '.list', 'list', [], 'array');
        $this->setListState($list);

        $validLimit = (isset($list['limit']) && is_numeric($list['limit']));
        $limit      = $validLimit ? $list['limit'] : $this->defaultLimit;
        $this->setState('list.limit', $limit);

        $value = $this->getUserStateFromRequest('limitstart', 'limitstart', 0);
        $start = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
        $this->setState('list.start', $start);
    }

    /**
     * Handles the full ordering list input if existent
     *
     * @param array  &$list      the list section of the form request
     * @param object &$session   the session object
     * @param string  $ordering  the attribute upon which the ordering is determined
     * @param string  $direction the direction of the sort
     *
     * @return void  alters the input parameters
     */
    protected function processFullOrdering(&$list, &$session, &$ordering, &$direction)
    {
        // Joomla lost the ordering part through pagination use
        if (strpos($list['fullordering'], 'null') !== false) {
            $list['fullordering'] = $session->get($this->context . '.ordering', "$ordering $direction");
        }
        $orderingParts = explode(' ', $list['fullordering']);
        if (count($orderingParts) == 2) {
            $plausibleOrdering = $orderingParts[0] != 'null';
            $validDirection    = in_array(strtoupper($orderingParts[1]), ['ASC', 'DESC', '']);
            if ($plausibleOrdering and $validDirection) {
                $ordering  = $orderingParts[0];
                $direction = $orderingParts[1];
            }
        }
    }

    /**
     * Provides a default method for setting filters based on id/unique values
     *
     * @param object &$query      the query object
     * @param string  $idColumn   the id column in the table
     * @param array   $filterName the filter name to look for the id in
     *
     * @return void
     */
    protected function setIDFilter(&$query, $idColumn, $filterName)
    {
        $value = $this->state->get($filterName, '');
        if ($value === '') {
            return;
        }

        /**
         * Special value reserved for empty filtering. Since an empty is dependent upon the column default, we must
         * check against multiple 'empty' values. Here we check against empty string and null. Should this need to
         * be extended we could maybe add a parameter for it later.
         */
        if ($value == '-1') {
            $query->where("$idColumn = '' OR $idColumn IS NULL");

            return;
        }

        // IDs are unique and therefore mutually exclusive => one is enough!
        $query->where("$idColumn = '$value'");

        return;
    }

    /**
     * Sets the ordering and direction filters should a valid full ordering request be made
     *
     * @param array $list an array of list variables
     *
     * @return void  sets state variables
     */
    protected function setListState($list)
    {
        $validReqOrdering = (!empty($list['ordering']) and strpos('null', $list['ordering']) !== null);
        $ordering         = $validReqOrdering ? $list['ordering'] : $this->defaultOrdering;

        $validReqDirection = (!empty($list['direction'])
            and in_array(strtoupper($list['direction']), ['ASC', 'DESC', '']));
        $direction         = $validReqDirection ? $list['direction'] : $this->defaultDirection;

        $session = Factory::getSession();
        if (!empty($list['fullordering'])) {
            $this->processFullOrdering($list, $session, $ordering, $direction);
        }

        $session->set($this->context . '.ordering', "$ordering $direction");
        $this->setState('list.fullordering', "$ordering $direction");
        $this->setState('list.ordering', $ordering);
        $this->setState('list.direction', $direction);

        $alreadyProcessed = ['ordering, direction, fullordering'];
        foreach ($list as $item => $value) {
            if (!in_array($item, $alreadyProcessed)) {
                $this->setState("list.$item", $value);
            }
        }
    }

    /**
     * Provides a default method for setting the list ordering
     *
     * @param object &$query the query object
     *
     * @return void
     */
    protected function setOrdering(&$query)
    {
        $defaultOrdering = "{$this->defaultOrdering} {$this->defaultDirection}";
        $session         = Factory::getSession();
        $listOrdering    = $this->state->get('list.fullordering', $defaultOrdering);
        if (strpos($listOrdering, 'null') !== false) {
            $sessionOrdering = $session->get('ordering', '');
            if (empty($sessionOrdering)) {
                $session->set($this->context . '.ordering', $defaultOrdering);
                $query->order($defaultOrdering);

                return;
            }
        }
        $query->order($listOrdering);
    }

    /**
     * Sets the search filter for the query
     *
     * @param object &$query       the query to modify
     * @param array   $columnNames the column names to use in the search
     *
     * @return void
     */
    protected function setSearchFilter(&$query, $columnNames)
    {
        $userInput = $this->state->get('filter.search', '');
        if (empty($userInput)) {
            return;
        }
        $search  = '%' . $this->_db->escape($userInput, true) . '%';
        $wherray = [];
        foreach ($columnNames as $name) {
            $wherray[] = "$name LIKE '$search'";
        }
        $where = implode(' OR ', $wherray);
        $query->where("( $where )");
    }

    /**
     * Provides a default method for setting filters for non-unique values
     *
     * @param object &$query       the query object
     * @param array   $filterNames the filter names. names should be synonymous with db column names.
     *
     * @return void
     */
    protected function setValueFilters(&$query, $filterNames)
    {
        // The view level filters
        foreach ($filterNames as $filterName) {

            $queryColumnName = $filterName;

            if (strpos($filterName, '.') !== false) {
                $filterName = explode('.', $filterName)[1];
            }

            $listValue   = $this->state->get("list.$filterName");
            $filterValue = $this->state->get("filter.$filterName");

            if (empty($listValue) and empty($filterValue)) {
                continue;
            }

            $value = empty($filterValue) ? $listValue : $filterValue;

            /**
             * Special value reserved for empty filtering. Since an empty is dependent upon the column default, we must
             * check against multiple 'empty' values. Here we check against empty string and null. Should this need to
             * be extended we could maybe add a parameter for it later.
             */
            if ($value == '-1') {
                $query->where("( $queryColumnName = '' OR $queryColumnName IS NULL )");
                continue;
            }

            $query->where("$queryColumnName = '$value'");
        }
    }
}
