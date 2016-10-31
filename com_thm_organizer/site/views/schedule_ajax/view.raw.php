<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewSchedule_Ajax
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
jimport('joomla.application.plugin.helper');

/**
 * Class loading persistent data into the view context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewSchedule_Ajax extends JViewLegacy
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
		$model    = $this->getModel();
		$function = JFactory::getApplication()->input->getString('task');

		echo $model->$function();
	}
}
