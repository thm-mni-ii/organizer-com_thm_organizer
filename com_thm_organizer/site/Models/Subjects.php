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
use Organizer\Helpers\Mappings;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Subjects as SubjectsHelper;

/**
 * Class retrieves information for a filtered set of subjects.
 */
class Subjects extends ListModelMenu
{
    const ALPHA = 0;

    const NUMBER = 1;

    const POOL = 2;

    const TEACHER = 3;

    private $admin = true;

    /**
     * Filters out form inputs which should not be displayed due to menu settings.
     *
     * @param Form $form the form to be filtered
     *
     * @return void modifies $form
     */
    protected function filterFilterForm(&$form)
    {
        $form->removeField('filter.isPrepCourse');
        $form->removeField('programID');

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
        $dbo      = Factory::getDbo();
        $shortTag = Languages::getShortTag();

        // Create the sql query
        $query  = $dbo->getQuery(true);
        $select = "DISTINCT s.id, s.externalID, s.name_$shortTag AS name, s.fieldID, s.creditpoints, ";
        $parts  = ["'index.php?option=com_thm_organizer&id='", 's.id'];
        $select .= $query->concatenate($parts, '') . ' AS url ';
        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');

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
        $isPrepCourse = $this->state->get('list.is_prep_course', '');
        if ($isPrepCourse !== "") {
            $query->where("is_prep_course = $isPrepCourse");
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
     * Overrides state properties with menu settings values.
     *
     * @return void sets state properties
     */
    protected function populateStateFromMenu()
    {
        $this->admin = false;
        $params      = OrganizerHelper::getParams();
        if (empty($params->get('programID'))) {
            return;
        }
        $this->state->set('filter.programID', $params->get('programID'));
        if ($this->state->get('list.grouping') === null) {
            $this->state->set('list.grouping', $params->get('groupBy', '0'));
        }

        return;
    }
}
