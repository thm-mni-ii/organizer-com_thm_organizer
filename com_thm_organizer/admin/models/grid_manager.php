<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelGrid_Manager
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class THM_OrganizerGrid_Manager for component com_thm_organizer
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelGrid_Manager extends THM_OrganizerModelList
{
	protected $defaultOrdering = 'name';

	protected $defaultDirection = 'asc';
	/**
	 * Method to get all grids from the database and set filters for name and default state
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$query    = $this->getDbo()->getQuery(true);

		// `default` in apostrophes because otherwise it's a keyword in sql
		$select = "id, name_$shortTag AS name, grid, `default`, ";
		$parts  = array("'index.php?option=com_thm_organizer&view=grid_edit&id='", "id");
		$select .= $query->concatenate($parts, "") . " AS link";
		$query->select($select);
		$query->from('#__thm_organizer_grids');
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
			if ($this->actions->{'core.edit'} OR $this->actions->{'core.delete'})
			{
				$return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
			}

			if ($this->actions->{'core.edit'})
			{
				$name = JHtml::_('link', $item->link, $item->name);
			}
			else
			{
				$name = $item->name;
			}

			$return[$index]['name'] = $name;

			$grid = json_decode($item->grid);
			if (isset($grid) AND isset($grid->periods))
			{
				$periods     = get_object_vars($grid->periods);
				$firstPeriod = $periods[1];
				$lastPeriod  = end($periods);
				/** 'l' (lowercase L) in date function for full textual day of the week */
				$return[$index]['startDay']  = JText::_(strtoupper(date('l', strtotime("Sunday + {$grid->start_day} days"))));
				$return[$index]['endDay']    = JText::_(strtoupper(date('l', strtotime("Sunday + {$grid->end_day} days"))));
				$return[$index]['startTime'] = THM_OrganizerHelperComponent::formatTime($firstPeriod->start_time);
				$return[$index]['endTime']   = THM_OrganizerHelperComponent::formatTime($lastPeriod->end_time);
			}
			else
			{
				$return[$index]['startDay']  = '';
				$return[$index]['endDay']    = '';
				$return[$index]['startTime'] = '';
				$return[$index]['endTime']   = '';
			}

			$tip                       = JText::_('COM_THM_ORGANIZER_GRID_DEFAULT_DESC');
			$return[$index]['default'] = $this->getToggle($item->id, $item->default, 'grid', $tip);
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
		$headers = array();

		if ($this->actions->{'core.edit'} OR $this->actions->{'core.delete'})
		{
			$headers['checkbox'] = '';
		}

		$headers['name']      = JText::_('COM_THM_ORGANIZER_NAME');
		$headers['startDay']  = JText::_('COM_THM_ORGANIZER_START_DAY');
		$headers['endDay']    = JText::_('COM_THM_ORGANIZER_END_DAY');
		$headers['startTime'] = JText::_('COM_THM_ORGANIZER_START_TIME');
		$headers['endTime']   = JText::_('COM_THM_ORGANIZER_END_TIME');
		$headers['default']   = JText::_('COM_THM_ORGANIZER_DEFAULT');

		return $headers;
	}
}
