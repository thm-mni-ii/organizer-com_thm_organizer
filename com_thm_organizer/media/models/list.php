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

/**
 * Class provides a standardized framework for the display of listed resources.
 */
abstract class THM_OrganizerModelList extends JModelList
{
    protected $defaultOrdering = '';

    protected $defaultDirection = 'ASC';

    protected $defaultLimit = '20';

    protected $defaultStart = '0';

    protected $defaultFilters = [];

    public $actions = null;

    /**
     * Constructor. Uses parent constructor, then sets model actions.
     *
     * @param array $config Configuration  (default: array)
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $path = JPATH_ROOT . "/media/com_thm_organizer/helpers/componentHelper.php";
        /** @noinspection PhpIncludeInspection */
        require_once $path;

        THM_OrganizerHelperComponent::addActions($this);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return mixed  The data for the form.
     * @throws Exception
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState($this->context, new stdClass);

        // Pre-create the list options
        if (!property_exists($data, 'list')) {
            $data->list = [];

        }

        if (!property_exists($data, 'filter')) {
            $data->filter = [];
        }

        // Joomla doesn't fill these correctly but requires some of them
        $data->list['fullordering'] = $this->state->get('list.fullordering',
            "$this->defaultOrdering $this->defaultDirection");
        $data->list['ordering']     = $this->state->get('list.ordering', $this->defaultOrdering);
        $data->list['direction']    = $this->state->get('list.direction', $this->defaultDirection);
        $data->list['limit']        = $this->state->get('list.limit', $this->defaultLimit);
        $data->list['start']        = $this->state->get('list.start', $this->defaultStart);

        // Set default values for filters
        foreach ($this->defaultFilters as $name => $defaultValue) {
            $data->filter[$name] = $this->state->get('filter.' . $name, $defaultValue);
        }

