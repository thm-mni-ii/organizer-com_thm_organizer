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
 * Class instantiates a Table Object associated with the events table.
 */
class Events extends BaseTable
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
	 * The id of the department entry referenced.
	 * INT(11) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $departmentID;

	/**
	 * The fee for participation in the course.
	 * INT(3) UNSIGNED DEFAULT 0
	 *
	 * @var int
	 */
	public $fee;

	/**
	 * The id of the field entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $fieldID;

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
	 * Whether or not the event is a preparatory event.
	 * TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
	 *
	 * @var bool
	 */
	public $preparatory;

	/**
	 * The method of processing used to accept course registrations. Values: NULL - None, 0 - FIFO, 1 - Manual.
	 * INT(1) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $registrationType;

	/**
	 * The resource's alphanumeric identifier in degree program documentation.
	 * VARCHAR(45) NOT NULL DEFAULT ''
	 *
	 * @var string
	 */
	public $subjectNo;

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
		parent::__construct('#__thm_organizer_events', 'id', $dbo);
	}
}
