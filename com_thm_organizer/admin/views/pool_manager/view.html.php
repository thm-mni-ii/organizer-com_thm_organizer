<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewPool_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Class THM_OrganizerViewPool_Manager for component com_thm_organizer
 *
 * @category    Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewPool_Manager extends JView
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

		$model = $this->getModel();
		$this->pools = $model->getItems();
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');
        $this->programName = $model->programName;

        $this->programSelect = $this->getProgramSelect($model->programs);

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
        $baseTitle = JText::_("COM_THM_ORGANIZER_POM_TOOLBAR_TITLE");
        $title = empty($this->programName)? $baseTitle : $baseTitle . " - " . $this->programName;
		JToolBarHelper::title($title, 'generic.png');
		JToolBarHelper::addNew('pool.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('pool.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::deleteList('COM_THM_ORGANIZER_POM_DELETE_CONFIRM', 'pool.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_thm_organizer');
	}

    /**
     * Retrieves a select box with the mapped programs
     * 
     * @param   array  $programs  the mapped programs
     * 
     * @return  string  html select box
     */
    private function getProgramSelect($programs)
    {
        $selectPrograms = array();
        $selectPrograms[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_POM_SEARCH_PROGRAM'));
        $selectPrograms[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_POM_ALL_PROGRAMS'));
        $programs = array_merge($selectPrograms, $programs);
        return JHTML::_('select.genericlist', $programs, 'filter_program', 'onchange="this.form.submit();"', 'id', 'name', $this->state->get('filter.program'));
    }
}
