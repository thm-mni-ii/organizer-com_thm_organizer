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

defined('_JEXEC') or die;

use Organizer\Helpers\Access;
use Joomla\CMS\Factory;
use Organizer\Helpers\Mappings;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class retrieves information for a filtered set of subjects.
 */
class Subject_Manager extends ListModelMenu
{
    public $programs = null;

    public $pools = null;

    /**
     * Method to select all existent assets from the database
     *
     * @return \JDatabaseQuery  the query object
     */
    protected function getListQuery()
    {
        $allowedDepartments = Access::getAccessibleDepartments('document');
        $dbo                = Factory::getDbo();
        $shortTag           = Languages::getShortTag();

        // Create the sql query
        $query  = $dbo->getQuery(true);
        $select = "DISTINCT s.id, externalID, s.name_$shortTag AS name, field_$shortTag AS field, color, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=subject_edit&id='", 's.id'];
        $select .= $query->concatenate($parts, '') . ' AS link ';
        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_fields AS f ON s.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->where('(s.departmentID IN (' . implode(',', $allowedDepartments) . ') OR s.departmentID IS NULL)');

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

        $this->setSearchFilter($query, $searchFields);
        $this->setValueFilters($query, ['externalID']);
        $this->setLocalizedFilters($query, ['name', 'field']);

        $programID = $this->state->get('list.programID', '');
        Mappings::setResourceIDFilter($query, $programID, 'program', 'subject');
        $poolID = $this->state->get('list.poolID', '');
        Mappings::setResourceIDFilter($query, $poolID, 'pool', 'subject');
        $isPrepCourse = $this->state->get('list.is_prep_course', '');
        if ($isPrepCourse !== "") {
            $query->where("is_prep_course = $isPrepCourse");
        }

        $this->setOrdering($query);

        return $query;
    }
}
