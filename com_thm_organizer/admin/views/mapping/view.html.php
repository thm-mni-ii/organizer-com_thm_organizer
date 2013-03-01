<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewMapping
 * @description THM_OrganizerViewMapping component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla view library
jimport('joomla.application.component.view');

/**
 * Class THM_OrganizerViewMapping for component com_thm_organizer
 *
 * Class provides methods to display the view mapping
 *
 * @category    Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerViewMapping extends JView
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
		// Get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');

		// Assign the Data
		$this->form = $form;
		$this->item = $item;

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return  void
	 */
	protected function addToolBar()
	{
		JRequest::setVar('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		JToolBarHelper::title(
				$isNew ? JText::_('COM_THM_ORGANIZER_SUBMENU_MODULMAPPINGS_NEW_MAPPING') :
				JText::_('COM_THM_ORGANIZER_SUBMENU_MODULMAPPINGS_EDIT_MAPPING')
		);
		JToolBarHelper::save('mapping.save');
		JToolBarHelper::save2new('mapping.save2new');
		JToolBarHelper::cancel('mapping.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
