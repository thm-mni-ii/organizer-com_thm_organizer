<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

abstract class ResourceHelper
{
	/**
	 * Attempts to retrieve the name of the resource.
	 *
	 * @param   int  $resourceID  the id of the resource
	 *
	 * @return string
	 */
	public static function getAbbreviation($resourceID)
	{
		return self::getNameAttribute('abbreviation', $resourceID);
	}

	/**
	 * Attempts to retrieve the name of the resource.
	 *
	 * @param   string  $columnName  the substatiative part of the column name to search for
	 * @param   int     $resourceID  the id of the resource
	 *
	 * @return string
	 */
	public static function getNameAttribute($columnName, $resourceID)
	{
		$table  = OrganizerHelper::getTable(OrganizerHelper::getClass(get_called_class()));
		$exists = $table->load($resourceID);
		if (empty($exists))
		{
			return '';
		}

		$tableFields = $table->getFields();
		if (array_key_exists($columnName, $tableFields))
		{
			return $table->name;
		}

		$localizedName = "{$columnName}_" . Languages::getTag();
		if (array_key_exists($localizedName, $tableFields))
		{
			return $table->$localizedName;
		}

		return '';
	}

	/**
	 * Attempts to retrieve the name of the resource.
	 *
	 * @param   int  $resourceID  the id of the resource
	 *
	 * @return string
	 */
	public static function getName($resourceID)
	{
		return self::getNameAttribute('name', $resourceID);
	}

	/**
	 * Attempts to retrieve the name of the resource.
	 *
	 * @param   int  $resourceID  the id of the resource
	 *
	 * @return string
	 */
	public static function getShortName($resourceID)
	{
		return self::getNameAttribute('shortName', $resourceID);
	}
}
