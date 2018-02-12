<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
jimport('joomla.application.plugin.helper');

/**
 * Class answers dynamic schedule related queries
 */
class THM_OrganizerViewSchedule_Ajax extends JViewLegacy
{
    /**
     * loads model data into view context
     *
     * @param string $tpl the name of the template to be used
     *
     * @return void
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function display($tpl = null)
    {
        $model    = $this->getModel();
        $function = JFactory::getApplication()->input->getString('task');

        echo $model->$function();
    }
}
