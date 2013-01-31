<?php
/**
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerViewAssets
 * @description THM_OrganizerViewAssets component admin view
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
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
 */
class THM_OrganizerViewAssets extends JView
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
		JHTML::_('behavior.modal', 'a.modal-button');
		JHtml::_('behavior.tooltip');

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');

		// Levels filter
		$options = array();
		$options[] = JHtml::_('select.option', '1', JText::_('COM_THM_ORGANIZER_COURSES'));
		$options[] = JHtml::_('select.option', '2', JText::_('COM_THM_ORGANIZER_POOLS'));
		$options[] = JHtml::_('select.option', '3', JText::_('COM_THM_ORGANIZER_DUMMIES'));

		$this->assign('f_levels', $options);

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
		JToolBarHelper::title(JText::_('COM_THM_ORGANIZER') . ': ' . JText::_('COM_THM_ORGANIZER_SUBMENU_ASSETS_TITLE'), 'generic.png');
		JToolBarHelper::addNew('course.add', JText::_('COM_THM_ORGANIZER_SUBMENU_ASSETS_ADD_COURSE'));
		JToolBarHelper::addNew('coursepool.add', JText::_('COM_THM_ORGANIZER_SUBMENU_ASSETS_ADD_GROUP'));
		JToolBarHelper::addNew('dummy.add', JText::_('COM_THM_ORGANIZER_SUBMENU_ASSETS_ADD_DUMMY'));
		JToolBarHelper::editList('course.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::deleteList('', 'assets.delete', 'JTOOLBAR_DELETE');
	}
}
