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

require_once 'department_resources.php';

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
	 * Retrieves the table id if existent.
	 *
	 * @param   string $subjectIndex the subject index (dept. abbreviation + gpuntis id)
	 *
	 * @return mixed int id on success, otherwise null
	 */
	public static function getID($subjectIndex)
	{
		$table  = JTable::getInstance('plan_subjects', 'thm_organizerTable');
		$data   = array('subjectIndex' => $subjectIndex);
		$exists = $table->load($data);
		if ($exists)
		{
			return $exists ? $table->id : null;
		}
		return null;
	}

	/**
	 * Attempts to get the plan subject's id, creating it if non-existent.
	 *
	 * @param   object $subject the subject object
	 *
	 * @return mixed int on success, otherwise null
	 */
	private static function getPlanResourceID($subjectIndex, $subject)
	{
		$subjectID = self::getID($subjectIndex);
		if (!empty($subjectID))
		{
			return $subjectID;
		}

		$data              = array();
		$data['subjectIndex'] = $subjectIndex;
		$data['gpuntisID'] = $subject->gpuntisID;

		if (!empty($subject->fieldID))
		{
			$data['fieldID']   = $subject->fieldID;
		}

		$data['subjectNo'] = $subject->subjectNo;
		$data['name']      = $subject->longname;

		$table   = JTable::getInstance('plan_subjects', 'thm_organizerTable');
		$success = $table->save($data);

		return $success ? $table->id : null;

	}

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
		$scheduleModel->schedule->subjects->$subjectIndex->gpuntisID = $subjectID;
		$scheduleModel->schedule->subjects->$subjectIndex->name      = $subjectID;

		$longName = self::validateLongName($scheduleModel, $subjectNode, $subjectIndex, $subjectID);
		if (!$longName)
		{
			return;
		}

		self::validateSubjectNo($scheduleModel, $subjectNode, $subjectIndex);
		self::validateField($scheduleModel, $subjectNode, $subjectIndex);
		if (!empty($warningString))
		{
			$warning                           = JText::sprintf("COM_THM_ORGANIZER_ERROR_SUBJECT_PROPERTY_MISSING", $longName, $subjectID, $warningString);
			$scheduleModel->scheduleWarnings[] = $warning;
		}

		$planResourceID = self::getPlanResourceID($subjectIndex, $scheduleModel->schedule->subjects->$subjectIndex);
		if (!empty($planResourceID))
		{
			$scheduleModel->schedule->subjects->$subjectIndex->id = $planResourceID;
			THM_OrganizerHelperXMLDepartment_Resources::setDepartmentResource($planResourceID, 'subjectID');
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
