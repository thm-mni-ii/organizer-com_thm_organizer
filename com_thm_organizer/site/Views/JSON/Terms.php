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
use Organizer\Helpers\Terms as TermsHelper;

/**
 * Class answers dynamic term related queries
 */
class Terms extends BaseView
{
    /**
     * loads model data into view context
     *
     * @return void
     */
    public function display()
    {
        $function = Input::getTask();
        if (method_exists('Organizer\\Helpers\\Terms', $function)) {
            echo json_encode(TermsHelper::$function());
        } else {
            echo false;
        }
    }
}
