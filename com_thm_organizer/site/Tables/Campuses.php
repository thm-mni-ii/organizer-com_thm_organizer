<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Tables;

/**
 * Class instantiates a Table Object associated with the campuses table.
 */
class Campuses extends Nullable
{
	/**
	 * The physical address of the resource.
	 * VARCHAR(255) NOT NULL
	 *
	 * @var string
	 */
	public $address;

	/**
	 * The city in which the resource is located.
	 * VARCHAR(60) NOT NULL
	 *
	 * @var string
	 */
	public $city;

	/**
	 * The id of the grid entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $gridID;

	/**
	 * A flag displaying if the campus is equatable with a city for internal purposes.
	 * TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
	 *
	 * @var bool
	 */
	public $isCity;

	/**
	 * The GPS coordinates of the resource.
	 * VARCHAR(20) NOT NULL
	 *
	 * @var string
	 */
	public $location;

	/**
	 * The resource's German name.
	 * VARCHAR(60) NOT NULL
	 *
	 * @var string
	 */
	public $name_de;

	/**
	 * The resource's English name.
	 * VARCHAR(60) NOT NULL
	 *
	 * @var string
	 */
	public $name_en;

	/**
	 * The id of the campus entry referenced as parent.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $parentID;

	/**
	 * The ZIP code of the resource.
	 * VARCHAR(60) NOT NULL
	 *
	 * @var string
	 */
	public $zipCode;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_campuses', 'id', $dbo);
	}

	/**
	 * Set the table column names which are allowed to be null
	 *
	 * @return boolean  true
	 */
	public function check()
	{
		if (empty($this->parentID))
		{
			$this->parentID = null;
		}
		if (empty($this->gridID))
		{
			$this->gridID = null;
		}

		return true;
	}
}
