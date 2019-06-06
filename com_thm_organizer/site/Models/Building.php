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

use Exception;
use Organizer\Helpers\Access;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class which manages stored building data.
 */
class Building extends BaseModel
{
    /**
     * Authenticates the user
     */
    protected function allow()
    {
        return Access::allowFMAccess();
    }
}
