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
 * Class instantiates a Table Object associated with the courses table.
 */
class Courses extends Nullable
{
	/**
	 * The id of the campus entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $campusID;

	/**
	 * The number of days before course begin when registration is closed.
	 * INT(2) UNSIGNED DEFAULT 0
	 *
	 * @var int
	 */
	public $deadline;

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
	 * The fee for participation in the course.
	 * INT(3) UNSIGNED DEFAULT 0
	 *
	 * @var int
	 */
	public $fee;

	/**
	 * A short textual description of which groups should visit the course.
	 * VARCHAR(100)     NOT NULL DEFAULT ''
	 *
	 * @var string
	 */
	public $groups;

	/**
	 * The maximum number of participants the course allows.
	 * INT(4) UNSIGNED DEFAULT 1000
	 *
	 * @var int
	 */
	public $maxParticipants;

	/**
	 * The resource's German name.
	 * VARCHAR(100) NOT NULL
	 *
	 * @var string
	 */
	public $name_de;

	/**
	 * The resource's English name.
	 * VARCHAR(100) NOT NULL
	 *
	 * @var string
	 */
	public $name_en;

	/**
	 * The method of processing used to accept course registrations. Values: NULL - None, 0 - FIFO, 1 - Manual.
	 * INT(1) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $registrationType;

	/**
	 * The id of the term entry referenced.
	 * INT(11) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $termID;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_courses', 'id', $dbo);
	}

	/**
	 * Set the table column names which are allowed to be null
	 *
	 * @return boolean  true
	 */
	public function check()
	{
		if (empty($this->campusID))
		{
			$this->campusID = null;
		}

		return true;
	}
}
