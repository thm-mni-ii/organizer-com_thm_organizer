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

use \THM_OrganizerHelperHTML as HTML;

require_once 'list.php';

/**
 * Class retrieves information for a filtered set of plan (subject) pools.
 */
class THM_OrganizerModelPlan_Pool_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'ppl.gpuntisID';

    protected $defaultDirection = 'asc';

    /**
     * Method to get all plan_pools from the database
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $allowedDepartments = THM_OrganizerHelperAccess::getAccessibleDepartments('schedule');
        $query              = $this->_db->getQuery(true);

        if (empty($allowedDepartments)) {
            return $query;
        }

        $select    = 'DISTINCT ppl.id, ppl.gpuntisID, ppl.full_name, ppl.name, ';
        $linkParts = ["'index.php?option=com_thm_organizer&view=plan_pool_edit&id='", 'ppl.id'];
        $select    .= $query->concatenate($linkParts, '') . ' AS link';

        $query->from('#__thm_organizer_plan_pools AS ppl');
        $query->leftJoin('#__thm_organizer_department_resources AS dr ON ppl.programID = dr.programID');

        $departmentID = $this->state->get('list.departmentID');

        if ($departmentID and in_array($departmentID, $allowedDepartments)) {
            $query->where("dr.departmentID = '$departmentID'");
        } elseif ($departmentID == '-1') {
            $query->where('dr.departmentID IS NULL');
        } else {
            $query->where("dr.departmentID IN ('" . implode("', '", $allowedDepartments) . "')");
        }

        $programID = $this->state->get('list.programID');

        if ($programID) {
            $select .= ', ppr.id as programID, ppr.name as programName';
            $query->innerJoin('#__thm_organizer_plan_programs AS ppr ON ppl.programID = ppr.id');
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
     * @return array  an array of objects fulfilling the request criteria
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
            $return[$index]['checkbox']  = HTML::_('grid.id', $index, $item->id);
            $return[$index]['full_name'] = HTML::_('link', $item->link, $item->full_name);
            $return[$index]['name']      = HTML::_('link', $item->link, $item->name);
            $return[$index]['gpuntisID'] = HTML::_('link', $item->link, $item->gpuntisID);
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
        $headers['full_name'] = HTML::sort('NAME', 'ppl.full_name', $direction, $ordering);
        $headers['name']      = HTML::sort('SHORT_NAME', 'ppl.name', $direction, $ordering);
        $headers['gpuntisID'] = HTML::sort('GPUNTISID', 'ppl.gpuntisID', $direction, $ordering);

        return $headers;
    }
}
