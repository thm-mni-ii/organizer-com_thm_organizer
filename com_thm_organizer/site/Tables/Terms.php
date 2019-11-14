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
 * Class instantiates a Table Object associated with the terms table.
 */
class Terms extends BaseTable
{
	/**
	 * The resource's code. (String ID)
	 * VARCHAR(10) NOT NULL
	 *
	 * @var string
	 */
	public $code;

	/**
	 * The end date of the resource.
	 * DATE DEFAULT NULL
	 *
	 * @var string
	 */
	public $endDate;

	/**
	 * The resource's full German name.
	 * VARCHAR(255) DEFAULT ''
	 *
	 * @var string
	 */
	public $fullName_de;

	/**
	 * The resource's full English name.
	 * VARCHAR(255) DEFAULT ''
	 *
	 * @var string
	 */
	public $fullName_en;

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
	 * The start date of the resource.
	 * DATE DEFAULT NULL
	 *
	 * @var string
	 */
	public $startDate;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_terms', 'id', $dbo);
	}
}
