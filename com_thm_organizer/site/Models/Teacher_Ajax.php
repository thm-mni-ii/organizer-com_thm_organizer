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

use Joomla\CMS\Factory;
use Organizer\Helpers\Mappings;
use Organizer\Helpers\Teachers;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class which retrieves dynamic teacher information.
 */
class Teacher_Ajax extends BaseModel
{
    /**
     * Gets the pool options as a string
     *
     * @return string the concatenated plan pool options
     */
    public function getPlanOptions()
    {
        $planOptions = Teachers::getPlanTeachers();

        return json_encode($planOptions);
    }

    /**
     * Retrieves teacher entries from the database
     *
     * @return string  the teachers who hold courses for the selected program and pool
     */
    public function teachersByProgramOrPool()
    {
        $input     = OrganizerHelper::getInput();
        $programID = $input->getString('programID');
        $poolID    = $input->getString('poolID');

        if (!empty($poolID) and $poolID != '-1' and $poolID != 'null') {
            $resourceType = 'pool';
            $resourceID   = $poolID;
        } else {
            $resourceType = 'program';
            $resourceID   = $programID;
        }

        $boundarySet = Mappings::getBoundaries($resourceType, $resourceID);

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT t.id, t.forename, t.surname')->from('#__thm_organizer_teachers AS t');
        $query->innerJoin('#__thm_organizer_subject_teachers AS st ON st.teacherID = t.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.subjectID = st.subjectID');
        if (!empty($boundarySet)) {
            $where   = '';
            $initial = true;
            foreach ($boundarySet as $boundaries) {
                $where   .= $initial ?
                    "((m.lft >= '{$boundaries['lft']}' AND m.rgt <= '{$boundaries['rgt']}')"
                    : " OR (m.lft >= '{$boundaries['lft']}' AND m.rgt <= '{$boundaries['rgt']}')";
                $initial = false;
            }

            $query->where($where . ')');
        }

        $query->order('t.surname');
        $dbo->setQuery($query);

        $teachers = OrganizerHelper::executeQuery('loadObjectList');
        if (empty($teachers)) {
            return '[]';
        }

        foreach ($teachers as $key => $value) {
            $teachers[$key]->name = empty($value->forename) ?
                $value->surname : $value->surname . ', ' . $value->forename;
        }

        return json_encode($teachers);
    }
}