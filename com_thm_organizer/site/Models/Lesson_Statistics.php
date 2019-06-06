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

use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Terms;

/**
 * Class calculates lesson statistics and loads them into the view context.
 */
class Lesson_Statistics extends FormModel
{
    public $columns = [];

    private $langTag = 'de';

    public $lessons = [];

    private $query = null;

    public $rows = [];

    public $total = [];

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->langTag = Languages::getShortTag();

        $this->populateState();
        $categoryID   = $this->state->get('categoryID');
        $departmentID = $this->state->get('departmentID');
        $periodID     = $this->state->get('termID');

        $this->query = $this->_db->getQuery(true);
        $this->setBaseQuery();

        if (empty($periodID)) {
            $this->rows = $this->getTerms();
        } else {
            $this->rows = $this->getMethods();
        }

        if (empty($departmentID) and empty($categoryID)) {
            $this->columns = $this->getDepartments();
        } elseif (empty($categoryID)) {
            $this->columns = $this->getCategories();
        } else {
            $this->columns = $this->getGroups();
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
     * Adds a term restriction to the query as appropriate.
     *
     * @return void
     */
    private function addPeriodRestriction()
    {
        $periodID = $this->state->get('termID');
        if (!empty($periodID)) {
            $this->query->where("l.termID = '$periodID'");
        }
    }

    /**
     * Adds a category restriction to the query as appropriate.
     *
     * @return void
     */
    private function addCategoryRestriction()
    {
        $categoryID = $this->state->get('categoryID');
        if (!empty($categoryID)) {
            $this->query->where("cat.id = '$categoryID'");
        }
    }

    /**
     * Gets an array of event categories.
     *
     * @return array the terms
     */
    private function getCategories()
    {
        $this->resetAdaptiveClauses();
        $this->query->select('DISTINCT cat.id, cat.name')
            ->where("l.delta != 'removed'")
            ->order('cat.name');

        $this->addDepartmentRestriction();
        $this->addPeriodRestriction();

        $this->_db->setQuery($this->query);

        $categories = OrganizerHelper::executeQuery('loadAssocList', [], 'id');
        if (empty($categories)) {
            return [];
        }

        foreach ($categories as &$category) {
            $category['total'] = [];
        }

        return $categories;
    }

    /**
     * Gets an array of departments.
     *
     * @return array the departments.
     */
    private function getDepartments()
    {
        $this->resetAdaptiveClauses();
        $this->query->select("DISTINCT dpt.id, dpt.short_name_$this->langTag AS name")
            ->where("l.delta != 'removed'")
            ->order("dpt.short_name_$this->langTag");

        $this->addPeriodRestriction();

        $this->_db->setQuery($this->query);

        $departments = OrganizerHelper::executeQuery('loadAssocList', [], 'id');
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
     * @return mixed  \JForm object on success, False on error.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_thm_organizer.lesson_statistics',
            'lesson_statistics',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        return !empty($form) ? $form : false;
    }

    /**
     * Gets an array of groups.
     *
     * @return array the groups
     */
    private function getGroups()
    {
        $this->resetAdaptiveClauses();
        $this->query->select('DISTINCT group.id, group.name')
            ->where("l.delta != 'removed'")
            ->order('group.name');

        $this->addDepartmentRestriction();
        $this->addPeriodRestriction();
        $this->addCategoryRestriction();

        $this->_db->setQuery($this->query);

        $pools = OrganizerHelper::executeQuery('loadAssocList', [], 'id');
        if (empty($pools)) {
            return [];
        }

        foreach ($pools as &$pool) {
            $pool['total'] = [];
        }

        return $pools;
    }

    /**
     * Gets an array of methods.
     *
     * @return array the methods
     */
    private function getMethods()
    {
        $this->resetAdaptiveClauses();
        $this->query->select("DISTINCT m.id, m.name_$this->langTag AS name")
            ->where("l.delta != 'removed'")
            ->order('name');

        $this->addDepartmentRestriction();
        $this->addPeriodRestriction();
        $this->addCategoryRestriction();

        $this->_db->setQuery($this->query);

        $methods = OrganizerHelper::executeQuery('loadAssocList', [], 'id');
        if (empty($methods)) {
            return [];
        }

        foreach ($methods as &$method) {
            if (empty($method['name'])) {
                $method['name'] = Languages::_('THM_ORGANIZER_NONE_GIVEN');
            }
            $method['total'] = [];
        }

        return $methods;
    }

    /**
     * Gets an array of terms.
     *
     * @return array the terms
     */
    private function getTerms()
    {
        $this->resetAdaptiveClauses();
        $this->query->select('DISTINCT term.*')
            ->where('term.startDate <= CURDATE()')
            ->where("l.delta != 'removed'")
            ->order('term.startDate DESC');

        $this->addDepartmentRestriction();
        $this->addCategoryRestriction();

        $this->_db->setQuery($this->query);

        $terms = OrganizerHelper::executeQuery('loadAssocList', [], 'id');
        if (empty($terms)) {
            return [];
        }

        foreach ($terms as &$term) {
            $term['total'] = [];
        }

        return $terms;
    }

    /**
     * Method to auto-populate the model state.
     *
     * @return void
     */
    protected function populateState()
    {
        parent::populateState();
        $defaultTerm = Terms::getCurrentID();
        $formData    = OrganizerHelper::getFormInput();

        $categoryID   = '';
        $departmentID = '';
        $termID       = $defaultTerm;
        if (!empty($formData)) {
            $termID = empty($formData['termID']) ? $defaultTerm : (int)$formData['termID'];

            $departmentSelected = !empty($formData['departmentID']);
            $departmentID       = $departmentSelected ? (int)$formData['departmentID'] : '';

            $categorySelected = !empty($formData['categoryID']);
            $categoryID       = $categorySelected ? (int)$formData['categoryID'] : '';
        }

        $this->setState('categoryID', $categoryID);
        $this->setState('departmentID', $departmentID);
        $this->setState('termID', $termID);
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
            ->innerJoin('#__thm_organizer_terms AS term ON term.id = l.termID')
            ->innerJoin('#__thm_organizer_departments AS dpt ON dpt.id = l.departmentID')
            ->innerJoin('#__thm_organizer_lesson_courses AS lcrs ON lcrs.lessonID = l.id')
            ->innerJoin('#__thm_organizer_lesson_groups AS lg on lg.lessonCourseID = lcrs.id')
            ->innerJoin('#__thm_organizer_groups AS group ON group.id = lg.groupID')
            ->innerJoin('#__thm_organizer_categories AS cat ON cat.id = group.categoryID')
            ->leftJoin('#__thm_organizer_methods AS m ON m.id = l.methodID');
    }

    /**
     * Creates an array of arrays with total values. Array[$rowID][$columnID] = $total.
     *
     * @returns void sets the model property $lessons
     */
    private function setLessonCounts()
    {
        $categoryID   = $this->state->get('categoryID');
        $departmentID = $this->state->get('departmentID');
        $termID       = $this->state->get('termID');
        $lessonCounts = [];
        foreach (array_keys($this->rows) as $rowID) {
            $lessons[$rowID] = [];
            foreach (array_keys($this->columns) as $columnID) {
                $this->resetAdaptiveClauses();
                $this->query->select('DISTINCT l.id')
                    ->where("l.delta != 'removed'");

                // Define column column
                if (empty($departmentID)) {
                    $column = empty($categoryID) ? 'dpt' : 'group';
                } else {
                    $this->query->where("l.departmentID = '$departmentID'");
                    $column = empty($categoryID) ? 'cat' : 'group';
                }
                $this->query->where("$column.id = '$columnID'");

                // Define row column
                if (empty($termID)) {
                    $this->query->where("term.id = '$rowID'");
                } else {
                    $this->query->where("term.id = '$termID'");
                    $clause = empty($rowID) ? 'm.id IS NULL' : "m.id = '$rowID'";
                    $this->query->where($clause);
                }

                $this->_db->setQuery($this->query);
                $lessons = OrganizerHelper::executeQuery('loadColumn', []);

                $lessonCounts[$rowID][$columnID] = count($lessons);

                // Eliminates inflated values for lessons associated with more than one column/row
                $totalLessons                      = array_merge($this->total, $lessons);
                $this->total                       = array_unique($totalLessons);
                $this->columns[$columnID]['total'] = array_unique(array_merge(
                    $this->columns[$columnID]['total'],
                    $lessons
                ));
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
