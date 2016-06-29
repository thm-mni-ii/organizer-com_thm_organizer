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
	 * Checks whether pool nodes have the expected structure and required
	 * information
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
		$scheduleModel->schedule->pools->$poolID->gpuntisID    = $gpuntisID;
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

		$scheduleModel->schedule->pools->$poolID->longname    = $poolID;
		$scheduleModel->schedule->pools->$poolID->restriction = $restriction;
		$scheduleModel->schedule->pools->$poolID->degree      = $degreeID;

		$grid                                          = (string) $poolNode->timegrid;
		$scheduleModel->schedule->pools->$poolID->grid = empty($grid) ? 'Haupt-Zeitraster' : $grid;
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
