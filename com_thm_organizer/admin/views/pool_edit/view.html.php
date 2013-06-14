<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerViewPool_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
jimport('jquery.jquery');
/**
 * Class THM_OrganizerViewPool_Edit for component com_thm_organizer
 * Class provides methods to display the view course pool
 *
 * @category    Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewPool_Edit extends JView
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
		JHtml::_('behavior.tooltip');
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript($this->baseurl . "/components/com_thm_organizer/assets/js/mapping.js");

		// Get the Data
		$form = $this->get('Form');
		$item = $this->get('Item');
        $poolID = empty($item->id)? JRequest::getInt('id') : $item->id;
        $this->_layout = empty($poolID)? 'add' : 'edit';
        if (!empty($poolID))
        {
            $this->children = $this->getModel()->children;
        }

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
		$isNew = ($this->item->id == 0);
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title($isNew ? JText::_('COM_THM_ORGANIZER_POM_NEW_TITLE') : JText::_('COM_THM_ORGANIZER_POM_EDIT_TITLE'));
		JToolBarHelper::apply('pool.apply', $isNew ? 'COM_THM_ORGANIZER_APPLY_NEW' : 'COM_THM_ORGANIZER_APPLY_EDIT');
        JToolBarHelper::save('pool.save');
		JToolBarHelper::cancel('pool.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
