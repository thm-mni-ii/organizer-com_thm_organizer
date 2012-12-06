<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		THM_OrganizerControllerCurriculum
 * @description THM_OrganizerControllerCurriculum component site controller
 * @author	   	Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla controllerform library
jimport('joomla.application.component.controlleradmin');

/**
 * Class THM_OrganizerControllerCurriculum for component com_thm_organizer
 *
 * Class provides methods for AJAX Requests
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerControllerCurriculum extends JControllerAdmin
{
	/**
	 * Major id
	 *
	 * @var    Integer
	 * @since  1.0
	 */
	public $majorID = null;

	/**
	 * Major organizer
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $organizer_major = null;

	/**
	 * Curriculum model
	 *
	 * @var    Object
	 * @since  1.0
	 */
	public $curriculumModel = null;

	/**
	 * Method for AJAX Callback
	 *
	 * @return void
	 */
	public function getJSONCurriculum()
	{
		$this->curriculumModel = $this->getModel("curriculum");
		$this->curriculumModel->getJSONCurriculum();
	}

}
