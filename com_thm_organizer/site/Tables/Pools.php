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

use Joomla\CMS\Table\Table;

/**
 * Class instantiates a Table Object associated with the pools table.
 */
class Pools extends Assets
{
	/**
	 * The resource's German abbreviation.
	 * VARCHAR(45) NOT NULL DEFAULT ''
	 *
	 * @var string
	 */
	public $abbreviation_de;

	/**
	 * The resource's English abbreviation.
	 * VARCHAR(45) NOT NULL DEFAULT ''
	 *
	 * @var string
	 */
	public $abbreviation_en;

	/**
	 * The id used by Joomla as a reference to its assets table.
	 * INT(11) NOT NULL
	 *
	 * @var int
	 */
	public $asset_id;

	/**
	 * The resource's German description.
	 * TEXT
	 *
	 * @var string
	 */
	public $description_de;

	/**
	 * The resource's English description.
	 * TEXT
	 *
	 * @var string
	 */
	public $description_en;

	/**
	 * The id of the department entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $departmentID;

	/**
	 * The id of the field entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $fieldID;

	/**
	 * The id of the group entry referenced.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $groupID;

	/**
	 * The id of the entry in the LSF software module.
	 * INT(11) UNSIGNED DEFAULT NULL
	 *
	 * @var int
	 */
	public $lsfID;

	/**
	 * The maximum credit points required to be achieved in subjects of this pool.
	 * INT(3) UNSIGNED DEFAULT 0
	 *
	 * @var int
	 */
	public $maxCrP;

	/**
	 * The minimum credit points required to be achieved in subjects of this pool.
	 * INT(3) UNSIGNED DEFAULT 0
	 *
	 * @var int
	 */
	public $minCrP;

	/**
	 * The resource's German name.
	 * VARCHAR(255) NOT NULL
	 *
	 * @var string
	 */
	public $name_de;

	/**
	 * The resource's English name.
	 * VARCHAR(255) NOT NULL
	 *
	 * @var string
	 */
	public $name_en;

	/**
	 * The resource's German shortened name.
	 * VARCHAR(45) DEFAULT ''
	 *
	 * @var string
	 */
	public $shortName_de;

	/**
	 * The resource's English shortened name.
	 * VARCHAR(45) DEFAULT ''
	 *
	 * @var string
	 */
	public $shortName_en;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_pools', 'id', $dbo);
	}

	/**
	 * Sets the department asset name
	 *
	 * @return string
	 */
	protected function _getAssetName()
	{
		return "com_thm_organizer.pool.$this->id";
	}

	/**
	 * Sets the parent as the component root
	 *
	 * @param   Table    $table  A Table object for the asset parent.
	 * @param   integer  $id     Id to look up
	 *
	 * @return int  the asset id of the component root
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		$asset = Table::getInstance('Asset');
		$asset->loadByName("com_thm_organizer.department.$this->departmentID");

		return $asset->id;
	}

	/**
	 * Set the table column names which are allowed to be null
	 *
	 * @return boolean  true
	 */
	public function check()
	{
		if (empty($this->lsfID))
		{
			$this->lsfID = null;
		}

		return true;
	}
}
