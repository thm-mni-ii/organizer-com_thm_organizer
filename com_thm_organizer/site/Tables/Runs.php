<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Tables;

defined('_JEXEC') or die;

/**
 * Class instantiates a Table Object associated with the runs table.
 */
class Runs extends BaseTable
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
        parent::__construct('#__thm_organizer_runs', 'id', $dbo);
    }
}
