<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPlan_Pool_Manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';

/**
 * Class provides methods to deal with plan_pools
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPlan_Pool_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'ppl.gpuntisID';

    protected $defaultDirection = 'asc';

    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param array $config Configuration  (default: array)
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * Method to get all plan_pools from the database
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $allowedDepartments = THM_OrganizerHelperComponent::getAccessibleDepartments('schedule');
        $query              = $this->_db->getQuery(true);

        if (empty($allowedDepartments)) {
            return $query;
        }

        $select    = "DISTINCT ppl.id, ppl.gpuntisID, ppl.full_name, ppl.name, ";
        $linkParts = ["'index.php?option=com_thm_organizer&view=plan_pool_edit&id='", "ppl.id"];
        $select    .= $query->concatenate($linkParts, "") . " AS link";

        $query->from('#__thm_organizer_plan_pools AS ppl');
        $query->innerJoin("#__thm_organizer_department_resources AS dr ON dr.poolID = ppl.id");

        $departmentID = $this->state->get('list.departmentID');

        if ($departmentID and in_array($departmentID, $allowedDepartments)) {
            $query->where("dr.departmentID = '$departmentID'");
        } else {
            $query->where("dr.departmentID IN ('" . implode("', '", $allowedDepartments) . "')");
        }

        $programID = $this->state->get('list.programID');

        if ($programID) {
            $select .= ", ppr.id as programID, ppr.name as programName";
            $query->innerJoin("#__thm_organizer_plan_programs AS ppr ON ppl.programID = ppr.id");
            $query->where("ppl.programID = '$programID'");
        }

        $query->select($select);

        $searchColumns = ['ppl.full_name', 'ppl.name', 'ppl.gpuntisID'];
        $this->setSearchFilter($query, $searchColumns);

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to overwrite the getItems method in order to set the pool name
     *
     * @return  array  an array of objects fulfilling the request criteria
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
            $return[$index]              = [];
            $return[$index]['checkbox']  = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['full_name'] = JHtml::_('link', $item->link, $item->full_name);
            $return[$index]['name']      = JHtml::_('link', $item->link, $item->name);
            $return[$index]['gpuntisID'] = JHtml::_('link', $item->link, $item->gpuntisID);
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
        $ordering             = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction            = $this->state->get('list.direction', $this->defaultDirection);
        $headers              = [];
        $headers['checkbox']  = '';
        $headers['full_name'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'ppl.full_name', $direction,
            $ordering);
        $headers['name']      = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_SHORT_NAME', 'ppl.name', $direction,
            $ordering);
        $headers['gpuntisID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_GPUNTISID', 'ppl.gpuntisID', $direction,
            $ordering);

        return $headers;
    }
}
