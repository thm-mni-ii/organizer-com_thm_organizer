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

use Organizer\Helpers\Departments as DepartmentsHelper;
use Organizer\Helpers\Input;

/**
 * Class answers dynamic organizational related queries
 */
class Departments extends BaseView
{
    /**
     * loads model data into view context
     *
     * @return void
     */
    public function display()
    {
        $function = Input::getTask();
        if (method_exists('Organizer\\Helpers\\Departments', $function)) {
            echo json_encode(DepartmentsHelper::$function(), JSON_UNESCAPED_UNICODE);
        } else {
            echo false;
        }
    }
}
