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
 * Class instantiates a Table Object associated with the roomtypes table.
 */
class Roomtypes extends BaseTable
{
	/**
	 * The resource's German description.
	 * TEXT
	 *
	 * @var string
	 */
	public $description_de;

	/**
	 * The resource's English description.
	 * TEXT
	 *
	 * @var string
	 */
	public $description_en;

	/**
	 * The maximum occupancy for rooms of this type.
	 * INT(4) UNSIGNED DEFAULT NULL
	 *
	 * @var string
	 */
	public $maxCapacity;

	/**
	 * The minimum occupancy for rooms of this type.
	 * INT(4) UNSIGNED DEFAULT NULL
	 *
	 * @var string
	 */
	public $minCapacity;

	/**
	 * The resource's German name.
	 * VARCHAR(50) NOT NULL
	 *
	 * @var string
	 */
	public $name_de;

	/**
	 * The resource's English name.
	 * VARCHAR(50) NOT NULL
	 *
	 * @var string
	 */
	public $name_en;

	/**
	 * A flag which displays whether rooms of this type should be displayed publicly.
	 * TINYINT(1) UNSIGNED NOT NULL DEFAULT 1
	 *
	 * @var bool
	 */
	public $public;

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
		parent::__construct('#__thm_organizer_roomtypes', 'id', $dbo);
	}
}
