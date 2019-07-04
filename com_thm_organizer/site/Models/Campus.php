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
use Organizer\Helpers\Input;

/**
 * Class which manages stored campus data.
 */
class Campus extends BaseModel
{
    /**
     * Authenticates the user
     */
    protected function allow()
    {
        return Access::isAdmin();
    }

    /**
     * Attempts to save the resource.
     *
     * @return bool true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function save()
    {
        if ($parentID = Input::getInt('parentID')) {
            $table = $this->getTable();
            $table->load($parentID);
            if (!empty($table->parentID)) {
                // TODO: add a message saying that it failed because the maximum depth was reached.
                return false;
            }
        }

        return parent::save();
    }
}
