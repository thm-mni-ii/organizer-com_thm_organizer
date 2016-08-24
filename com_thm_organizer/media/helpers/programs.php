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

require_once 'department_resources.php';

/**
 * Provides validation methods for xml degree (department) objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperPrograms
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
		$table  = JTable::getInstance('plan_programs', 'thm_organizerTable');
		$data   = array('gpuntisID' => $gpuntisID);
		$exists = $table->load($data);
		if ($exists)
		{
			return $exists ? $table->id : null;
		}

		return null;
	}

	/**
	 * Attempts to get the plan program's id, creating it if non-existent.
	 *
	 * @param   object $program the program object
	 *
	 * @return mixed int on success, otherwise null
	 */
	public static function getPlanResourceID($program)
	{
		$programID = self::getID($program->gpuntisID);
		if (!empty($programID))
		{
			return $programID;
		}

		$data              = array();
		$data['gpuntisID'] = $program->gpuntisID;
		$data['name']      = $program->name;
		$plausibleData     = self::getPlausibleData($program->gpuntisID);
		$tempArray         = explode('(', $program->name);
		$tempName          = trim($tempArray[0]);
		$data['programID'] = $plausibleData ? self::getProgramID($plausibleData, $tempName) : null;
		$planResourceTable = JTable::getInstance('plan_programs', 'thm_organizerTable');
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
	private static function getProgramID($programData, $tempName)
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

		require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/program.php';

		$model     = JModelLegacy::getInstance('program', 'THM_OrganizerModel');
		$programID = $model->save($programData);

		return empty($programID) ? null : $programID;
	}
}
