<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

defined('_JEXEC') or die;

\JFormHelper::loadFieldClass('list');

/**
 * Class creates a select box for plan programs.
 */
class PlanProgramsField extends \JFormFieldList
{
    private $context;

    private $selectedIDs;

    protected $type = 'PlanPrograms';

    /**
     * Method to instantiate the form field object.
     *
     * @param Form $form The form to attach to the form field object.
     */
    public function __construct($form = null)
    {
        parent::__construct($form);
        $this->setContext();
    }

    /**
     * Adds department restrictions according to user scheduling access rights
     *
     * @param \JDatabaseQuery $query the query to be modified
     *
     * @return void modifies the query
     */
    private function addAccessibleRestriction(&$query)
    {
        $allowedDepartments = \Access::getAccessibleDepartments('schedule');
        $query->where("dr.departmentID IN ('" . implode("', '", $allowedDepartments) . "')");
    }

    /**
     * Adds department restrictions from any possible filter source
     *
     * @param \JDatabaseQuery $query the query to be modified
     *
     * @return void modifies the query
     */
    private function addFilterRestriction(&$query)
    {
        $input = \OrganizerHelper::getInput();

        $form = $input->get('jform', [], 'array');
        if (empty($form['departmentID'])) {
            $filter = $input->get('filter', [], 'array');
            if (empty($filter['departmentID'])) {
                $list = $input->get('list', [], 'array');
                if (empty($list['departmentID'])) {
                    $departmentID = $input->getInt('departmentID', 0);
                } else {
                    $departmentID = $list['departmentID'];
                }
            } else {
                $departmentID = $filter['departmentID'];
            }
        } else {
            $departmentID = $form['departmentID'];
        }

        if (empty($departmentID)) {
            return;
        }

        $query->where("dr.departmentID = '$departmentID'");
    }

    /**
     * Restricts the plan programs to those already associated with plan pools
     *
     * @param \JDatabaseQuery $query the query to be modified
     *
     * @return void modifies the query
     */
    private function addMergeRestriction(&$query)
    {
        if (!empty($selectedIDs)) {
            $query->innerJoin('#__thm_organizer_plan_pools AS ppl ON ppl.programID = ppr.id');
            $query->where("ppl.id IN ( '" . implode("', '", $this->selectedIDs) . "' )");
        }
    }

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return array the options for the select box
     */
    protected function getOptions()
    {
        $options = parent::getOptions();

        $programs = $this->getPlanPrograms();
        if (empty($programs)) {
            return $options;
        }

        foreach ($programs as $program) {
            if (!empty($program['value'])) {
                $options[] = \HTML::_('select.option', $program['value'], $program['text']);
            }
        }

        return $options;
    }

    /**
     * Retrieves the plan programs to be displayed in the field
     *
     * @return mixed
     */
    private function getPlanPrograms()
    {
        $dbo   = \Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT ppr.id AS value, ppr.name AS text');
        $query->from('#__thm_organizer_plan_programs AS ppr');
        $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.programID = ppr.id');
        $query->order('text ASC');

        switch ($this->context) {
            case 'edit':
                $this->addAccessibleRestriction($query);
                break;
            case 'filter':
                if ($this->getAttribute('access', 'false') == 'true') {
                    $this->addAccessibleRestriction($query);
                }
                $this->addFilterRestriction($query);
                break;
            case 'merge':
                $this->addMergeRestriction($query);
                break;
        }

        $dbo->setQuery($query);

        return \OrganizerHelper::executeQuery('loadAssocList');
    }

    /**
     * Sets context information about where the plan programs is being used.
     *
     * @return void
     */
    private function setContext()
    {
        $input = \OrganizerHelper::getInput();

        $poolID = $input->getInt('id');
        if (!empty($poolID)) {
            $this->context       = 'edit';
            $this->selectedIDs[] = $poolID;

            return;
        }

        $poolIDs = $input->get('cid', [], 'array');
        if (!empty($poolIDs)) {
            $this->context     = 'merge';
            $this->selectedIDs = \Joomla\Utilities\ArrayHelper::toInteger($poolIDs);

            return;
        }

        $this->context = 'filter';
        $filter        = $input->get('filter', [], 'array');
        if (empty($filter['programID'])) {
            $list                = $input->get('list', [], 'array');
            $this->selectedIDs[] = empty($list['programID']) ? '' : $list['programID'];
        } else {
            $this->selectedIDs[] = $filter['programID'];
        }
    }
}
