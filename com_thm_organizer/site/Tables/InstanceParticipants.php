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
 * Class instantiates a Table Object associated with the instance_participants table.
 */
class InstanceParticipants extends BaseTable
{
	/**
	 * The primary key.
	 * INT(20) UNSIGNED NOT NULL AUTO_INCREMENT
	 *
	 * @var int
	 */
	public $id;

	/**
	 * The id of the instance entry referenced.
	 * INT(20) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $instanceID;

	/**
	 * The id of the participant entry referenced.
	 * INT(11) NOT NULL
	 *
	 * @var int
	 */
	public $participantID;


	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_instance_participants', 'id', $dbo);
	}
}
