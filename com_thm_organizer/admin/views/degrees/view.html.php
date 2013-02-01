<?php
/**
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerViewDegrees
 * @description THM_OrganizerViewDegrees component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Class THM_OrganizerViewDegrees for component com_thm_organizer
 * Class provides methods to display the view degrees
 *
 * @category    Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerViewDegrees extends JView
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

		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		$this->items = $items;
		$this->pagination = $pagination;
		$this->state = $this->get('State');
				
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
		JToolBarHelper::title(JText::_('COM_THM_ORGANIZER') . ': ' . JText::_('COM_THM_ORGANIZER_SUBMENU_DEGREES_TITLE'), 'generic.png');

		JToolBarHelper::addNew('degree.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('degree.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::deleteList('', 'degree.delete', 'JTOOLBAR_DELETE');
	}
}
