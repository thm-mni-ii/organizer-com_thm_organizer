<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\JSON;

use Organizer\Helpers\Rooms;

/**
 * Class answers dynamic room related queries
 */
class RoomOptions extends BaseView
{
    /**
     * loads model data into view context
     *
     * @return void
     */
    public function display()
    {
        echo json_encode(Rooms::getOptions(), JSON_UNESCAPED_UNICODE);
    }
}
