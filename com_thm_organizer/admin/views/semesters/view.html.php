<?php
/**
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerViewSemesters
 * @description THM_OrganizerViewSemesters component admin view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * Class THM_OrganizerViewSemesters for component com_thm_organizer
 * Class provides methods to display the view semesters
 *
 * @category    Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewSemesters extends JView
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

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
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
		JToolBarHelper::title(JText::_('COM_THM_ORGANIZER') . ': ' . JText::_('COM_THM_ORGANIZER_SUBMENU_SEMESTERS_TITLE'), 'generic.png');
		JToolBarHelper::addNew('semester.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('semester.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::deleteList('', 'semester.delete', 'JTOOLBAR_DELETE');
	}
}
