<?php
/**
 * @category    Joomla component
 * @package     com_thm_organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelLesson_Statistics
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/planning_periods.php';

/**
 * Retrieves lesson information for the generation of statisitics
 *
 * @category    Joomla.com_thm_organizer.site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelLesson_Statistics extends JModelForm
{
    public $columns = [];

    private $lang = null;

    private $langTag = 'de';

    public $lessons = [];

    private $query = null;

    public $rows = [];

    public $total = [];

    public function __construct(array $config = array())
    {
        parent::__construct($config);

        $this->lang    = THM_OrganizerHelperLanguage::getLanguage();
        $this->langTag = THM_OrganizerHelperLanguage::getShortTag();

        $this->populateState();
        $departmentID = $this->state->get('departmentID');
        $periodID     = $this->state->get('planningPeriodID');
        $programID    = $this->state->get('programID');

        $this->query = $this->_db->getQuery(true);
        $this->setBaseQuery();

        if (empty($periodID)) {
            $this->rows = $this->getPlanningPeriods();
        } else {
            $this->rows = $this->getMethods();
        }

        if (empty($departmentID) and empty($programID)) {
            $this->columns = $this->getDepartments();
        } elseif (empty($programID)) {
            $this->columns = $this->getPrograms();
        } else {
            $this->columns = $this->getPools();
        }

        $this->setLessonCounts();
    }

    /**
     * Adds a department restriction to the query as appropriate.
     *
     * @return void
     */
    private function addDepartmentRestriction()
    {
        $departmentID = $this->state->get('departmentID');
        if (!empty($departmentID)) {
            $this->query->where("l.departmentID = '$departmentID'");
        }
    }

    /**
     * Adds a planning period restriction to the query as appropriate.
     *
     * @return void
     */
    private function addPeriodRestriction()
    {
        $periodID = $this->state->get('planningPeriodID');
        if (!empty($periodID)) {
            $this->query->where("l.planningPeriodID = '$periodID'");
        }
    }

    /**
     * Adds a program restriction to the query as appropriate.
     *
     * @return void
     */
    private function addProgramRestriction()
    {
        $programID = $this->state->get('programID');
        if (!empty($programID)) {
            $this->query->where("pProg.id = '$programID'");
        }
    }

    /**
     * Gets an array of departments.
     *
     * @return array the departments.
     * @throws Exception
     */
    private function getDepartments()
    {
        $this->resetAdaptiveClauses();
        $this->query->select("DISTINCT dpt.id, dpt.short_name_$this->langTag AS name")
            ->where("l.delta != 'removed'")
            ->order("dpt.short_name_$this->langTag");

        $this->addPeriodRestriction();

        $this->_db->setQuery($this->query);

        try {
            $departments = $this->_db->loadAssocList('id');
        } catch (Exception $exception) {
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

            return [];
        }

        if (empty($departments)) {
            return [];
        }

        foreach ($departments as &$department) {
            $department['total'] = [];
        }

        return $departments;
    }

    /**
     * Method to get the form
     *
     * @param array $data     Data         (default: array)
     * @param bool  $loadData Load data  (default: true)
     *
     * @return  mixed  JForm object on success, False on error.
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            "com_thm_organizer.lesson_statistics",
            "lesson_statistics",
            ['control' => 'jform', 'load_data' => $loadData]
        );

        return !empty($form) ? $form : false;
    }

    /**
     * Gets an array of planning periods.
     *
     * @return array the planning periods
     * @throws Exception
     */
    private function getMethods()
    {
        $this->resetAdaptiveClauses();
        $this->query->select("DISTINCT m.id, m.name_$this->langTag AS name")
            ->where("l.delta != 'removed'")
            ->order('name');

        $this->addDepartmentRestriction();
        $this->addPeriodRestriction();
        $this->addProgramRestriction();

        $this->_db->setQuery($this->query);

        try {
            $methods = $this->_db->loadAssocList('id');
        } catch (Exception $exception) {
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

            return [];
        }

        if (empty($methods)) {
            return [];
        }

        foreach ($methods as &$method) {
            if (empty($method['name'])) {
                $method['name'] = $this->lang->_('COM_THM_ORGANIZER_NONE_GIVEN');
            }
            $method['total'] = [];
        }

        return $methods;
    }

    /**
     * Gets an array of planning periods.
     *
     * @return array the planning periods
     * @throws Exception
     */
    private function getPlanningPeriods()
    {
        $this->resetAdaptiveClauses();
        $this->query->select("DISTINCT pp.*")
            ->where('pp.startDate <= CURDATE()')
            ->where("l.delta != 'removed'")
            ->order('pp.startDate DESC');

        $this->addDepartmentRestriction();
        $this->addProgramRestriction();

        $this->_db->setQuery($this->query);

        try {
            $planningPeriods = $this->_db->loadAssocList('id');
        } catch (Exception $exception) {
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

            return [];
        }

        if (empty($planningPeriods)) {
            return [];
        }

        foreach ($planningPeriods as &$planningPeriod) {
            $planningPeriod['total'] = [];
        }

        return $planningPeriods;
    }

    /**
     * Gets an array of plan pools.
     *
     * @return array the planning periods
     * @throws Exception
     */
    private function getPools()
    {
        $this->resetAdaptiveClauses();
        $this->query->select("DISTINCT pPool.id, pPool.name")
            ->where("l.delta != 'removed'")
            ->order('pPool.name');

        $this->addDepartmentRestriction();
        $this->addPeriodRestriction();
        $this->addProgramRestriction();

        $this->_db->setQuery($this->query);

        try {
            $pools = $this->_db->loadAssocList('id');
        } catch (Exception $exception) {
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

            return [];
        }

        if (empty($pools)) {
            return [];
        }

        foreach ($pools as &$pool) {
            $pool['total'] = [];
        }

        return $pools;
    }

    /**
     * Gets an array of degree programs.
     *
     * @return array the planning periods
     * @throws Exception
     */
    private function getPrograms()
    {
        $this->resetAdaptiveClauses();
        $this->query->select("DISTINCT pProg.id, pProg.name")
            ->where("l.delta != 'removed'")
            ->order('pProg.name');

        $this->addDepartmentRestriction();
        $this->addPeriodRestriction();

        $this->_db->setQuery($this->query);

        try {
            $programs = $this->_db->loadAssocList('id');
        } catch (Exception $exception) {
            JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

            return [];
        }

        if (empty($programs)) {
            return [];
        }

        foreach ($programs as &$program) {
            $program['total'] = [];
        }

        return $programs;
    }

    /**
     * Method to auto-populate the model state.
     *
     * @return  void
     */
    protected function populateState()
    {
        parent::populateState();
        $defaultPeriod = THM_OrganizerHelperPlanning_Periods::getCurrentID();
        $formData      = JFactory::getApplication()->input->get('jform', [], 'array');

        // Not reached by form action
        if (empty($formData)) {
            $periodID     = $defaultPeriod;
            $departmentID = '';
            $programID    = '';
        } else {
            $periodID = empty($formData['planningPeriodID']) ? '' : (int)$formData['planningPeriodID'];

            $departmentSelected = !empty($formData['departmentID']);
            $departmentID       = $departmentSelected ? (int)$formData['departmentID'] : '';

            $programSelected = !empty($formData['programID']);
            $programID       = $programSelected ? (int)$formData['programID'] : '';
        }

        $this->setState('planningPeriodID', $periodID);
        $this->setState('departmentID', $departmentID);
        $this->setState('programID', $programID);
    }

    /**
     * Resets the query clauses which can vary.
     *
     * @return void modifies the model's query
     */
    private function resetAdaptiveClauses()
    {
        $this->query->clear('select')
            ->clear('where')
            ->clear('order');
    }

    /**
     * Sets the core clauses for lesson statistics queries.
     *
     * @return void modifies the model's query
     */
    private function setBaseQuery()
    {
        $this->query->from('#__thm_organizer_lessons AS l')
            ->innerJoin('#__thm_organizer_planning_periods AS pp ON pp.id = l.planningPeriodID')
            ->innerJoin('#__thm_organizer_departments AS dpt ON dpt.id = l.departmentID')
            ->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.lessonID = l.id')
            ->innerJoin('#__thm_organizer_lesson_pools AS lp on lp.subjectID = ls.id')
            ->innerJoin('#__thm_organizer_plan_pools AS pPool ON pPool.id = lp.poolID')
            ->innerJoin('#__thm_organizer_plan_programs AS pProg ON pProg.id = pPool.programID')
            ->leftJoin('#__thm_organizer_methods AS m ON m.id = l.methodID');
    }

    /**
     * Creates an array of arrays with total values. Array[$rowID][$columnID] = $total.
     *
     * @returns void sets the model property $lessons
     * @throws Exception
     */
    private function setLessonCounts()
    {
        $departmentID = $this->state->get('departmentID');
        $periodID     = $this->state->get('planningPeriodID');
        $programID    = $this->state->get('programID');
        $lessonCounts = [];
        foreach (array_keys($this->rows) as $rowID) {
            $lessons[$rowID] = [];
            foreach (array_keys($this->columns) as $columnID) {
                $this->resetAdaptiveClauses();
                $this->query->select("DISTINCT l.id")
                    ->where("l.delta != 'removed'");

                // Define column column
                if (empty($departmentID)) {
                    $column = empty($programID) ? 'dpt' : 'pPool';
                } else {
                    $this->query->where("l.departmentID = '$departmentID'");
                    $column = empty($programID) ? 'pProg' : 'pPool';
                }
                $this->query->where("$column.id = '$columnID'");

                // Define row column
                if (empty($periodID)) {
                    $this->query->where("pp.id = '$rowID'");
                } else {
                    $this->query->where("pp.id = '$periodID'");
                    $clause = empty($rowID) ? "m.id IS NULL" : "m.id = '$rowID'";
                    $this->query->where($clause);
                }

                $this->_db->setQuery($this->query);

                try {
                    $lessons = $this->_db->loadColumn();
                } catch (Exception $exception) {
                    JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

                    return;
                }

                $lessonCounts[$rowID][$columnID] = count($lessons);

                // Eliminates inflated values for lessons associated with more than one column/row
                $totalLessons                      = array_merge($this->total, $lessons);
                $this->total                       = array_unique($totalLessons);
                $this->columns[$columnID]['total'] = array_unique(array_merge($this->columns[$columnID]['total'],
                    $lessons));
                $this->rows[$rowID]['total']       = array_unique(array_merge($this->rows[$rowID]['total'], $lessons));
            }
        }

        foreach ($this->columns as $columnID => $column) {
            if (empty($column['total'])) {
                unset($this->columns[$columnID]);
            } else {
                $this->columns[$columnID]['total'] = count($column['total']);
            }
        }

        foreach ($this->rows as $rowID => $row) {
            if (empty($row['total'])) {
                unset($this->rows[$rowID]);
            } else {
                $this->rows[$rowID]['total'] = count($row['total']);
            }
        }

        $this->total = count($this->total);

        $this->lessons = $lessonCounts;
    }
}