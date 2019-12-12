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

use Joomla\CMS\Table\Table;
use Organizer\Helpers\Languages;

/**
 * Class instantiates a Table Object associated with the departments table.
 */
class Departments extends Assets
{
	/**
	 * The id used by Joomla as a reference to its assets table.
	 * INT(11) NOT NULL
	 *
	 * @var int
	 */
	public $asset_id;

	/**
	 * The id of the user entry referenced.
	 * INT(11) DEFAULT NULL
	 *
	 * @var int
	 */
	public $contactID;

	/**
	 * The email address to be used for contacting participants
	 * VARCHAR(100) DEFAULT NULL
	 *
	 * @var string
	 */
	public $contactEmail;

	/**
	 * The way way in which department contact is to be carried out. Values: 0 - User Entry, 1 - Email Address
	 * TINYINT(1) UNSIGNED DEFAULT 0
	 *
	 * @var int
	 */
	public $contactType;

	/**
	 * The resource's German name.
	 * VARCHAR(150) NOT NULL
	 *
	 * @var string
	 */
	public $name_de;

	/**
	 * The resource's English name.
	 * VARCHAR(150) NOT NULL
	 *
	 * @var string
	 */
	public $name_en;

	/**
	 * The resource's German shortened name.
	 * VARCHAR(50) NOT NULL
	 *
	 * @var string
	 */
	public $shortName_de;

	/**
	 * The resource's English shortened name.
	 * VARCHAR(50) NOT NULL
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
		parent::__construct('#__thm_organizer_departments', 'id', $dbo);
	}

	/**
	 * Method to return the title to use for the asset table.  In tracking the assets a title is kept for each asset so
	 * that there is some context available in a unified access manager.
	 *
	 * @return string  The string to use as the title in the asset table.
	 */
	protected function _getAssetTitle()
	{
		$shortNameColumn = 'shortName_' . Languages::getTag();

		return $this->$shortNameColumn;
	}

	/**
	 * Sets the department asset name
	 *
	 * @return string
	 */
	protected function _getAssetName()
	{
		$key = $this->_tbl_key;

		return 'com_thm_organizer.department.' . (int) $this->$key;
	}

	/**
	 * Sets the parent as the component root
	 *
	 * @param   Table  $table  the Table object
	 * @param   int    $id     the resource id
	 *
	 * @return int  the asset id of the component root
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		$asset = Table::getInstance('Asset');
		$asset->loadByName('com_thm_organizer');

		return $asset->id;
	}

	/**
	 * Set the table column names which are allowed to be null
	 *
	 * @return boolean  true
	 */
	public function check()
	{
		if (empty($this->contactID))
		{
			$this->contactID = null;
		}

		return true;
	}
}
