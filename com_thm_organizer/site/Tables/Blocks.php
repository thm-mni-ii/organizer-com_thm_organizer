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
 * Class instantiates a Table Object associated with the calendar table.
 */
class Blocks extends BaseTable
{
	/**
	 * The date of the block.
	 * DATE DEFAULT NULL
	 *
	 * @var string
	 */
	public $date;

	/**
	 * The end time of the block.
	 * TIME DEFAULT NULL
	 *
	 * @var string
	 */
	public $endTime;

	/**
	 * The start time of the block.
	 * TIME DEFAULT NULL
	 *
	 * @var string
	 */
	public $startTime;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_blocks', 'id', $dbo);
	}
}
