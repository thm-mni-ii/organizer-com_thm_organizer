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
		JToolBarHelper::title(JText::_("COM_THM_ORGANIZER_POM_TOOLBAR_TITLE"), 'generic.png');
		JToolBarHelper::addNew('pool.add', 'JTOOLBAR_NEW');
		JToolBarHelper::editList('pool.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::deleteList('', 'pool.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::cancel('pools.cancel', 'JTOOLBAR_CLOSE');
	}
}
