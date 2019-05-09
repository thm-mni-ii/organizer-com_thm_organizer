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

defined('_JEXEC') or die;

use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Plan_Programs as Plan_ProgramsHelper;

/**
 * Class answers dynamic (degree) program related queries
 */
class Plan_Programs extends BaseView
{
    /**
     * loads model data into view context
     *
     * @param string $tpl the name of the template to be used
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function display($tpl = null)
    {
        $function = OrganizerHelper::getInput()->getString('task');
        echo json_encode(Plan_ProgramsHelper::$function());
    }
}
