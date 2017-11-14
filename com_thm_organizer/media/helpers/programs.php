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

require_once 'departments.php';
require_once 'language.php';

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
	 * @param mixed $program the program object (gpuntisID & name) or string (gpuntisID)
	 *
	 * @return mixed int id on success, otherwise null
	 */
	public static function getID($program)
	{
		$table = JTable::getInstance('plan_programs', 'thm_organizerTable');

		$gpuntisID = is_string($program) ? $program : $program->gpuntisID;
		$pullData  = ['gpuntisID' => $gpuntisID];
		$exists    = $table->load($pullData);

		if ($exists)
		{
			return $table->id;
		}
		elseif (is_string($program))
		{
			return null;
		}

		$pullData = ['name' => $program->name];
		$exists   = $table->load($pullData);

		if ($exists)
		{
			return $table->id;
		}

		return null;
	}

	/**
	 * Retrieves the (plan) program name
	 *
	 * @param int    $programID the table id for the program
	 * @param string $type      the type of the id (real or plan)
	 *
	 * @return string the name of the (plan) program, otherwise empty
	 */
	public static function getName($programID, $type)
	{
		$dbo         = JFactory::getDbo();
		$languageTag = THM_OrganizerHelperLanguage::getShortTag();

		$query     = $dbo->getQuery(true);
		$nameParts = ["p.name_$languageTag", "' ('", "d.abbreviation", "' '", "p.version", "')'"];
		$query->select('ppr.name AS ppName, ' . $query->concatenate($nameParts, "") . ' AS name');

		if ($type == 'real')
		{
			$query->from('#__thm_organizer_programs AS p');
			$query->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
			$query->leftJoin('#__thm_organizer_plan_programs AS ppr ON ppr.programID = p.id');
			$query->where("p.id = '$programID'");
		}
		else
		{
			$query->from('#__thm_organizer_plan_programs AS ppr');
			$query->leftJoin('#__thm_organizer_programs AS p ON ppr.programID = p.id');
			$query->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
			$query->where("ppr.id = '$programID'");
		}

		$dbo->setQuery($query);

		try
		{
			$names = $dbo->loadAssoc();
		}
		catch (RuntimeException $exception)
		{
			JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

			return '';
		}

		return empty($names) ? '' : empty($names['name']) ? $names['ppName'] : $names['name'];
	}

	/**
	 * Getter method for schedule programs in database
	 *
	 * @return array an array of program information
	 *
	 * @throws RuntimeException
	 */
	public static function getPlanPrograms()
	{
		$dbo           = JFactory::getDbo();
		$languageTag   = THM_OrganizerHelperLanguage::getShortTag();
		$departmentIDs = JFactory::getApplication()->input->get('departmentIDs', [], 'raw');

		$query     = $dbo->getQuery(true);
		$nameParts = ["p.name_$languageTag", "' ('", "d.abbreviation", "' '", "p.version", "')'"];
		$query->select('DISTINCT ppr.id, ppr.name AS ppName, ' . $query->concatenate($nameParts, "") . ' AS name');
		$query->from('#__thm_organizer_plan_programs AS ppr');
		$query->innerJoin('#__thm_organizer_plan_pools AS ppo ON ppo.programID = ppr.id');
		$query->leftJoin('#__thm_organizer_programs AS p ON ppr.programID = p.id');
		$query->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');

		if (!empty($departmentIDs))
		{
			$query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.programID = ppr.id');
			$query->where("dr.departmentID IN ('" . str_replace(",", "', '", $departmentIDs) . "')");
		}

		$query->order('ppName');
		$dbo->setQuery($query);

		$default = [];
		try
		{
			$results = $dbo->loadAssocList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return $default;
		}

		return empty($results) ? $default : $results;
	}

	/**
	 * Attempts to get the plan program's id, creating it if non-existent.
	 *
	 * @param object $program the program object
	 *
	 * @return mixed int on success, otherwise null
	 */
	public static function getPlanResourceID($program)
	{
		$programID = self::getID($program);
		if (!empty($programID))
		{
			return $programID;
		}

		$data              = [];
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
	 * @param string $gpuntisID the id used in untis for this program
	 *
	 * @return  array empty if the id is implausible
	 */
	private static function getPlausibleData($gpuntisID)
	{
		$container       = [];
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
				$degreePullData = ['code' => $programPieces[1]];
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
	 * @param array  $programData the program data
	 * @param string $tempName    the name to be used if no entry already exists
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

		$formData                    = JFactory::getApplication()->input->get('jform', [], 'array');
		$programData['departmentID'] = $formData['departmentID'];
		$programData['name_de']      = $tempName;
		$programData['name_en']      = $tempName;

		require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/program.php';

		$model     = JModelLegacy::getInstance('program', 'THM_OrganizerModel');
		$programID = $model->save($programData);

		return empty($programID) ? null : $programID;
	}
}
