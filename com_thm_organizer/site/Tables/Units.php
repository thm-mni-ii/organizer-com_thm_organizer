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
 * Class instantiates a Table Object associated with the lessons table.
 */
class Units extends Nullable
{
	/**
	 * A supplementary text description.
	 * VARCHAR(200) DEFAULT NULL
	 *
	 * @var string
	 */
	public $comment;

	/**
	 * The id of the course entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $courseID;

	/**
	 * The textual description of the associations last change. Values: changed, <empty>, new, removed.
	 * VARCHAR(10) NOT NULL DEFAULT ''
	 *
	 * @var string
	 */
	public $delta;

	/**
	 * The id of the department entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $departmentID;

	/**
	 * The end date of the resource.
	 * DATE DEFAULT NULL
	 *
	 * @var string
	 */
	public $endDate;

	/**
	 * The id of the grid entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $gridID;

	/**
	 * The timestamp of the time at which the last change to the entry occurred.
	 * TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
	 *
	 * @var int
	 */
	public $modified;

	/**
	 * The id of the run entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $runID;

	/**
	 * The start date of the resource.
	 * DATE DEFAULT NULL
	 *
	 * @var string
	 */
	public $startDate;

	/**
	 * The id of the term entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $termID;

	/**
	 * The resource's identifier in Untis scheduling software.
	 * INT(11) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $untisID;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_units', 'id', $dbo);
	}

	/**
	 * Set the table column names which are allowed to be null
	 *
	 * @return boolean  true
	 */
	public function check()
	{
		if (empty($this->gridID))
		{
			$this->gridID = null;
		}

		$this->modified = null;

		return true;
	}
}
