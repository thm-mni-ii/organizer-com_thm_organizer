<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPool_Manager
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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Class provides functions for displaying a list of pools
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPool_Manager extends THM_OrganizerModelList
{
	protected $defaultOrdering = 'name';

	protected $defaultDirection = 'asc';

	/**
	 * constructor
	 *
	 * @param array $config configurations parameter
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array('name', 'field');
		}

		parent::__construct($config);
	}

	/**
	 * Method to select the tree of a given major
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$query = $this->_db->getQuery(true);

		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$select   = "DISTINCT p.id, p.name_$shortTag AS name, field_$shortTag AS field, color, ";
		$parts    = array("'index.php?option=com_thm_organizer&view=pool_edit&id='", "p.id");
		$select .= $query->concatenate($parts, "") . "AS link ";
		$query->select($select);

		$query->from('#__thm_organizer_pools AS p');
		$query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
		$query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

		$searchColumns = array('p.name_de', 'short_name_de', 'abbreviation_de', 'description_de',
		                       'p.name_en', 'short_name_en', 'abbreviation_en', 'description_en'
		);
		$this->setSearchFilter($query, $searchColumns);
		$this->setLocalizedFilters($query, array('p.name'));
		$this->setValueFilters($query, array('fieldID'));

		$programID = $this->state->get('filter.programID', '');
		THM_OrganizerHelperMapping::setResourceIDFilter($query, $programID, 'program', 'pool');

		$this->setOrdering($query);

		return $query;
	}

	/**
	 * Method to overwrite the getItems method in order to set the program name
	 *
	 * @return  array  an array of objects fulfilling the request criteria
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
			$return[$index]              = array();
			$return[$index]['checkbox']  = JHtml::_('grid.id', $index, $item->id);
			$return[$index]['name']      = JHtml::_('link', $item->link, $item->name);
			$programName                 = THM_OrganizerHelperMapping::getProgramName('pool', $item->id);
			$return[$index]['programID'] = JHtml::_('link', $item->link, $programName);
			if (!empty($item->field))
			{
				if (!empty($item->color))
				{
					$return[$index]['fieldID'] = THM_OrganizerHelperComponent::getColorField($item->field, $item->color);
				}
				else
				{
					$return[$index]['fieldID'] = $item->field;
				}
			}
			else
			{
				$return[$index]['fieldID'] = '';
			}

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

		$headers              = array();
		$headers['checkbox']  = '';
		$headers['name']      = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'name', $direction, $ordering);
		$headers['programID'] = JText::_('COM_THM_ORGANIZER_PROGRAM');
		$headers['fieldID']   = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_FIELD', 'field', $direction, $ordering);

		return $headers;
	}

	/**
	 * Method to get the total number of items for the data set.
	 *
	 * @param  string $idColumn not used
	 *
	 * @return  integer  The total number of items available in the data set.
	 */
	public function getTotal($idColumn = null)
	{
		$query = $this->getListQuery();
		$query->clear('select');
		$query->clear('order');
		$query->select('COUNT(DISTINCT p.id)');
		$dbo = JFactory::getDbo();
		$dbo->setQuery((string) $query);

		try
		{
			$result = $dbo->loadResult();

			return $result;
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($exc->getMessage());

			return null;
		}
	}

	/**
	 * Overrides the LoadFormData function of JModelList in order to add multiple field paths
	 *
	 * @return  mixed  The data for the form.
	 */
	public function loadFormData()
	{
		JForm::addFieldPath(JPATH_ROOT . '/media/com_thm_organizer/fields');

		return parent::loadFormData();
	}

	/**
	 * Overwrites the JModelList populateState function
	 *
	 * @param string $ordering  the column by which the table is should be ordered
	 * @param string $direction the direction in which this column should be ordered
	 *
	 * @return  void  sets object state variables
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		$filter = JFactory::getApplication()->getUserStateFromRequest($this->context . '.filter', 'filter', array(), 'array');
		if (!empty($filter['name']))
		{
			$this->setState('filter.p.name', $filter['name']);
		}
		else
		{
			$pname = 'filter.p.name';
			unset($this->state->$pname);
		}
	}
}