        return $data;
    }

    /**
     * Method to get the total number of items for the data set. Joomla erases critical fields for complex data sets.
     * This method fixes the erroneous output of undesired duplicate entries.
     *
     * @param  string $idColumn the main id column of the list query
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

        try {
            $total = (int)$this->_db->loadResult();
        } catch (RuntimeException $exc) {
            $this->setError($exc->getMessage());

            return false;
        }

        // Add the total to the internal cache.
        $this->cache[$store] = $total;

        return $this->cache[$store];
    }

    /**
     * Overwrites the JModelList populateState function
     *
     * @param  string $ordering  An optional ordering field.
     * @param  string $direction An optional direction (asc|desc).
     *
     * @return void  sets object state variables
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication();

        // Receive & set filters
        $filters = $app->getUserStateFromRequest($this->context . '.filter', 'filter', [], 'array');
        if (!empty($filters)) {
            foreach ($filters as $name => $value) {
                $this->setState('filter.' . $name, $value);
            }
        } else {
            foreach ($this->defaultFilters as $name => $defaultValue) {
                $this->state->set('filter.' . $name, $defaultValue);
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

        $validReqDirection = (!empty($list['direction']) and in_array(strtoupper($list['direction']),
                ['ASC', 'DESC', '']));
        $direction         = $validReqDirection ? $list['direction'] : $this->defaultDirection;

        $session = JFactory::getSession();
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
     * Handles the full ordering list input if existent
     *
     * @param  array  &$list     the list section of the form request
     * @param  object &$session  the session object
     * @param  string $ordering  the attribute upon which the ordering is determined
     * @param  string $direction the direction of the sort
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
     * Generates a toggle for the attribute in question
     *
     * @param  int    $id         the id of the database entry
     * @param  bool   $value      the value currently set for the attribute (saves asking it later)
     * @param  string $controller the name of the data management controller
     * @param  string $tip        the tooltip
     * @param  string $attribute  the resource attribute to be changed (useful if multiple entries can be toggled)
     *
     * @return string  a HTML string
     */
    protected function getToggle($id, $value, $controller, $tip, $attribute = null)
    {
        $iconClass = empty($value) ? 'unpublish' : 'publish';
        $icon      = '<i class="icon-' . $iconClass . '"></i>';

        $attributes          = [];
        $attributes['title'] = $tip;
        $attributes['class'] = 'btn btn-micro hasTooltip';
        $attributes['class'] .= empty($value) ? ' inactive' : '';

        $url  = "index.php?option=com_thm_organizer&task=" . $controller . ".toggle&id=" . $id . "&value=" . $value;
        $url  .= empty($attribute) ? '' : "&attribute=$attribute";
        $link = JHtml::_('link', $url, $icon, $attributes);

        return '<div class="button-grp">' . $link . '</div>';
    }

    /**
     * Provides a default method for setting the list ordering
     *
     * @param  object &$query the query object
     *
     * @return void
     */
    protected function setOrdering(&$query)
    {
        $defaultOrdering = "{$this->defaultOrdering} {$this->defaultDirection}";
        $session         = JFactory::getSession();
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
     * @param  object &$query      the query to modify
     * @param  array  $columnNames the column names to use in the search
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
     * Provides a default method for setting filters based on id/unique values
     *
     * @param  object &$query      the query object
     * @param  string $idColumn    the id column in the table
     * @param  array  $filterNames the filter names which filter against ids
     *
     * @return void
     */
    protected function setIDFilter(&$query, $idColumn, $filterNames)
    {
        foreach ($filterNames as $name) {
            $value = $this->state->get($name, '');
            if ($value === '') {
                continue;
            }

            /**
             * Special value reserved for empty filtering. Since an empty is dependent upon the column default, we must
             * check against multiple 'empty' values. Here we check against empty string and null. Should this need to
             * be extended we could maybe add a parameter for it later.
             */
            if ($value == '-1') {
                $query->where("$idColumn = '' OR $idColumn IS NULL");
            }

            // IDs are unique and therefore mutually exclusive => one is enough!

            $query->where("$idColumn = '$value'");

            return;
        }
    }

    /**
     * Provides a default method for setting filters for non-unique values
     *
     * @param  object &$query      the query object
     * @param  array  $filterNames the filter names. names should be synonymous with db column names.
     *
     * @return void
     */
    protected function setValueFilters(&$query, $filterNames)
    {
        // The view level filters
        foreach ($filterNames as $name) {
            $value = $this->state->get("list.$name", '');
            if ($value === '') {
                continue;
            }

            /**
             * Special value reserved for empty filtering. Since an empty is dependent upon the column default, we must
             * check against multiple 'empty' values. Here we check against empty string and null. Should this need to
             * be extended we could maybe add a parameter for it later.
             */
            if ($value == '-1') {
                $query->where("( $name = '' OR $name IS NULL )");
                continue;
            }

            $query->where("$name = '$value'");
        }

        // The column level filters
        foreach ($filterNames as $name) {
            $value = $this->state->get("filter.$name", '');
            if ($value === '') {
                continue;
            }

            /**
             * Special value reserved for empty filtering. Since an empty is dependent upon the column default, we must
             * check against multiple 'empty' values. Here we check against empty string and null. Should this need to
             * be extended we could maybe add a parameter for it later.
             */
            if ($value == '-1') {
                $query->where("( $name = '' OR $name IS NULL )");
                continue;
            }

            $query->where("$name = '$value'");
        }
    }

    /**
     * Provides a default method for setting filters for non-unique values
     *
     * @param  object &$query      the query object
     * @param  array  $filterNames the filter names. names should be synonymous with db column names.
     *
     * @return void
     */
    protected function setLocalizedFilters(&$query, $filterNames)
    {
        /** @noinspection PhpIncludeInspection */
        require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
        $tag = THM_OrganizerHelperLanguage::getShortTag();
        foreach ($filterNames as $name) {
            $value = $this->state->get("filter.$name", '');
            if ($value === '') {
                continue;
            }

            // The column is localized the filter is not
            $name .= "_$tag";

            /**
             * Special value reserved for empty filtering. Since an empty is dependent upon the column default, we must
             * check against multiple 'empty' values. Here we check against empty string and null. Should this need to
             * be extended we could maybe add a parameter for it later.
             */
            if ($value == '-1') {
                $query->where("( $name = '' OR $name IS NULL )");
                continue;
            }

            $query->where("$name = '$value'");
        }
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public abstract function getHeaders();


}
