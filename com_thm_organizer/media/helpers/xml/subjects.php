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
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$xmlObject     the xml object being validated
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

		$scheduleModel->schedule->subjects = new stdClass;

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

		return;
	}

	/**
	 * Checks whether subject nodes have the expected structure and required
	 * information
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$subjectNode   the subject node to be validated
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

		$department                                                  = $scheduleModel->schedule->departmentname;
		$subjectID                                                   = str_replace('SU_', '', $gpuntisID);
		$subjectIndex                                                = $department . "_" . $subjectID;
		$scheduleModel->schedule->subjects->$subjectIndex            = new stdClass;
		$scheduleModel->schedule->subjects->$subjectIndex->gpuntisID = $gpuntisID;
		$scheduleModel->schedule->subjects->$subjectIndex->name      = $subjectID;

		$longname = self::validateLongName($scheduleModel, $subjectNode, $subjectIndex, $subjectID);
		if (!$longname)
		{
			return;
		}

		self::validateSubjectNo($scheduleModel, $subjectNode, $subjectIndex);
		self::validateField($scheduleModel, $subjectNode, $subjectIndex);
		if (!empty($warningString))
		{
			$warning                           = JText::sprintf("COM_THM_ORGANIZER_ERROR_SUBJECT_PROPERTY_MISSING", $longname, $subjectID, $warningString);
			$scheduleModel->scheduleWarnings[] = $warning;
		}
	}

	/**
	 * Validates the subject's longname
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$subjectNode   the subject node object
	 * @param   string $subjectIndex   the subject's interdepartment unique identifier
	 * @param   string $subjectID      the subject's id
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

		$scheduleModel->schedule->subjects->$subjectIndex->longname = $longName;

		return $longName;
	}

	/**
	 * Validates the subject's subject number (text) attribute
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$subjectNode   the subject node object
	 * @param   string $subjectIndex   the subject's interdepartment unique identifier
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
			$scheduleModel->schedule->subjects->$subjectIndex->subjectNo = '';

			return;
		}

		$scheduleModel->schedule->subjects->$subjectIndex->subjectNo = $subjectNo;
	}

	/**
	 * Validates the subject's field (description) attribute
	 *
	 * @param   object &$scheduleModel the validating schedule model
	 * @param   object &$subjectNode   the subject node object
	 * @param   string $subjectIndex   the subject's interdepartment unique identifier
	 *
	 * @return  void
	 */
	private static function validateField(&$scheduleModel, &$subjectNode, $subjectIndex)
	{
		$fieldID      = str_replace('DS_', '', trim($subjectNode->subject_description[0]['id']));
		$invalidField = (empty($fieldID) OR empty($scheduleModel->schedule->fields->$fieldID));
		if ($invalidField)
		{
			$scheduleModel->scheduleWarnings['SUBJECT-FIELD']
				                                                           = empty($scheduleModel->scheduleWarnings['SUBJECT-FIELD']) ?
				1 : $scheduleModel->scheduleWarnings['SUBJECT-FIELD'] + 1;
			$scheduleModel->schedule->subjects->$subjectIndex->description = '';

			return;
		}

		$scheduleModel->schedule->subjects->$subjectIndex->description = $fieldID;
		$scheduleModel->schedule->subjects->$subjectIndex->fieldID     = $scheduleModel->schedule->fields->$fieldID->id;
	}
}