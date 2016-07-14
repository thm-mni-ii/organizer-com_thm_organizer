<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperXMLPrograms
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once 'schedule_resource.php';

/**
 * Provides validation methods for xml degree (department) objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperXMLPrograms
{
	/**
	 * Attempts to get the plan program's id, creating it if non-existent.
	 *
	 * @param   object $program the program object
	 *
	 * @return mixed int on success, otherwise null
	 */
	private static function getPlanResourceID($program)
	{
		$planResourceTable = JTable::getInstance('plan_programs', 'thm_organizerTable');
		$data              = array('gpuntisID' => $program->gpuntisID);
		$exists            = $planResourceTable->load($data);
		if ($exists)
		{
			return $planResourceTable->id;
		}

		$data['name']      = $program->name;
		$plausibleData     = self::getPlausibleData($program->gpuntisID);
		$tempArray         = explode('(', $program->name);
		$tempName          = trim($tempArray[0]);
		$data['programID'] = $plausibleData ? self::getRealID($plausibleData, $tempName) : null;
		$success           = $planResourceTable->save($data);

		return $success ? $planResourceTable->id : null;

	}

	/**
	 * Determines whether the data conveyed in the gpuntis ID is plausible for finding a real program.
	 *
	 * @param   string $gpuntisID the id used in untis for this program
	 *
	 * @return  array empty if the id is implausible
	 */
	private static function getPlausibleData($gpuntisID)
	{
		$container       = array();
		$programPieces   = explode('.', $gpuntisID);
		$plausibleNumber = count($programPieces) === 3;
		if ($plausibleNumber)
		{
			$plausibleCode = ctype_upper($programPieces[0]) AND preg_match('/^[A-Z]+$/', $programPieces[0]);
			$plausibleVersion = ctype_digit($programPieces[2]) AND preg_match('/^[2]{1}[0-9]{3}$/', $programPieces[2]);
			$plausibleDegree = ctype_upper($programPieces[1]) AND preg_match('/^[B|M]{1}[A-Z]{1,2}$/', $programPieces[1]);
			if ($plausibleDegree)
			{
				$degreeTable    = JTable::getInstance('degrees', 'thm_organizerTable');
				$degreePullData = array('code' => $programPieces[1]);
				$exists         = $degreeTable->load($degreePullData);
				$degreeID       = $exists ? $degreeTable->id : null;
			}
			if ($plausibleCode AND !empty($degreeID) AND $plausibleVersion)
			{
				$container['code']     = $programPieces[0];
				$container['degreeID'] = $degreeID;
				$container['version']  = $programPieces[2];
			}
		}

		return $container;
	}

	/**
	 * Attempts to get the real program's id, creating the stub if non-existent.
	 *
	 * @param   array  $programData the program data
	 * @param   string $tempName    the name to be used if no entry already exists
	 *
	 * @return mixed int on success, otherwise false
	 */
	private static function getRealID($programData, $tempName)
	{
		$programTable = JTable::getInstance('programs', 'thm_organizerTable');
		$exists       = $programTable->load($programData);
		if ($exists)
		{
			return $programTable->id;
		}

		$formData                    = JFactory::getApplication()->input->get('jform', array(), 'array');
		$programData['departmentID'] = $formData['departmentID'];
		$programData['name_de']      = $tempName;
		$programData['name_en']      = $tempName;
		$success                     = $programTable->save($programData);

		return $success ? $programTable->id : null;
	}

	/**
	 * Validates the resource collection node
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$xmlObject     the xml object being validated
	 *
	 * @return  void
	 */
	public static function validate(&$scheduleModel, &$xmlObject)
	{
		if (empty($xmlObject->departments))
		{
			$scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_PROGRAMS_MISSING");

			return;
		}

		$scheduleModel->schedule->degrees = new stdClass;

		foreach ($xmlObject->departments->children() as $degreeNode)
		{
			self::validateIndividual($scheduleModel, $degreeNode);
		}
	}

	/**
	 * Checks whether program nodes have the expected structure and required information
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$programNode   the degree (program/department) node to be validated
	 *
	 * @return void
	 */
	private static function validateIndividual(&$scheduleModel, &$programNode)
	{
		$gpuntisID = trim((string) $programNode[0]['id']);
		if (empty($gpuntisID))
		{
			if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_PROGRAM_ID_MISSING"), $scheduleModel->scheduleErrors))
			{
				$scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_PROGRAM_ID_MISSING");
			}

			return;
		}

		$degreeID                                               = str_replace('DP_', '', $gpuntisID);
		$scheduleModel->schedule->degrees->$degreeID            = new stdClass;
		$scheduleModel->schedule->degrees->$degreeID->gpuntisID = $degreeID;

		$degreeName = (string) $programNode->longname;
		if (!isset($degreeName))
		{
			$scheduleModel->scheduleErrors[] = JText::sprintf("COM_THM_ORGANIZER_ERROR_PROGRAM_NAME_MISSING", $degreeID);

			return;
		}

		$scheduleModel->schedule->degrees->$degreeID->name = $degreeName;
		$planResourceID                                    = self::getPlanResourceID($scheduleModel->schedule->degrees->$degreeID);
		if (!empty($planResourceID))
		{
			$scheduleModel->schedule->degrees->$degreeID->id = $planResourceID;
			THM_OrganizerHelperXMLSchedule_Resource::setDepartmentResource($planResourceID, 'programID');
		}
	}
}
