<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\JSON;

use Organizer\Helpers\Input;
use Organizer\Helpers\Persons as TeachersHelper;

/**
 * Class answers dynamic teacher related queries
 */
class Teachers extends BaseView
{
    /**
     * loads model data into view context
     *
     * @return void
     */
    public function display()
    {
        $function = Input::getTask();
        if (method_exists('Organizer\\Helpers\\Persons', $function)) {
            echo json_encode(TeachersHelper::$function());
        } else {
            echo false;
        }
    }
}
