<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;

/**
 * Class provides general functions for retrieving building data.
 */
class Grids extends ResourceHelper
{

	/**
	 * Retrieves the selectable options for the resource.
	 *
	 * @return array the available options
	 */
	public static function getOptions()
	{
		$options = [];
		foreach (self::getResources() as $grid)
		{
			$options[] = HTML::_('select.option', $grid['id'], $grid['name']);
		}

		return $options;
	}

	/**
	 * Retrieves the default grid.
	 *
	 * @param   bool  $onlyID  whether or not only the id will be returned, defaults to true
	 *
	 * @return mixed
	 */
	public static function getDefault($onlyID = true)
	{

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select("*")->from('#__thm_organizer_grids')->where('defaultGrid = 1');

		$dbo->setQuery($query);

		return $onlyID ?
			OrganizerHelper::executeQuery('loadResult', []) : OrganizerHelper::executeQuery('loadAssoc', []);
	}

	/**
	 * Retrieves the grid property for the given grid.
	 *
	 * @param   int  $gridID  the grid id
	 *
	 * @return mixed string the grid json string on success, otherwise null
	 */
	public static function getGrid($gridID)
	{
		return self::getTable()->getProperty('grid', $gridID, '');
	}

	/**
	 * Retrieves the resource items.
	 *
	 * @return array the available resources
	 */
	public static function getResources()
	{
		$tag = Languages::getTag();

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select("*, name_$tag as name, defaultGrid")->from('#__thm_organizer_grids')->order('name');

		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadAssocList', []);
	}

}
