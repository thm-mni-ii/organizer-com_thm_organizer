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
use Organizer\Helpers\Subjects as SubjectsHelper;

/**
 * Class answers dynamic subject related queries
 */
class Subjects extends BaseView
{
    /**
     * loads model data into view context
     *
     * @return void
     */
    public function display()
    {
        $function = OrganizerHelper::getInput()->getString('task');
        echo json_encode(SubjectsHelper::$function());
    }
}
