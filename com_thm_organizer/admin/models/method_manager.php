<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelMethod_Manager
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
 * Class provides methods to deal with teaching methods
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelMethod_Manager extends THM_OrganizerModelList
{
	protected $defaultOrdering = 'abbreviation';

	protected $defaultDirection = 'asc';

	/**
	 * Method to get all methodss from the database
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$query    = $this->_db->getQuery(true);

		$select = "id, abbreviation_$shortTag AS abbreviation, name_$shortTag AS name, ";
		$parts  = ["'index.php?option=com_thm_organizer&view=method_edit&id='", "id"];
		$select .= $query->concatenate($parts, "") . " AS link";
		$query->select($select);
		$query->from('#__thm_organizer_methods');

		$this->setSearchFilter($query, ['name_de', 'name_en', 'abbreviation_de', 'abbreviation_en']);

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
		$return = [];

		if (empty($items))
		{
			return $return;
		}

		$index = 0;

		foreach ($items as $item)
		{
			$return[$index]                 = [];
			$return[$index]['checkbox']     = JHtml::_('grid.id', $index, $item->id);
			$return[$index]['abbreviation'] = JHtml::_('link', $item->link, $item->abbreviation);
			$return[$index]['name']         = JHtml::_('link', $item->link, $item->name);
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
		$ordering                = $this->state->get('list.ordering', $this->defaultOrdering);
		$direction               = $this->state->get('list.direction', $this->defaultDirection);
		$headers                 = [];
		$headers['checkbox']     = '';
		$headers['abbreviation'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_ABBREVIATION', 'abbreviation', $direction, $ordering);
		$headers['name']         = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'name', $direction, $ordering);

		return $headers;
	}
}
