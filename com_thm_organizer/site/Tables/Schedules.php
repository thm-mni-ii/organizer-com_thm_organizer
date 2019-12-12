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

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class instantiates a Table Object associated with the schedules table.
 */
class Schedules extends BaseTable
{
	/**
	 * A flag which displays whether the resource is currently active.
	 * TINYINT(1) UNSIGNED NOT NULL DEFAULT 1
	 *
	 * @var bool
	 */
	public $active;

	/**
	 * The id used by Joomla as a reference to its assets table.
	 * INT(11) NOT NULL
	 *
	 * @var int
	 */
	public $asset_id;

	/**
	 * The date of the schedule's creation.
	 * DATE DEFAULT NULL
	 *
	 * @var string
	 */
	public $creationDate;

	/**
	 * The time of the schedule's creation.
	 * TIME DEFAULT NULL
	 *
	 * @var string
	 */
	public $creationTime;

	/**
	 * The id of the department entry referenced.
	 * INT(11) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $departmentID;

	/**
	 * A collection of instance objects modeled by a JSON string.
	 * MEDIUMTEXT NOT NULL
	 *
	 * @var string
	 */
	public $schedule;

	/**
	 * The id of the term entry referenced.
	 * INT(11) UNSIGNED NOT NULL
	 *
	 * @var int
	 */
	public $termID;

	/**
	 * The id of the user entry referenced.
	 * INT(11) DEFAULT NULL
	 *
	 * @var int
	 */
	public $userID;

	/**
	 * Declares the associated table
	 *
	 * @param   \JDatabaseDriver &$dbo  A database connector object
	 */
	public function __construct(&$dbo = null)
	{
		parent::__construct('#__thm_organizer_schedules', 'id', $dbo);
	}

	/**
	 * Method to return the title to use for the asset table.  In tracking the assets a title is kept for each asset so
	 * that there is some context available in a unified access manager.
	 *
	 * @return string  The string to use as the title in the asset table.
	 */
	protected function _getAssetTitle()
	{
		$dbo       = Factory::getDbo();
		$deptQuery = $dbo->getQuery(true);
		$deptQuery->select('shortName_en')
			->from('#__thm_organizer_departments')
			->where("id = '{$this->departmentID}'");

		$dbo->setQuery($deptQuery);
		$deptName = (string) OrganizerHelper::executeQuery('loadResult');

		$tag = Languages::getTag();
		$termQuery = $dbo->getQuery(true);
		$termQuery->select("name_$tag")
			->from('#__thm_organizer_terms')
			->where("id = '{$this->termID}'");

		$dbo->setQuery($termQuery);
		$termName = (string) OrganizerHelper::executeQuery('loadResult');

		return "Schedule: $deptName - $termName";
	}

	/**
	 * Sets the department asset name
	 *
	 * @return string
	 */
	protected function _getAssetName()
	{
		return "com_thm_organizer.schedule.$this->id";
	}

	/**
	 * Sets the parent as the component root.
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
	 * Overridden bind function
	 *
	 * @param   array  $array   named array
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return mixed  Null if operation was satisfactory, otherwise returns an error string
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['rules']) && is_array($array['rules']))
		{
			OrganizerHelper::cleanRules($array['rules']);
			$rules = new AccessRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}
}
