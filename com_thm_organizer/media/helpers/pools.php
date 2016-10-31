<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperXMLPools
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once 'department_resources.php';
require_once 'programs.php';

/**
 * Provides validation methods for xml pool (class) objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperPools
{
	/**
	 * Retrieves the table id if existent.
	 *
	 * @param string $gpuntisID the pool name in untis
	 *
	 * @return mixed int id on success, otherwise null
	 */
	public static function getID($gpuntisID)
	{
		$table  = JTable::getInstance('plan_pools', 'thm_organizerTable');
		$data   = array('gpuntisID' => $gpuntisID);
		$exists = $table->load($data);
		if ($exists)
		{
			return $exists ? $table->id : null;
		}

		return null;
	}

	/**
	 * Attempts to get the plan pool's id, creating it if non-existent.
	 *
	 * @param object $pool the pool object
	 *
	 * @return mixed int on success, otherwise null
	 */
	public static function getPlanResourceID($gpuntisID, $pool)
	{
		$poolID = self::getID($gpuntisID);
		if (!empty($poolID))
		{
			return $poolID;
		}

		$data              = array();
		$data['gpuntisID'] = $gpuntisID;

		$programID = THM_OrganizerHelperPrograms::getID($pool->degree);
		if (!empty($programID))
		{
			$data['programID'] = $programID;
		}

		$data['name']      = $pool->restriction;
		$data['full_name'] = $pool->longname;

		$table   = JTable::getInstance('plan_pools', 'thm_organizerTable');
		$success = $table->save($data);

		return $success ? $table->id : null;

	}
}
