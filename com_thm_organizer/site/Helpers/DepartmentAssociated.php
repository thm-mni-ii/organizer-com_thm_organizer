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

/**
 * Ensures that resources associated with departments have functions pertaining to those associations.
 */
interface DepartmentAssociated
{
    /**
     * Retrieves the ids of departments associated with the resource
     *
     * @param int $resourceID the id of the resource for which the associated departments are requested
     *
     * @return array the ids of departments associated with the resource
     */
    public static function getDepartmentIDs($resourceID);
}
