<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerControllerCurriculum
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');

/**
 * Class THM_OrganizerControllerCurriculum for component com_thm_organizer
 *
 * Class provides methods for AJAX Requests
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerControllerScheduler_Tree extends JControllerAdmin
{
	/**
	 * Redirects to the ajax information which represents the view
	 *
	 * @return  void
	 */
	public function load()
	{
		$this->input->set('view', 'scheduler_tree');
		$this->input->set('format', 'raw');
		parent::display();
	}
}
