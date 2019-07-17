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

use JDatabaseQuery;
use Joomla\CMS\Form\Form;
use Organizer\Helpers\Access;
use Joomla\CMS\Factory;
use Organizer\Helpers\Input;
use Organizer\Helpers\Mappings;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Subjects as SubjectsHelper;

/**
 * Class retrieves information for a filtered set of subjects.
 */
class Subjects extends ListModel implements FiltersFormFilters
{
    private $admin = true;

    /**
     * Filters out form inputs which should not be displayed due to menu settings.
     *
     * @param Form $form the form to be filtered
     *
     * @return void modifies $form
     */
    public function filterFilterForm(&$form)
    {
        $params = Input::getParams();
        if (!empty($params->get('programID'))) {
            $form->removeField('departmentID', 'filter');
            $form->removeField('limit', 'list');
            $form->removeField('programID', 'filter');
        } else {
            $form->removeField('languageTag', 'list');

            if ($this->admin) {
                $allowedDepartments = Access::getAccessibleDepartments('documentation');
                if (count($allowedDepartments) === 1) {
                    $form->removeField('departmentID', 'filter');
                }
            }
        }

        return;
    }

    /**
     * Method to get an array of data items.
     *
     * @return  array  item objects on success, otherwise empty
     */
    public function getItems()
    {
        $items = parent::getItems();

        if (empty($items)) {
            return [];
        }

        foreach ($items as $item) {
            $item->teachers = SubjectsHelper::getTeachers($item->id);
        }

        return $items;
    }

    /**
     * Method to select all existent assets from the database
     *
     * @return JDatabaseQuery  the query object
     */
    protected function getListQuery()
    {
        $dbo = Factory::getDbo();
        $tag = Languages::getTag();

        // Create the sql query
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT s.id, s.externalID, s.name_$tag AS name, s.fieldID, s.creditpoints")
            ->from('#__thm_organizer_subjects AS s');

        $searchFields = [
            's.name_de',
            'short_name_de',
            'abbreviation_de',
            's.name_en',
            'short_name_en',
            'abbreviation_en',
            'externalID',
            'description_de',
            'objective_de',
            'content_de',
            'description_en',
            'objective_en',
            'content_en',
            'lsfID'
        ];

        $this->setDepartmentFilter($query);
        $this->setSearchFilter($query, $searchFields);

        $programID = $this->state->get('filter.programID', '');
        Mappings::setResourceIDFilter($query, $programID, 'program', 'subject');
        $poolID = $this->state->get('filter.poolID', '');
        Mappings::setResourceIDFilter($query, $poolID, 'pool', 'subject');
        $teacherID = $this->state->get('filter.teacherID', '');
        if (!empty($teacherID)) {
            if ($teacherID === '-1') {
                $query->leftJoin('#__thm_organizer_subject_teachers AS st ON st.subjectID = s.id')
                    ->where('st.subjectID IS NULL');
            } else {
                $query->innerJoin('#__thm_organizer_subject_teachers AS st ON st.subjectID = s.id')
                    ->where("st.teacherID = $teacherID");
            }
        }

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Sets restrictions to the subject's departmentID field
     *
     * @param JDatabaseQuery &$query the query to be modified
     *
     * @return void modifies the query
     */
    private function setDepartmentFilter(&$query)
    {
        if ($this->admin) {
            $allowedDepartments = Access::getAccessibleDepartments('document');
            $query->where('(s.departmentID IN (' . implode(',', $allowedDepartments) . ') OR s.departmentID IS NULL)');
        }
        $departmentID = $this->state->get('filter.departmentID');
        if (empty($departmentID)) {
            return;
        } elseif ($departmentID == '-1') {
            $query->where('(s.departmentID IS NULL)');
        }
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param string $ordering  An optional ordering field.
     * @param string $direction An optional direction (asc|desc).
     *
     * @return void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $this->admin = OrganizerHelper::getApplication()->isClient('administrator') ? true : false;
        if ($this->admin) {
            $allowedDepartments = Access::getAccessibleDepartments('documentation');
            if (count($allowedDepartments) === 1) {
                $this->state->set('filter.departmentID', $allowedDepartments[0]);
            }
        } else {
            $params = Input::getParams();
            if (!empty($params->get('programID'))) {
                $this->state->set('filter.programID', $params->get('programID'));
                $this->state->set('list.limit', 0);
            }
        }

        return;
    }
}
