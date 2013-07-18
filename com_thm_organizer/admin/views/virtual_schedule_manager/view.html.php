<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.view
 * @name        THM_OrganizerViewVirtual_Schedule_Manager
 * @description provides a list of virtual schedules
 * @author      Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class THM_OrganizerViewVirtual_Schedule_Manager for component com_thm_organizer
 * Class provides methods to display a list of virtual schedules
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.view
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewVirtual_Schedule_Manager extends JView
{
	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (Default: null)
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		if (!JFactory::getUser()->authorise('core.admin'))
		{
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$document = JFactory::getDocument();
		$document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");

		$app = JFactory::getApplication("administrator");
		$filter_order = $app->getUserStateFromRequest(
				".filter_order", 'filter_order',
				'vs.semesterID, vs.vid', ''
		);
		$filter_order_Dir = $app->getUserStateFromRequest(".filter_order_Dir", 'filter_order_Dir',	'', '');

		// Table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		$model = $this->getModel();

		$elements = array();
		foreach ($model->getElements() as $element)
		{
			if (!isset($elements[$element->vid]))
			{
				$elements[$element->vid] = $element;
			}
			else
			{
				$elements[$element->vid]->eid .= ";" . $element->eid;
			}
		}

		$items = $this->get('Data');
		foreach ($items as $item)
		{
			foreach ($elements as $element)
			{
				if ($item->id == $element->vid)
				{
					$item->eid = $element->eid;
				}
			}
		}

		// Assign data to template
		$this->assignRef('lists', $lists);
		$this->assignRef('items', $items);
		$this->pagination = $this->get('Pagination');
		$this->assignRef('lists', $lists);

        $this->addToolBar();
		parent::display($tpl);
	}

	/**
	 * Method to add the toolbar
	 *
	 * @return  void
	 */
	private function addToolBar()
	{
		$title = JText::_('COM_THM_ORGANIZER') . ': ' . JText::_('COM_THM_ORGANIZER_VSM_TITLE');
		JToolBarHelper::title($title, 'mni');
		JToolBarHelper::addNewX('virtual_schedule.add');
		JToolBarHelper::editListX('virtual_schedule.edit');
		JToolBarHelper::deleteListX('Really?', 'virtual_schedule.remove');
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_thm_organizer');
	}
}
