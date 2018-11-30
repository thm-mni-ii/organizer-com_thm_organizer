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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class retrieves information for a filtered set of plan (degree) programs / organizational groupings.
 */
class THM_OrganizerModelPlan_Program_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'ppr.gpuntisID';

    protected $defaultDirection = 'asc';

    /**
     * Method to get all plan_programs from the database
     *
     * @return JDatabaseQuery
     */
    protected function getListQuery()
    {
        $allowedDepartments = THM_OrganizerHelperAccess::getAccessibleDepartments('schedule');
        $shortTag           = THM_OrganizerHelperLanguage::getShortTag();
        $query              = $this->_db->getQuery(true);

        $select    = "DISTINCT ppr.id, ppr.gpuntisID, ppr.name, pr.name_$shortTag AS prName, pr.version, d.abbreviation AS abbreviation, ";
        $linkParts = ["'index.php?option=com_thm_organizer&view=plan_program_edit&id='", 'ppr.id'];
        $select    .= $query->concatenate($linkParts, '') . ' AS link';
        $query->select($select);

        $query->from('#__thm_organizer_plan_programs AS ppr');
        $query->leftJoin('#__thm_organizer_programs AS pr ON ppr.programID = pr.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON pr.degreeID = d.id');

        $departmentID = $this->state->get('list.departmentID');
        $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.programID = ppr.id');

        if ($departmentID and in_array($departmentID, $allowedDepartments)) {
            $query->where("dr.departmentID = '$departmentID'");
        } else {
            $query->where("dr.departmentID IN ('" . implode("', '", $allowedDepartments) . "')");
        }

        $searchColumns = ['ppr.name', 'ppr.gpuntisID'];
        $this->setSearchFilter($query, $searchColumns);

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to overwrite the getItems method in order to set the program name
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
            $return[$index]['gpuntisID'] = HTML::_('link', $item->link, $item->gpuntisID);
            $return[$index]['name']      = HTML::_('link', $item->link, $item->name);
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

        $headers['checkbox']  = '';
        $headers['gpuntisID'] = HTML::sort('GPUNTISID', 'ppr.gpuntisID', $direction, $ordering);
        $headers['name']      = HTML::sort('DISPLAY_NAME', 'ppr.name', $direction, $ordering);

        return $headers;
    }
}
