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
 * Class instantiates a Table Object associated with the roomtypes table.
 */
class Roomtypes extends BaseTable
{
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
     * Declares the associated table
     *
     * @param \JDatabaseDriver &$dbo A database connector object
     */
    public function __construct(&$dbo = null)
    {
        parent::__construct('#__thm_organizer_roomtypes', 'id', $dbo);
    }
}
