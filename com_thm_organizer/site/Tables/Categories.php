<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Tables;

/**
 * Class instantiates a Table Object associated with the categories table.
 */
class Categories extends Nullable
{
	/**
	 * The resource's name.
	 * VARCHAR(100) NOT NULL
	 *
	 * @var string
	 */
	public $name;

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
		parent::__construct('#__thm_organizer_categories', 'id', $dbo);
	}

	/**
	 * Set the table column names which are allowed to be null
	 *
	 * @return boolean  true
	 */
	public function check()
	{
		if (empty($this->programID))
		{
			$this->programID = null;
		}

		return true;
	}
}
