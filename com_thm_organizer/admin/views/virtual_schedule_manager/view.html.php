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

		$document = & JFactory::getDocument();
		$document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");

		$this->addToolbar();

		$mainframe = JFactory::getApplication("administrator");
		$option = $mainframe->scope;
		$view = JRequest::getString('view');
		$dbo = JFactory::getDBO();

		$filter_order = $mainframe->getUserStateFromRequest(
				"$option.$view.filter_order", 'filter_order',
				'#__thm_organizer_virtual_schedules.semesterID, #__thm_organizer_virtual_schedules.vid', ''
		);
		$filter_order_Dir = $mainframe->getUserStateFromRequest("$option.$view.filter_order_Dir", 'filter_order_Dir',	'', '');
		$filter_type = $mainframe->getUserStateFromRequest("$option.$view.filter_type", 'filter_type', 0, 'string');
		$filter_logged = $mainframe->getUserStateFromRequest("$option.$view.filter_logged", 'filter_logged', 0, 'int');
		$filter = $mainframe->getUserStateFromRequest($option . $view . '.filter', 'filter', '', 'int');
		$search = $mainframe->getUserStateFromRequest($option . $view . '.search', 'search', '', 'string');
		$search = $dbo->getEscaped(trim(JString::strtolower($search)));

		// Table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		$model =& $this->getModel();

		// Get data from the model
		$items = & $this->get('Data');
		$newitem = array();

		$elements = $model->getElements();

		foreach ($elements as $k => $v)
		{
			if (!isset($newitem[$v->vid]))
			{
				$newitem[$v->vid] = $v;
			}
			else
			{
				$newitem[$v->vid]->eid = $newitem[$v->vid]->eid . ";" . $v->eid;
			}
		}
		$elements = array_values($newitem);

		foreach ($items as $ik => $iv)
		{
			foreach ($elements as $ek => $ev)
			{
				if ($iv->id == $ev->vid)
				{
					if (isset($iv->eid))
					{
						$iv->eid = "";
					}
					$iv->eid = $ev->eid;
				}
			}
		}

		$pagination = & $this->get('Pagination');

		// Search filter
		$lists['search'] = $search;

		// Assign data to template
		$this->assignRef('lists', $lists);

		$this->assignRef('items', $items);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('lists', $lists);
		if (isset($roleFilters_req))
		{
			$this->assignRef('rolesFilters', $roleFilters_req);
		}
		if (isset($groupFilters_req))
		{
			$this->assignRef('groupFilters', $groupFilters_req);
		}

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
		/**
		 * ToDo: Virtuelle Stundenpl�ne sollen kopiert werden k�nnen.
		 */
		// JToolBarHelper::customX( 'copy', 'copy.png', 'copy_f2.png', JText::_('Copy') );
		JToolBarHelper::deleteListX('Really?', 'virtual_schedule.remove');
		if (thm_organizerHelper::isAdmin("virtual_schedule_manager"))
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_thm_organizer');
		}
	}
}
