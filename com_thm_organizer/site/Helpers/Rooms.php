<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

/**
 * Class provides general functions for retrieving room data.
 */
class Rooms extends ResourceHelper implements Selectable
{
    use Filtered;

    /**
     * Retrieves a list of resources in the form of name => id.
     *
     * @return array the resources, or empty
     */
    public static function getOptions()
    {
        $options = [];
        foreach (self::getResources() as $room) {
            $options[] = HTML::_('select.option', $room['id'], $room['name']);
        }

        return $options;
    }

    /**
     * Retrieves the ids for filtered rooms used in events.
     *
     * @return array the rooms used in actual events which meet the filter criteria
     */
    public static function getPlannedRooms()
    {
        $allRooms = self::getResources();
        $default  = [];

        if (empty($allRooms)) {
            return $default;
        }

        $app           = OrganizerHelper::getApplication();
        $dbo           = Factory::getDbo();
        $relevantRooms = [];

        $selectedDepartment = $app->input->getInt('departmentIDs');
        $selectedCategories = explode(',', $app->input->getString('categoryIDs'));
        $categoryIDs        = $selectedCategories[0] > 0 ?
            implode(',', ArrayHelper::toInteger($selectedCategories)) : '';

        $query = $dbo->getQuery(true);
        $query->select('COUNT(DISTINCT lcnf.id)')
            ->from('#__thm_organizer_lesson_configurations AS lcnf')
            ->innerJoin('#__thm_organizer_lesson_courses AS lcrs ON lcrs.id = lcnf.lessonCourseID')
            ->innerJoin('#__thm_organizer_lesson_groups AS lg ON lg.lessonCourseID = lcrs.id')
            ->innerJoin('#__thm_organizer_groups AS gr ON gr.id = lg.groupID')
            ->innerJoin('#__thm_organizer_department_resources AS dr ON dr.categoryID = gr.categoryID');

        foreach ($allRooms as $room) {
            $query->clear('where');
            // Negative lookaheads are not possible in MySQL and POSIX (e.g. [[:colon:]]) is not in MariaDB
            // This regex is compatible with both
            $regex = '"rooms":\\{("[0-9]+":"[\w]*",)*"' . $room['id'] . '":("new"|"")';
            $query->where("lcnf.configuration REGEXP '$regex'");

            if (!empty($selectedDepartment)) {
                $query->where("dr.departmentID = $selectedDepartment");

                if (!empty($categoryIDs)) {
                    $query->where("gr.programID in ($categoryIDs)");
                }
            }

            $dbo->setQuery($query);

            $count = OrganizerHelper::executeQuery('loadResult');

            if (!empty($count)) {
                $relevantRooms[$room['name']] = ['id' => $room['id'], 'typeID' => $room['typeID']];
            }
        }

        ksort($relevantRooms);

        return $relevantRooms;
    }

    /**
     * Retrieves all room entries which match the given filter criteria. Ordered by their display names.
     *
     * @return array the rooms matching the filter criteria or empty if none were found
     */
    public static function getResources()
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT r.id, r.*")
            ->from('#__thm_organizer_rooms AS r');

        // Type is the more common parameter, roomtype is only used in the schedule_grid context.
        self::addResourceFilter($query, 'type', 'rt', 'r', 'roomtypes');
        self::addResourceFilter($query, 'type', 'rt', 'r', 'roomtypes', 'roomtype');

        self::addResourceFilter($query, 'building', 'b1', 'r');

        // This join is used specifically to filter campuses independent of buildings.
        $query->leftJoin('#__thm_organizer_buildings AS b2 ON b2.id = r.buildingID');
        self::addCampusFilter($query, 'b2');

        $query->order('name');
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }
}
