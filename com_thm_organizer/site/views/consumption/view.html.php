<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewConsumption
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class loads consumption information into the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewConsumption extends JViewLegacy
{
	public $params = null;

	public $model = null;

	public $scheduleSelectBox = '';

	public $typeSelectBox = '';

	public $startCalendar = '';

	public $endCalendar = '';

	public $hoursSelectBox = '';

	public $table = '';

	/**
	 * Method to get display
	 *
	 * @param Object $tpl template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// Sets js and css
		$this->modifyDocument();

		$this->params = JFactory::getApplication()->getParams();

		$this->model = $this->getModel();
		$this->makeScheduleSelectBox();
		$this->makeTypeSelectBox();
		$this->makeHoursSelectBox();

		if (!empty($this->model->schedule))
		{
			if ($this->model->type == ROOM)
			{
				$this->makeResourceSelectBox('rooms');
				$this->makeResourceSelectBox('roomTypes');
			}
			if ($this->model->type == TEACHER)
			{
				$this->makeResourceSelectBox('teachers');
				$this->makeResourceSelectBox('fields');
			}
			$this->table = $this->getModel()->getConsumptionTable();
		}
		parent::display($tpl);
	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return  void
	 */
	private function modifyDocument()
	{
		JHtml::_('jquery.ui');
		JHtml::_('behavior.calendar');
		JHtml::_('formbehavior.chosen', 'select');
		JFactory::getDocument()->setCharset("utf-8");
		JFactory::getDocument()->addScript(JUri::root() . '/media/com_thm_organizer/js/consumption.js');
		JFactory::getDocument()->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/consumption.css');
	}

	/**
	 * Creates a select box for the active schedules
	 *
	 * @return  void
	 */
	private function makeScheduleSelectBox()
	{
		$scheduleID = $this->model->scheduleID;
		$schedules  = $this->getModel()->getActiveSchedules();

		$options   = array();
		$options[] = JHtml::_('select.option', 0, JText::_("COM_THM_ORGANIZER_FILTER_SCHEDULE"));
		foreach ($schedules as $schedule)
		{
			$options[] = JHtml::_('select.option', $schedule['id'], $schedule['name']);
		}

		$attribs             = array();
		$attribs['onChange'] = "jQuery('#reset').val('1');this.form.submit();";

		$this->scheduleSelectBox = JHtml::_('select.genericlist', $options, 'scheduleID', $attribs, 'value', 'text', $scheduleID);
	}

	/**
	 * Creates a select box for the resource types
	 *
	 * @return  void
	 */
	private function makeTypeSelectBox()
	{
		$options   = array();
		$options[] = JHtml::_('select.option', ROOM, JText::_("COM_THM_ORGANIZER_FILTER_ROOM_USAGE"));
		$options[] = JHtml::_('select.option', TEACHER, JText::_("COM_THM_ORGANIZER_FILTER_TEACHER_USAGE"));

		$attribs      = array('onChange' => "jQuery('#reset').val('1');this.form.submit();");
		$selectedType = $this->model->type;

		$this->typeSelectBox = JHtml::_('select.genericlist', $options, 'type', $attribs, 'value', 'text', $selectedType);
	}

	/**
	 * Creates a select box for the resource types
	 *
	 * @return  void
	 */
	private function makeHoursSelectBox()
	{
		$options   = array();
		$options[] = JHtml::_('select.option', REAL, JText::_("COM_THM_ORGANIZER_FILTER_HOURS_REAL"));
		$options[] = JHtml::_('select.option', SCHOOL, JText::_("COM_THM_ORGANIZER_FILTER_HOURS_SCHOOL"));

		$attribs       = array('onChange' => "this.form.submit();");
		$selectedHours = $this->model->hours;

		$this->hoursSelectBox = JHtml::_('select.genericlist', $options, 'hours', $attribs, 'value', 'text', $selectedHours);
	}

	/**
	 * Creates a select box for resources
	 *
	 * @param string $typeName the resource type
	 *
	 * @return  void
	 */
	private function makeResourceSelectBox($typeName)
	{
		$boxName = $typeName . 'SelectBox';

		$resources = $this->model->names[$typeName];
		asort($resources);
		$selectedResources = $this->model->selected[$typeName];
		$options           = array();
		$options[]         = JHtml::_('select.option', '*', JText::_('JALL'));
		foreach ($resources as $resourceID => $resourceName)
		{
			$options[] = JHtml::_('select.option', $resourceID, $resourceName);
		}

		$typeName = $typeName . '[]';
		$attribs  = array('multiple' => 'multiple', 'size' => '10');

		$this->$boxName = JHtml::_('select.genericlist', $options, $typeName, $attribs, 'value', 'text', $selectedResources);
	}
}
