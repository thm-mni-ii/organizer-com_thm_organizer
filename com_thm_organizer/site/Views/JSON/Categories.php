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

use Organizer\Helpers\Input;
use Organizer\Helpers\Categories as CategoriesHelper;

/**
 * Class answers dynamic (degree) program related queries
 */
class Categories extends BaseView
{
    /**
     * loads model data into view context
     *
     * @return void
     */
    public function display()
    {
        $function = Input::getTask();
        if (method_exists('Organizer\\Helpers\\Categories', $function)) {
            echo json_encode(CategoriesHelper::$function(), JSON_UNESCAPED_UNICODE);
        } else {
            echo false;
        }
    }
}
