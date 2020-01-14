<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Tables;

/**
 * Class instantiates a Table Object associated with the monitors table.
 */
class Monitors extends BaseTable
{
	/**
	 * The file name of the content to be displayed.
	 * VARCHAR(256) DEFAULT ''
	 *
	 * @var string
	 */
	public $content;

	/**
	 * The refresh interval (in seconds) for content display.
	 * INT(3) UNSIGNED NOT NULL DEFAULT 60
	 *
	 * @var int
	 */
	public $contentRefresh;

	/**
	 * A flag displaying for component or monitor specific settings. Values: 1 - Daily Plan, 2 - Interval, 3 - Content
	 * INT(1) UNSIGNED NOT NULL DEFAULT 1
	 *
	 * @var int
	 */
	public $display;

	/**
	 * The interval (in minutes) between display type switches.
	 * INT(1) UNSIGNED NOT NULL DEFAULT 1
	 *
	 * @var int
	 */
	public $interval;

	/**
	 * The ip address associated with the monitor.
	 * VARCHAR(15) NOT NULL
	 *
	 * @var string
	 */
	public $ip;

	/**
	 * The id of the room entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $roomID;

	/**
	 * The refresh interval (in seconds) for schedule display.
	 * INT(3) UNSIGNED NOT NULL DEFAULT 60
	 *
	 * @var int
	 */
	public $scheduleRefresh;

	/**
	 * The monitor settings source. Values: 0 - Monitor Specific, 1 - Component
	 * TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
	 *
	 * @var int
	 */
	public $useDefaults;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_monitors', 'id', $dbo);
	}
}
