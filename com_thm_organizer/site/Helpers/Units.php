<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;

/**
 * Provides general function for data retrieval and display.
 */
class Units extends ResourceHelper
{
    /**
     * Retrieves the id of events associated with the resource
     *
     * @param int $resourceID the id of the resource for which the associated events are requested
     *
     * @return id of events associated with the resource
     */
    public static function getEventID($resourceID)
    {

        $dbo = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select('DISTINCT eventID');

        $query->from('#__thm_organizer_units AS u')
            ->innerJoin('#__thm_organizer_instances AS i ON i.unitID = u.id')
            ->where("unitID = $resourceID");

        $dbo->setQuery($query);

        $eventID = OrganizerHelper::executeQuery('loadColumn', []);

        return $eventID;

    }
}