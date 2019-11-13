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
 * Class instantiates a Table Object associated with the course_participants table.
 */
class CourseParticipants extends BaseTable
{
	/**
	 * The primary key.
	 * INT(20) UNSIGNED NOT NULL AUTO_INCREMENT
	 *
	 * @var int
	 */
	public $id;

    /**
     * Declares the associated table
     *
     * @param \JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo = null)
    {
        parent::__construct('#__thm_organizer_course_participants', 'id', $dbo);
    }
}
