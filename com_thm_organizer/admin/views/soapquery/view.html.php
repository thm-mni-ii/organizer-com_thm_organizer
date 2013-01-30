<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerViewSoapquery
 * @description THM_OrganizerViewSoapquery component admin view
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Class THM_OrganizerViewSoapquery for component com_thm_organizer
 *
 * Class provides methods to display the view soap query
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerViewSoapquery extends JView
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

		// Check for errors
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Assign the Data
		$this->form = $form;
		$this->item = $item;

		// Set the toolbar
		$this->addToolBar();
		parent::display($tpl);
	}

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return  void
	 */
	protected function addToolBar()
	{
		$isNew = $this->item->id == 0;
		JToolBarHelper::title($isNew ? JText::_("COM_THM_ORGANIZER_SOAP_QUERIES_NEW") : JText::_("COM_THM_ORGANIZER_SOAP_QUERIES_EDIT"));
		JToolBarHelper::apply('soapquery.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('soapquery.save', 'JTOOLBAR_SAVE');
		JToolBarHelper::cancel('soapquery.cancel', 'JTOOLBAR_CANCEL');
	}
}
