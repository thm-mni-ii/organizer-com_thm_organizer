<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerViewMappings
 * @description THM_OrganizerViewMappings component admin view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * Class THM_OrganizerViewMappings for component com_thm_organizer
 *
 * Class provides methods to display the view mappings
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerViewMappings extends JView
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
		JHTML::_('stylesheet', 'thm_curriculum.css', 'components/com_thm_organizer/assets/css/');

		$this->model = $this->getModel();
		$this->assets = $this->model->getItems();
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');

		// Preprocess the list of items to find ordering divisions
		foreach ($this->assets as &$item)
		{
			$this->ordering[$item->parent_id][] = $item->asset_id;
		}

		$_SESSION['stud_id'] = JRequest::getVar('id');

		// Levels filter
		$options = array();
		$options[] = JHtml::_('select.option', '1', JText::_('J1'));
		$options[] = JHtml::_('select.option', '2', JText::_('J2'));
		$options[] = JHtml::_('select.option', '3', JText::_('J3'));
		$options[] = JHtml::_('select.option', '4', JText::_('J4'));
		$options[] = JHtml::_('select.option', '5', JText::_('J5'));
		$options[] = JHtml::_('select.option', '6', JText::_('J6'));
		$options[] = JHtml::_('select.option', '7', JText::_('J7'));
		$options[] = JHtml::_('select.option', '8', JText::_('J8'));
		$options[] = JHtml::_('select.option', '9', JText::_('J9'));
		$options[] = JHtml::_('select.option', '10', JText::_('J10'));
		$this->assign('f_levels', $options);
		$this->addToolBar($this->model->getCurriculumName());

		parent::display($tpl);
	}

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @param   String  $title  Title
	 *
	 * @return  void
	 */
	protected function addToolBar($title)
	{
		$curr_name = $title['abschluss'] . ' ' . $title['fach'] . ' (' . $title['po'] . ')';
		JToolBarHelper::title(JText::_("com_thm_organizer_SUBMENU_CURRICULUM_TITLE") . $curr_name, 'generic.png');
		JToolBarHelper::addNew('mapping.add', 'JTOOLBAR_NEW');
		//JToolBarHelper::addNew('dummy_mapping.add', 'com_thm_organizer_INSERT_PLACEHOLDER_MAPPING');
		JToolBarHelper::addNew('fillpool.add', 'AutoFill');
		JToolBarHelper::editList('mapping.edit', 'JTOOLBAR_EDIT');
		JToolBarHelper::custom(
				'copy.save', 'copy.png', JPATH_BASE . DS . 'administrator' . DS .
				'com_thm_organizer' . DS . 'assets' . DS . 'images' . DS . 'copy.png', 'Copy', false, false
				);
		JToolBarHelper::deleteList('', 'mapping.delete', 'JTOOLBAR_DELETE');
		JToolBarHelper::publish('mapping.publish', 'JTOOLBAR_PUBLISH', true);
		JToolBarHelper::unpublish('mapping.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		JToolBarHelper::cancel('mappings.cancel', 'JTOOLBAR_CLOSE');
	}
}
