<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Tables;

use Organizer\Helpers\OrganizerHelper;

/**
 * Class instantiates a Table Object associated with the holidays table.
 */
class Holidays extends BaseTable
{
	/**
	 * The end date of the resource.
	 * DATE DEFAULT NULL
	 *
	 * @var string
	 */
	public $endDate;

	/**
	 * The resource's German name.
	 * VARCHAR(50) NOT NULL
	 *
	 * @var string
	 */
	public $name_de;

	/**
	 * The resource's English name.
	 * VARCHAR(50) NOT NULL
	 *
	 * @var string
	 */
	public $name_en;

	/**
	 * The start date of the resource.
	 * DATE DEFAULT NULL
	 *
	 * @var string
	 */
	public $startDate;

	/**
	 * The impact of the holiday on the planning process. Values: 1 - Automatic, 2 - Manual, 3 - Unplannable
	 * TINYINT(1) UNSIGNED NOT NULL DEFAULT 3
	 *
	 * @var int
	 */
	public $type;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */

	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_holidays', 'id', $dbo);
	}

	/**
	 * Checks the start date and end date
	 *
	 * @return boolean true on success, otherwise false
	 */

	public function check()
	{
		if ($this->endDate < $this->startDate)
		{
			OrganizerHelper::message('ORGANIZER_DATE_CHECK', 'error');

			return false;
		}

		return true;
	}
}