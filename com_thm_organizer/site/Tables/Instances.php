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
 * Class instantiates a Table Object associated with the lesson_courses table.
 */
class Instances extends Nullable
{
	/**
	 * The id of the block entry referenced.
	 * INT(11) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $blockID;

	/**
	 * The textual description of the associations last change. Values: changed, <empty>, new, removed.
	 * VARCHAR(10) NOT NULL DEFAULT ''
	 *
	 * @var string
	 */
	public $delta;

	/**
	 * The id of the event entry referenced.
	 * INT(11) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $eventID;

	/**
	 * The primary key.
	 * INT(20) UNSIGNED NOT NULL AUTO_INCREMENT
	 *
	 * @var int
	 */
	public $id;

	/**
	 * The id of the method entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $methodID;

	/**
	 * The timestamp of the time at which the last change to the entry occurred.
	 * TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
	 *
	 * @var int
	 */
	public $modified;

	/**
	 * The id of the unit entry referenced.
	 * INT(11) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $unitID;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_instances', 'id', $dbo);
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
