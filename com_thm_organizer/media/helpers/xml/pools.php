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

require_once 'grids.php';
require_once 'department_resources.php';
require_once 'programs.php';

/**
 * Provides validation methods for xml pool (class) objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperXMLPools
{
	/**
	 * Retrieves the table id if existent.
	 *
	 * @param   string $gpuntisID the grid name in untis
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
	 * @param   object $pool the pool object
	 *
	 * @return mixed int on success, otherwise null
	 */
	private static function getPlanResourceID($pool)
	{
		$poolID = self::getID($pool->gpuntisID);
		if (!empty($poolID))
		{
			return $poolID;
		}

		$programID = THM_OrganizerHelperXMLPrograms::getID($pool->degree);
		if (empty($programID))
		{
			return null;
		}

		$data              = array();
		$data['gpuntisID'] = $pool->gpuntisID;
		$data['programID'] = $programID;
		$data['name']      = $pool->restriction;
		$data['full_name'] = $pool->longname;

		$table   = JTable::getInstance('plan_pools', 'thm_organizerTable');
		$success = $table->save($data);

		return $success ? $table->id : null;

	}

	/**
	 * Sets grid information for the pool node
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   int    $poolID
	 * @param   object &$poolNode      the pool node to be modified
	 *
	 * @return  void modifies the $scheduleModel object
	 */
	private static function setGrid(&$scheduleModel, $poolID, &$poolNode)
	{
		$grid = (string) $poolNode->timegrid;
		if (!empty($grid))
		{
			$scheduleModel->schedule->pools->$poolID->grid   = $grid;
			$scheduleModel->schedule->pools->$poolID->gridID = THM_OrganizerHelperXMLGrids::getID($grid);
		}
		else
		{
			$grid                                          = 'Haupt-Zeitraster';
			$scheduleModel->schedule->pools->$poolID->grid = $grid;
			$gridID                                        = THM_OrganizerHelperXMLGrids::getID($grid);
			if (!empty($gridID))
			{
				$scheduleModel->schedule->pools->$poolID->gridID = $gridID;
			}
		}
	}

	/**
	 * Validates the pools (classes) node
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$xmlObject     the xml object being validated
	 *
	 * @return  void
	 */
	public static function validate(&$scheduleModel, &$xmlObject)
	{
		if (empty($xmlObject->classes))
		{
			$scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_POOLS_MISSING");

			return;
		}

		$scheduleModel->schedule->pools = new stdClass;

		foreach ($xmlObject->classes->children() as $poolNode)
		{
			self::validateIndividual($scheduleModel, $poolNode);
		}

		return;
	}

	/**
	 * Checks whether pool nodes have the expected structure and required information
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$poolNode      the pool node to be validated
	 *
	 * @return  void
	 */
	private static function validateIndividual(&$scheduleModel, &$poolNode)
	{

		$gpuntisID = self::validateUntisID($scheduleModel, $poolNode);
		if (empty($gpuntisID))
		{
			return;
		}

		$poolID                                                = str_replace('CL_', '', $gpuntisID);
		$scheduleModel->schedule->pools->$poolID               = new stdClass;
		$scheduleModel->schedule->pools->$poolID->gpuntisID    = $poolID;
		$scheduleModel->schedule->pools->$poolID->name         = $poolID;
		$scheduleModel->schedule->pools->$poolID->localUntisID = str_replace('CL_', '', trim((string) $poolNode[0]['id']));

		$longName = trim((string) $poolNode->longname);
		if (empty($longName))
		{
			$scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_ERROR_POOL_LONGNAME_MISSING', $poolID);

			return;
		}

		$restriction = trim((string) $poolNode->classlevel);
		if (empty($restriction))
		{
			$scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_ERROR_NODE_NAME', $poolID);

			return;
		}

		$degreeID = str_replace('DP_', '', trim((string) $poolNode->class_department[0]['id']));
		if (empty($degreeID))
		{
			$scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_ERROR_POOL_DEGREE_MISSING', $longName, $poolID);

			return;
		}
		elseif (empty($scheduleModel->schedule->degrees->$degreeID))
		{
			$scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_ERROR_POOL_DEGREE_LACKING', $longName, $poolID, $degreeID);

			return;
		}

		$scheduleModel->schedule->pools->$poolID->longname    = $longName;
		$scheduleModel->schedule->pools->$poolID->restriction = $restriction;
		$scheduleModel->schedule->pools->$poolID->degree      = $degreeID;

		self::setGrid($scheduleModel, $poolID, $poolNode);

		$planResourceID = self::getPlanResourceID($scheduleModel->schedule->pools->$poolID);
		if (!empty($planResourceID))
		{
			$scheduleModel->schedule->pools->$poolID->id = $planResourceID;
			THM_OrganizerHelperXMLDepartment_Resources::setDepartmentResource($planResourceID, 'poolID');
		}
	}

	/**
	 * Validates the pools's gp untis id
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$poolNode      the pool node object
	 *
	 * @return  mixed  string id if valid, otherwise false
	 */
	private static function validateUntisID(&$scheduleModel, &$poolNode)
	{
		$externalName = trim((string) $poolNode->external_name);
		$internalName = trim((string) $poolNode[0]['id']);
		$gpuntisID    = empty($externalName) ? $internalName : $externalName;
		if (empty($gpuntisID))
		{
			if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_POOL_ID_MISSING"), $scheduleModel->scheduleErrors))
			{
				$scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_POOL_ID_MISSING");
			}

			return false;
		}

		return $gpuntisID;
	}
}
