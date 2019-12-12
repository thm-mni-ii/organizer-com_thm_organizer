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
 * Class instantiates a Table Object associated with the subject_persons table.
 */
class SubjectPersons extends BaseTable
{
	/**
	 * The id of the person entry referenced.
	 * INT(11) NOT NULL
	 *
	 * @var int
	 */
	public $personID;

	/**
	 * The person's responsibility for the subject. Values: 1 - Coordinates, 2 - Teaches.
	 * TINYINT(1) UNSIGNED NOT NULL DEFAULT 1
	 *
	 * @var int
	 */
	public $role;

	/**
	 * The id of the subject entry referenced.
	 * INT(11) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $subjectID;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_subject_persons', 'id', $dbo);
	}
}
