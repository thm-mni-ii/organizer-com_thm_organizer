<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperXMLTeachers
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/departments.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/teachers.php';

/**
 * Provides validation methods for xml teacher objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperXMLTeachers
{
	/**
	 * Checks whether teacher nodes have the expected structure and required information
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$xmlObject     the xml object being validated
	 *
	 * @return void
	 */
	public static function validate(&$scheduleModel, &$xmlObject)
	{
		if (empty($xmlObject->teachers))
		{
			$scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_TEACHERS_MISSING");

			return;
		}

		$scheduleModel->newSchedule->teachers = new stdClass;

		foreach ($xmlObject->teachers->children() as $teacherNode)
		{
			self::validateIndividual($scheduleModel, $teacherNode);
		}

		if (!empty($scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']))
		{
			$warningCount = $scheduleModel->scheduleWarnings['TEACHER-EXTERNALID'];
			unset($scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']);
			$scheduleModel->scheduleWarnings[] = sprintf(JText::_('COM_THM_ORGANIZER_WARNING_TEACHER_EXTID_MISSING'), $warningCount);
		}

		if (!empty($scheduleModel->scheduleWarnings['TEACHER-FORENAME']))
		{
			$warningCount = $scheduleModel->scheduleWarnings['TEACHER-FORENAME'];
			unset($scheduleModel->scheduleWarnings['TEACHER-FORENAME']);
			$scheduleModel->scheduleWarnings[] = sprintf(JText::_('COM_THM_ORGANIZER_WARNING_FORENAME_MISSING'), $warningCount);
		}

		if (!empty($scheduleModel->scheduleWarnings['TEACHER-TITLE']))
		{
			$warningCount = $scheduleModel->scheduleWarnings['TEACHER-TITLE'];
			unset($scheduleModel->scheduleWarnings['TEACHER-TITLE']);
			$scheduleModel->scheduleWarnings[] = sprintf(JText::_('COM_THM_ORGANIZER_WARNING_TITLE_MISSING'), $warningCount);
		}

		if (!empty($scheduleModel->scheduleWarnings['TEACHER-FIELD']))
		{
			$warningCount = $scheduleModel->scheduleWarnings['TEACHER-FIELD'];
			unset($scheduleModel->scheduleWarnings['TEACHER-FIELD']);
			$scheduleModel->scheduleWarnings[] = sprintf(JText::_('COM_THM_ORGANIZER_WARNING_TEACHER_FIELD_MISSING'), $warningCount);
		}

		if (!empty($scheduleModel->scheduleWarnings['TEACHER-USERNAME']))
		{
			$warningCount = $scheduleModel->scheduleWarnings['TEACHER-USERNAME'];
			unset($scheduleModel->scheduleWarnings['TEACHER-USERNAME']);
			$scheduleModel->scheduleWarnings[] = sprintf(JText::_('COM_THM_ORGANIZER_WARNING_USERNAME_MISSING'), $warningCount);
		}
	}

	/**
	 * Checks whether teacher nodes have the expected structure and required
	 * information
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$teacherNode   the teacher node to be validated
	 *
	 * @return void
	 */
	private static function validateIndividual(&$scheduleModel, &$teacherNode)
	{

		$gpuntisID = self::validateUntisID($scheduleModel, $teacherNode);

		if (!$gpuntisID)
		{
			return;
		}

		$gpuntisID                                                   = str_replace('TR_', '', $gpuntisID);
		$scheduleModel->newSchedule->teachers->$gpuntisID            = new stdClass;
		$scheduleModel->newSchedule->teachers->$gpuntisID->gpuntisID = $gpuntisID;

		$scheduleModel->newSchedule->teachers->$gpuntisID->localUntisID
			= str_replace('TR_', '', trim((string) $teacherNode[0]['id']));

		$surname = self::validateSurname($scheduleModel, $teacherNode, $gpuntisID);

		if (!$surname)
		{
			unset($scheduleModel->newSchedule->teachers->$gpuntisID);

			return;
		}

		self::validateField($scheduleModel, $teacherNode, $gpuntisID);
		self::validateForename($scheduleModel, $teacherNode, $gpuntisID);
		self::validateTitle($scheduleModel, $teacherNode, $gpuntisID);
		self::validateUserName($scheduleModel, $teacherNode, $gpuntisID);


		$teacherID = THM_OrganizerHelperTeachers::getID($gpuntisID, $scheduleModel->newSchedule->teachers->$gpuntisID);

		if (!empty($teacherID))
		{
			$scheduleModel->newSchedule->teachers->$gpuntisID->id = $teacherID;
			THM_OrganizerHelperDepartments::setDepartmentResource($teacherID, 'teacherID');
		}
	}

	/**
	 * Validates the teacher's field attribute
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$teacherNode   the teacher node object
	 * @param string $teacherID      the teacher's id
	 *
	 * @return  void
	 */
	private static function validateField(&$scheduleModel, &$teacherNode, $teacherID)
	{
		$fieldID        = str_replace('DS_', '', trim($teacherNode->teacher_description[0]['id']));
		$invalidFieldID = (empty($fieldID) OR empty($scheduleModel->schedule->fields->$fieldID));
		if ($invalidFieldID)
		{
			$scheduleModel->newSchedule->teachers->$teacherID->description = '';
			$scheduleModel->newSchedule->teachers->$teacherID->fieldID     = '';

			return;
		}

		$scheduleModel->newSchedule->teachers->$teacherID->description = $fieldID;
		$scheduleModel->newSchedule->teachers->$teacherID->fieldID     = $scheduleModel->schedule->fields->$fieldID->id;
	}

	/**
	 * Validates the teacher's forename attribute
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$teacherNode   the teacher node object
	 * @param string $teacherID      the teacher's id
	 *
	 * @return  void
	 */
	private static function validateForename(&$scheduleModel, &$teacherNode, $teacherID)
	{
		$forename = trim((string) $teacherNode->forename);
		if (empty($forename))
		{
			$scheduleModel->scheduleWarnings['TEACHER-FORENAME']
				= empty($scheduleModel->scheduleWarnings['TEACHER-FORENAME']) ?
				1 : $scheduleModel->scheduleWarnings['TEACHER-FORENAME'] + 1;
		}

		$scheduleModel->newSchedule->teachers->$teacherID->forename = empty($forename) ? '' : $forename;
	}

	/**
	 * Validates the teacher's surname
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$teacherNode   the teacher node object
	 * @param string $teacherID      the teacher's id
	 *
	 * @return  mixed  string surname if valid, otherwise false
	 */
	private static function validateSurname(&$scheduleModel, &$teacherNode, $teacherID)
	{
		$surname = trim((string) $teacherNode->surname);
		if (empty($surname))
		{
			$scheduleModel->scheduleErrors[] = sprintf(JText::_('COM_THM_ORGANIZER_ERROR_TEACHER_SURNAME_MISSING'), $teacherID);

			return false;
		}

		$scheduleModel->newSchedule->teachers->$teacherID->surname = $surname;

		return $surname;
	}

	/**
	 * Validates the teacher's description attribute
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$teacherNode   the teacher node object
	 * @param string $teacherID      the teacher's id
	 *
	 * @return  void
	 */
	private static function validateTitle(&$scheduleModel, &$teacherNode, $teacherID)
	{
		$title = trim((string) $teacherNode->title);
		if (empty($title))
		{
			$scheduleModel->scheduleWarnings['TEACHER-TITLE']
				= empty($scheduleModel->scheduleWarnings['TEACHER-TITLE']) ?
				1 : $scheduleModel->scheduleWarnings['TEACHER-TITLE'] + 1;
		}

		$scheduleModel->newSchedule->teachers->$teacherID->title = empty($title) ? '' : $title;
	}

	/**
	 * Validates the teacher's untis id
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$teacherNode   the teacher node object
	 *
	 * @return  mixed  string untis id if valid, otherwise false
	 */
	private static function validateUntisID(&$scheduleModel, &$teacherNode)
	{
		$externalID = trim((string) $teacherNode->external_name);
		$internalID = trim((string) $teacherNode[0]['id']);
		if (empty($internalID))
		{
			if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_TEACHER_ID_MISSING"), $scheduleModel->scheduleErrors))
			{
				$scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_TEACHER_ID_MISSING");
			}

			return false;
		}

		if (empty($externalID))
		{
			$scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']
				       = empty($scheduleModel->scheduleWarnings['TEACHER-EXTERNALID']) ?
				1 : $scheduleModel->scheduleWarnings['TEACHER-EXTERNALID'] + 1;
			$gpuntisID = $internalID;
		}
		else
		{
			$gpuntisID = $externalID;
		}

		return $gpuntisID;
	}

	/**
	 * Validates the teacher's description attribute
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$teacherNode   the teacher node object
	 * @param string $teacherID      the teacher's id
	 *
	 * @return  void
	 */
	private static function validateUserName(&$scheduleModel, &$teacherNode, $teacherID)
	{
		$userName = trim((string) $teacherNode->payrollnumber);
		if (empty($userName))
		{
			$scheduleModel->scheduleWarnings['TEACHER-USERNAME'] = empty($scheduleModel->scheduleWarnings['TEACHER-USERNAME']) ?
				1 : $scheduleModel->scheduleWarnings['TEACHER-USERNAME'] + 1;
		}

		$scheduleModel->newSchedule->teachers->$teacherID->username = empty($userName) ? '' : $userName;
	}
}
