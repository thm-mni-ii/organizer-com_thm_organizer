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

/**
 * Class answers dynamic planning period related queries
 */
class Planning_Period extends \Joomla\CMS\MVC\View\HtmlView
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
        $model = $this->getModel();
        $task  = OrganizerHelper::getInput()->getString('task');
        echo $model->$task();
    }
}
