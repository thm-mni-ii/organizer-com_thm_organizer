<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerHelperXMLSubjects
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/departments.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/subjects.php';

/**
 * Provides validation methods for xml subject objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperXMLSubjects
{
	/**
	 * Checks whether subject nodes have the expected structure and required information
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$xmlObject     the xml object being validated
	 *
	 * @return void
	 */
	public static function validate(&$scheduleModel, &$xmlObject)
	{
		if (empty($xmlObject->subjects))
		{
			$scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_SUBJECTS_MISSING");

			return;
		}

		$scheduleModel->newSchedule->subjects = new stdClass;

		foreach ($xmlObject->subjects->children() as $subjectNode)
		{
			self::validateIndividual($scheduleModel, $subjectNode);
		}

		if (!empty($scheduleModel->scheduleWarnings['SUBJECT-NO']))
		{
			$warningCount = $scheduleModel->scheduleWarnings['SUBJECT-NO'];
			unset($scheduleModel->scheduleWarnings['SUBJECT-NO']);
			$scheduleModel->scheduleWarnings[] = JText::sprintf('COM_THM_ORGANIZER_WARNING_SUBJECTNO_MISSING', $warningCount);
		}

		if (!empty($scheduleModel->scheduleWarnings['SUBJECT-FIELD']))
		{
			$warningCount = $scheduleModel->scheduleWarnings['SUBJECT-FIELD'];
			unset($scheduleModel->scheduleWarnings['SUBJECT-FIELD']);
			$scheduleModel->scheduleWarnings[] = JText::sprintf('COM_THM_ORGANIZER_WARNING_SUBJECT_FIELD_MISSING', $warningCount);
		}
	}

	/**
	 * Checks whether subject nodes have the expected structure and required
	 * information
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$subjectNode   the subject node to be validated
	 *
	 * @return void
	 */
	private static function validateIndividual(&$scheduleModel, &$subjectNode)
	{
		$gpuntisID = trim((string) $subjectNode[0]['id']);
		if (empty($gpuntisID))
		{
			if (!in_array(JText::_("COM_THM_ORGANIZER_ERROR_SUBJECT_ID_MISSING"), $scheduleModel->scheduleErrors))
			{
				$scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_ERROR_SUBJECT_ID_MISSING");
			}

			return;
		}

		$department                                                     = $scheduleModel->newSchedule->departmentname;
		$gpuntisID                                                      = str_replace('SU_', '', $gpuntisID);
		$subjectIndex                                                   = $department . "_" . $gpuntisID;
		$scheduleModel->newSchedule->subjects->$subjectIndex            = new stdClass;
		$scheduleModel->newSchedule->subjects->$subjectIndex->gpuntisID = $gpuntisID;
		$scheduleModel->newSchedule->subjects->$subjectIndex->name      = $gpuntisID;

		$longName = self::validateLongName($scheduleModel, $subjectNode, $subjectIndex, $gpuntisID);

		if (!$longName)
		{
			unset($scheduleModel->newSchedule->subjects->$subjectIndex);

			return;
		}

		self::validateSubjectNo($scheduleModel, $subjectNode, $subjectIndex);
		self::validateField($scheduleModel, $subjectNode, $subjectIndex);

		if (!empty($warningString))
		{
			$warning = JText::sprintf("COM_THM_ORGANIZER_ERROR_SUBJECT_PROPERTY_MISSING", $longName, $gpuntisID, $warningString);

			$scheduleModel->scheduleWarnings[] = $warning;
		}

		$planResourceID = THM_OrganizerHelperSubjects::getPlanResourceID($subjectIndex, $scheduleModel->newSchedule->subjects->$subjectIndex);

		if (!empty($planResourceID))
		{
			$scheduleModel->newSchedule->subjects->$subjectIndex->id = $planResourceID;
			THM_OrganizerHelperDepartments::setDepartmentResource($planResourceID, 'subjectID');
		}
	}

	/**
	 * Validates the subject's longname
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$subjectNode   the subject node object
	 * @param string $subjectIndex   the subject's interdepartment unique identifier
	 * @param string $subjectID      the subject's id
	 *
	 * @return  mixed  string longname if valid, otherwise false
	 */
	private static function validateLongName(&$scheduleModel, &$subjectNode, $subjectIndex, $subjectID)
	{
		$longName = trim((string) $subjectNode->longname);
		if (empty($longName))
		{
			$scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_ERROR_SUBJECT_LONGNAME_MISSING', $subjectID);

			return false;
		}

		$scheduleModel->newSchedule->subjects->$subjectIndex->longname = $longName;

		return $longName;
	}

	/**
	 * Validates the subject's subject number (text) attribute
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$subjectNode   the subject node object
	 * @param string $subjectIndex   the subject's interdepartment unique identifier
	 *
	 * @return  void
	 */
	private static function validateSubjectNo(&$scheduleModel, &$subjectNode, $subjectIndex)
	{
		$subjectNo = trim((string) $subjectNode->text);
		if (empty($subjectNo))
		{
			$scheduleModel->scheduleWarnings['SUBJECT-NO']
				                                                            = empty($scheduleModel->scheduleWarnings['SUBJECT-NO']) ?
				1 : $scheduleModel->scheduleWarnings['SUBJECT-NO'] + 1;
			$scheduleModel->newSchedule->subjects->$subjectIndex->subjectNo = '';

			return;
		}

		$scheduleModel->newSchedule->subjects->$subjectIndex->subjectNo = $subjectNo;
	}

	/**
	 * Validates the subject's field (description) attribute
	 *
	 * @param object &$scheduleModel the validating schedule model
	 * @param object &$subjectNode   the subject node object
	 * @param string $subjectIndex   the subject's interdepartment unique identifier
	 *
	 * @return  void
	 */
	private static function validateField(&$scheduleModel, &$subjectNode, $subjectIndex)
	{
		$untisID      = str_replace('DS_', '', trim($subjectNode->subject_description[0]['id']));
		$invalidField = (empty($fieldID) OR empty($scheduleModel->newSchedule->fields->$untisID));

		if ($invalidField)
		{
			$scheduleModel->newSchedule->subjects->$subjectIndex->description = '';

			return;
		}

		$scheduleModel->newSchedule->subjects->$subjectIndex->description = $untisID;
		$scheduleModel->newSchedule->subjects->$subjectIndex->fieldID     = $scheduleModel->newSchedule->fields->$fieldID->id;
	}
}
