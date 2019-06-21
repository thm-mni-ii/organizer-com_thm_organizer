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

/**
 * Class provides general functions for retrieving building data.
 */
class Buildings implements Selectable
{
    /**
     * Checks for the building entry in the database, creating it as necessary. Adds the id to the building entry in the
     * schedule.
     *
     * @param string $name the building name
     *
     * @return mixed  int the id if the room could be resolved/added, otherwise null
     */
    public static function getID($name)
    {
        $table   = OrganizerHelper::getTable('Buildings');
        $data    = ['name' => $name];
        $success = $table->load($data);

        if ($success) {
            return $table->id;
        }

        // Entry not found
        $success = $table->save($data);

        return $success ? $table->id : null;
    }

    /**
     * Retrieves the selectable options for the resource.
     *
     * @return array the available options
     */
    public static function getOptions()
    {
        $buildings = self::getResources();
        if (empty($buildings)) {
            return $buildings;
        }

        $options = [];
        for ($index = 0; $index < count($buildings); $index++) {
            $thisBuilding = $buildings[$index];
            $buildingName = $thisBuilding['name'];

            $listEnd          = empty($buildings[$index + 1]);
            $standardHandling = ($listEnd or $thisBuilding['name'] != $buildings[$index + 1]['name']);

            if ($standardHandling) {
                $buildingName .= empty($thisBuilding['campusName']) ? '' : " ({$thisBuilding['campusName']})";
                $options[]    = HTML::_('select.option', $thisBuilding['id'], $buildingName);
                continue;
            }

            // The campus name is relevant to unique identification
            $nextBuilding = $buildings[$index + 1];

            $thisCampusID = empty($thisBuilding['parentID']) ? $thisBuilding['campusID'] : $thisBuilding['parentID'];
            $nextCampusID = empty($nextBuilding['parentID']) ? $nextBuilding['campusID'] : $nextBuilding['parentID'];

            $thisBuilding['campusName'] = Campuses::getName($thisCampusID);
            $nextBuilding['campusName'] = Campuses::getName($nextCampusID);

            if ($thisBuilding['campusName'] < $nextBuilding['campusName']) {
                $buildingID   = $thisBuilding['id'];
                $buildingName .= " ({$thisBuilding['campusName']})";

                $buildings[$index + 1] = $nextBuilding;
            } else {
                $buildingID   = $nextBuilding['id'];
                $buildingName .= " ({$nextBuilding['campusName']})";

                $buildings[$index + 1] = $thisBuilding;
            }

            $options[] = HTML::_('select.option', $buildingID, $buildingName);
        }

        return $options;
    }

    /**
     * Retrieves the resource items.
     *
     * @return array the available resources
     */
    public static function getResources()
    {
        $input    = OrganizerHelper::getInput();
        $formData = OrganizerHelper::getFormInput();
        $campusID = (empty($formData) or empty($formData['campusID'])) ?
            $input->getInt('campusID') : (int)$formData['campusID'];

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('DISTINCT b.*, c.parentID');
        $query->from('#__thm_organizer_buildings AS b')
            ->leftJoin('#__thm_organizer_campuses AS c ON c.id = b.campusID');

        if (!empty($campusID)) {
            $query->where("(c.id = '$campusID' OR c.parentID = '$campusID')");
        }

        $query->order('name');
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }
}
