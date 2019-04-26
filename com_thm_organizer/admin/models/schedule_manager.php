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

require_once 'list.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/date.php';


/**
 * Class retrieves information for a filtered set of schedules.
 */
class THM_OrganizerModelSchedule_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'created';

    protected $defaultDirection = 'DESC';

    /**
     * sets variables and configuration data
     *
     * @param array $config the configuration parameters
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['departmentname', 'semestername', 'creationDate', 'active'];
        }

        parent::__construct($config);
    }

    /**
     * generates the query to be used to fill the output list
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $allowedDepartments = Access::getAccessibleDepartments('schedule');
        $shortTag           = Languages::getShortTag();
        $dbo                = $this->getDbo();
        $query              = $dbo->getQuery(true);

        $select       = 's.id, s.active, s.creationDate, s.creationTime, ';
        $select       .= "d.id AS departmentID, d.short_name_$shortTag AS departmentName, ";
        $select       .= 'pp.id AS planningPeriodID, pp.name AS planningPeriodName, ';
        $select       .= 'u.name AS userName, ';
        $createdParts = ['s.creationDate', 's.creationTime'];
        $select       .= $query->concatenate($createdParts, ' ') . ' AS created ';

        $query->select($select)
            ->from('#__thm_organizer_schedules AS s')
            ->innerJoin('#__thm_organizer_departments AS d ON s.departmentID = d.id')
            ->innerJoin('#__thm_organizer_planning_periods AS pp ON s.planningPeriodID = pp.id')
            ->leftJoin('#__users AS u ON u.id = s.userID')
            ->where('d.id IN (' . implode(', ', $allowedDepartments) . ')');

        $this->setValueFilters($query, ['departmentID', 'planningPeriodID', 'active']);

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
        $items  = parent::getItems();
        $return = [];

        if (empty($items)) {
            return $return;
        }

        $index = 0;

        foreach ($items as $item) {
            $return[$index] = [];

            $return[$index]['checkbox']         = HTML::_('grid.id', $index, $item->id);
            $return[$index]['departmentID']     = $item->departmentName;
            $return[$index]['planningPeriodID'] = $item->planningPeriodName;

            $return[$index]['active']
                = $this->getToggle($item->id, $item->active, 'schedule', Languages::_('THM_ORGANIZER_TOGGLE_ACTIVE'));

            $return[$index]['userName'] = $item->userName;

            $created                   = Dates::formatDate($item->creationDate);
            $created                   .= ' / ' . Dates::formatTime($item->creationTime);
            $return[$index]['created'] = $created;

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

        $headers['checkbox']         = '';
        $headers['departmentID']     = HTML::sort('DEPARTMENT', 'departmentName', $direction, $ordering);
        $headers['planningPeriodID'] = HTML::sort('PLANNING_PERIOD', 'planningPeriodName', $direction, $ordering);
        $headers['active']           = HTML::sort('STATE', 'active', $direction, $ordering);
        $headers['userName']         = HTML::sort('USERNAME', 'userName', $direction, $ordering);
        $headers['created']          = HTML::sort('CREATION_DATE', 'created', $direction, $ordering);

        return $headers;
    }
}
