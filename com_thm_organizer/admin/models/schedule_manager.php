<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSchedule_Manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class defining functions to be used for lesson resources
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSchedule_Manager extends THM_OrganizerModelList
{
	protected $defaultOrdering = 'created';

	protected $defaultDirection = 'DESC';

	/**
	 * sets variables and configuration data
	 *
	 * @param array $config the configuration parameters
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array('departmentname', 'semestername', 'creationDate', 'active');
		}

		parent::__construct($config);
	}

	/**
	 * generates the query to be used to fill the output list
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$allowedDepartments = THM_OrganizerHelperComponent::getAccessibleDepartments('schedule');
		$shortTag           = THM_OrganizerHelperLanguage::getShortTag();
		$dbo                = $this->getDbo();
		$query              = $dbo->getQuery(true);

		$select = "s.id, s.active, s.creationDate, s.creationTime, ";
		$select .= "d.id AS departmentID, d.short_name_$shortTag AS departmentName, ";
		$select .= "pp.id as planningPeriodID, pp.name AS planningPeriodName, ";
		$createdParts = array("s.creationDate", "s.creationTime");
		$select .= $query->concatenate($createdParts, " ") . " AS created ";

		$query->select($select);
		$query->from("#__thm_organizer_schedules AS s");
		$query->innerJoin("#__thm_organizer_departments AS d ON s.departmentID = d.id");
		$query->innerJoin("#__thm_organizer_planning_periods AS pp ON s.planningPeriodID = pp.id");
		$query->where("d.id IN ('" . implode("', '", $allowedDepartments) . "')");

		$this->setValueFilters($query, array('departmentID', 'planningPeriodID', 'active'));

		$this->setOrdering($query);

		return $query;
	}

	/**
	 * Function to feed the data in the table body correctly to the list view
	 *
	 * @return array consisting of items in the body
	 */
	public function getItems()
	{
		$items  = parent::getItems();
		$return = array();

		if (empty($items))
		{
			return $return;
		}

		$index = 0;

		foreach ($items as $item)
		{
			$return[$index] = array();

			$return[$index]['checkbox']         = JHtml::_('grid.id', $index, $item->id);
			$return[$index]['departmentID']     = $item->departmentName;
			$return[$index]['planningPeriodID'] = $item->planningPeriodName;

			$return[$index]['active']
				= $this->getToggle($item->id, $item->active, 'schedule', JText::_('COM_THM_ORGANIZER_TOGGLE_ACTIVE'));

			$created = THM_OrganizerHelperComponent::formatDate($item->creationDate);
			$created .= ' / ' . THM_OrganizerHelperComponent::formatTime($item->creationTime);
			$return[$index]['created'] = $created;

			$index++;
		}

		return $return;
	}

	/**
	 * Function to get table headers
	 *
	 * @return array including headers
	 */
	public function getHeaders()
	{
		$ordering            = $this->state->get('list.ordering', $this->defaultOrdering);
		$direction           = $this->state->get('list.direction', $this->defaultDirection);
		$headers             = array();
		$headers['checkbox'] = '';

		$headers['departmentID']
			= JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DEPARTMENT', 'departmentname', $direction, $ordering);
		$headers['planningPeriodID']
			= JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_PLANNING_PERIOD', 'semestername', $direction, $ordering);
		$headers['active']
			= JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_STATE', 'active', $direction, $ordering);
		$headers['created']
			= JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_CREATION_DATE', 'created', $direction, $ordering);

		return $headers;
	}
}
