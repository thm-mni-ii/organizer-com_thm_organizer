<?php
/**
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerViewColor
 * @description THM_OrganizerViewColor component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Class THM_OrganizerViewColor for component com_thm_organizer
 *
 * Class provides methods to display the view color
 *
 * @category    Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewColor_Edit extends JView
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
        if (!thm_organizerHelper::isAdmin('color'))
        {
            thm_organizerHelper::noAccess();
        }

		JHtml::_('behavior.tooltip');
		$document = JFactory::getDocument();
		$document->addScript('/administrator/components/com_thm_organizer/views/color/tmpl/colorpicker/jscolor.js');

		$form = $this->get('Form');
		$item = $this->get('Item');

		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		$this->form = $form;
		$this->item = $item;

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
		JRequest::setVar('hidemainmenu', true);
		$isNew = ($this->item->id == 0);
		JToolBarHelper::title($isNew ?
			JText::_('COM_THM_ORGANIZER_CLM_NEW_TITLE') : JText::_('COM_THM_ORGANIZER_CLM_EDIT_TITLE'));
		JToolBarHelper::save('color.save');
		JToolBarHelper::cancel('color.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
