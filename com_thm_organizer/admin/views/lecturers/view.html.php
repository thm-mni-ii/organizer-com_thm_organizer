<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerViewLecturers
 * @description THM_OrganizerViewLecturers component admin view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Class THM_OrganizerViewLecturers for component com_thm_organizer
 *
 * Class provides methods to display the view lecturers
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerViewLecturers extends JView
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
		JToolBarHelper::title('THM CURRICULUM: Dozenten', 'generic.png');

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
		JToolBarHelper::title(JText::_('com_thm_organizer_SUBMENU_LECTURERS_TITLE'), 'generic.png');

		JToolBarHelper::addNew('lecturer.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('lecturer.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::deleteList('', 'lecturer.delete', 'JTOOLBAR_DELETE');
	}
}
