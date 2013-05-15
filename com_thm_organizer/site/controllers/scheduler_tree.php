<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerControllerCurriculum
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
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
class THM_OrganizerControllerScheduler_Tree extends JController
{
    /**
     * Redirects to the ajax information which represents the view
     */
	public function load()
	{
	    $this->setRedirect(JRoute::_("index.php?option=com_thm_organizer&view=scheduler_tree&format=raw", false));
	}
}
