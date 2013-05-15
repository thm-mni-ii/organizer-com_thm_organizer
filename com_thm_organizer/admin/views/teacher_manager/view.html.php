<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerViewTeacher_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Class provides methods to display a list of teachers
 *
 * @category    Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewTeacher_Manager extends JView
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
		$doc = JFactory::getDocument();
        $doc->addStyleSheet($this->baseurl . '/components/com_thm_organizer/assets/css/thm_organizer.css');

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
		JToolBarHelper::title(JText::_('COM_THM_ORGANIZER_TRM_TOOLBAR_TITLE'), 'generic.png');
		JToolBarHelper::addNew('teacher.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('teacher.edit', 'JTOOLBAR_EDIT');
        JToolBarHelper::custom('teacher.mergeAll', 'merge', 'merge', 'COM_THM_ORGANIZER_MERGE_ALL', false);
        JToolBarHelper::custom('teacher.mergeView', 'merge', 'merge', 'COM_THM_ORGANIZER_MERGE', true);
		JToolBarHelper::deleteList('', 'teacher.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_thm_organizer');
	}
}
