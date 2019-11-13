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
 * Class instantiates a Table Object associated with the instance_persons table.
 */
class InstancePersons extends Nullable
{
	/**
	 * The primary key.
	 * INT(20) UNSIGNED NOT NULL AUTO_INCREMENT
	 *
	 * @var int
	 */
	public $id;

	/**
	 * The id of the instance entry referenced.
	 * INT(20) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $instanceID;

	/**
	 * The id of the person entry referenced.
	 * INT(11) NOT NULL
	 *
	 * @var int
	 */
	public $personID;

	/**
	 * The id of the role entry referenced.
	 * TINYINT(2) UNSIGNED NOT NULL DEFAULT 1
	 *
	 * @var int
	 */
	public $roleID;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_instance_persons', 'id', $dbo);
	}

	/**
	 * Set the table column names which are allowed to be null
	 *
	 * @return boolean  true
	 */
	public function check()
	{
		$this->modified = null;

		return true;
	}
}
