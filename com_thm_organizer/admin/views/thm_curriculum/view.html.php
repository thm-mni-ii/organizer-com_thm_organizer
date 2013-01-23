<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerViewAssets
 * @description THM_OrganizerViewAssets component admin view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Class THM_OrganizerViewAssets for component com_thm_organizer
 *
 * Class provides methods to display the view assets
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerViewTHM_Curriculum extends JView
{
	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$document = & JFactory::getDocument();
		$document->addStyleSheet($this->baseurl . '/components/com_thm_organizer/assets/css/thm_curriculum.css');

		JToolBarHelper::title('THM CURRICULUM', 'generic.png');
		parent::display($tpl);
	}
}
