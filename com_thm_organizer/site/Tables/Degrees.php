<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Tables;

/**
 * Class instantiates a Table Object associated with the degrees table.
 */
class Degrees extends BaseTable
{
	/**
	 * The resource's abbreviation.
	 * VARCHAR(45) NOT NULL DEFAULT ''
	 *
	 * @var string
	 */
	public $abbreviation;

	/**
	 * The resource's code. (String ID)
	 * VARCHAR(10) DEFAULT ''
	 *
	 * @var string
	 */
	public $code;

	/**
	 * The resource's name.
	 * VARCHAR(255) NOT NULL
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_degrees', 'id', $dbo);
	}
}
