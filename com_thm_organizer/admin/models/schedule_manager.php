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
	 * @param   array $config the configuration parameters
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array('departmentname', 'semestername', 'creationdate', 'active');
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
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$dbo      = $this->getDbo();
		$query    = $dbo->getQuery(true);

		$select = "s.id, d.short_name_$shortTag AS departmentname, semestername, active, ";
		$select .= "term_enddate, creationdate, creationtime, ";
		$createdParts = array("creationdate", "creationtime");
		$select .= $query->concatenate($createdParts, " ") . " AS created, ";
		$sNameParts = array("semestername", "SUBSTRING(term_enddate, 3, 2)");
		$select .= $query->concatenate($sNameParts, " ") . " AS semestername ";
		$query->select($select);
		$query->from("#__thm_organizer_schedules AS s");
		$query->innerJoin("#__thm_organizer_departments AS d ON s.departmentID = d.id");

		$this->setSearchFilter($query, array('departmentname', 'semestername', 'd.name'));
		$this->setValueFilters($query, array('departmentID', 'semestername', 'active'));
		$this->setCreatedFilter($query);

		$this->setOrdering($query);

		return $query;
	}


	/**
	 * Provides a default method for setting filters for non-unique values
	 *
	 * @param   object &$query the query object
	 *
	 * @return  void
	 */
	private function setCreatedFilter(&$query)
	{
		$value = $this->state->get("filter.created", '');

		/**
		 * Special value reserved for empty filtering. Since an empty is dependent upon the column default, we must
		 * check against multiple 'empty' values. Here we check against empty string and null. Should this need to
		 * be extended we could maybe add a parameter for it later.
		 */
		if (empty($value))
		{
			return;
		}

		$query->where("creationdate = '$value'");

		return;
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
			$canEdit        = THM_OrganizerHelperComponent::allowResourceManage('schedule', $item->id);

			if ($canEdit)
			{
				$return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
			}
			else
			{
				$return[$index]['checkbox'] = '';
			}

			$return[$index]['departmentID'] = $item->departmentname;
			$return[$index]['semestername'] = $item->semestername;

			if ($canEdit)
			{
				$return[$index]['active']
					= $this->getToggle($item->id, $item->active, 'schedule', JText::_('COM_THM_ORGANIZER_TOGGLE_ACTIVE'));
			}
			else
			{
				$return[$index]['active']
					= $this->getToggle($item->id, $item->active, 'schedule', JText::_('COM_THM_ORGANIZER_TOGGLE_ACTIVE'), null);
			}

			$created = THM_OrganizerHelperComponent::formatDate($item->creationdate);
			$created .= ' / ' . THM_OrganizerHelperComponent::formatTime($item->creationtime);
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
		$ordering  = $this->state->get('list.ordering', $this->defaultOrdering);
		$direction = $this->state->get('list.direction', $this->defaultDirection);

		$headers                 = array();
		$headers['checkbox']     = '';
		$headers['departmentID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DEPARTMENT', 'departmentname', $direction, $ordering);
		$headers['semestername'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_PLANNING_PERIOD', 'semestername', $direction, $ordering);
		$headers['active']       = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_STATE', 'active', $direction, $ordering);
		$headers['created']      = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_CREATION_DATE', 'created', $direction, $ordering);

		return $headers;
	}
}
