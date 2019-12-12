<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Tables;

/**
 * Class instantiates a Table Object associated with the rooms table.
 */
class Rooms extends Nullable
{
	/**
	 * A flag which displays whether the resource is currently active.
	 * TINYINT(1) UNSIGNED NOT NULL DEFAULT 1
	 *
	 * @var bool
	 */
	public $active;

	/**
	 * The id of the building entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $buildingID;

	/**
	 * The rooms maximum occupancy for participants.
	 * INT(4) UNSIGNED DEFAULT NULL
	 *
	 * @var string
	 */
	public $capacity;

	/**
	 * The resource's name.
	 * VARCHAR(10) NOT NULL
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The id of the roomtype entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $roomtypeID;

	/**
	 * The resource's identifier in Untis scheduling software.
	 * VARCHAR(60) DEFAULT NULL
	 *
	 * @var string
	 */
	public $untisID;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_rooms', 'id', $dbo);
	}

	/**
	 * Set the table column names which are allowed to be null
	 *
	 * @return boolean  true
	 */
	public function check()
	{
		$nullColumns = ['roomtypeID', 'buildingID'];
		foreach ($nullColumns as $nullColumn)
		{
			if (!strlen($this->$nullColumn))
			{
				$this->$nullColumn = null;
			}
		}

		return true;
	}
}
