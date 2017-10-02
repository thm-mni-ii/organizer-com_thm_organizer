<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSubject_Details
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_COMPONENT . '/helpers/teacher.php';

/**
 * Class THM_OrganizerModeldetails for component com_thm_organizer
 *
 * Class provides methods to get details about modules
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSubject_Details extends JModelLegacy
{
	/**
	 * Loads subject information from the database
	 *
	 * @return  object  filled with subject data on success, otherwise empty
	 */
	public function getItem()
	{
		$subjectID = $this->resolveID();
		if (empty($subjectID))
		{
			return new stdClass;
		}

		$input   = JFactory::getApplication()->input;
		$langTag = $input->getString('languageTag', THM_OrganizerHelperLanguage::getShortTag());
		$query   = $this->_db->getQuery(true);

		$select = "s.id, externalID, name_$langTag AS name, description_$langTag AS description, ";
		$select .= "objective_$langTag AS objective, content_$langTag AS content, instructionLanguage, ";
		$select .= "preliminary_work_$langTag AS preliminary_work, literature, creditpoints, expenditure, ";
		$select .= "present, independent, proof_$langTag AS proof, frequency_$langTag AS frequency, ";
		$select .= "method_$langTag AS method, recommended_prerequisites_$langTag as recommended_prerequisites, ";
		$select .= "prerequisites_$langTag AS prerequisites, aids_$langTag AS aids, used_for_$langTag AS prerequisiteOf, ";
		$select .= "evaluation_$langTag AS evaluation, sws, expertise, method_competence, self_competence, ";
		$select .= "social_competence, duration";

		$query->select($select);
		$query->from('#__thm_organizer_subjects AS s');
		$query->leftJoin('#__thm_organizer_frequencies AS f ON s.frequencyID = f.id');
		$query->where("s.id = '$subjectID'");
		$this->_db->setQuery($query);

		try
		{
			$subject = $this->_db->loadObject();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return new stdClass;
		}

		// This should not occur.
		if (empty($subject->id))
		{
			return new stdClass;
		}

		$this->setExpenditureText($subject);
		$this->setDependencies($subject);
		$this->setTeachers($subject);

		return $subject;
	}

	/**
	 * Attempts to determine the desired subject id
	 *
	 * @return  mixed  int on success, otherwise null
	 */
	private function resolveID()
	{
		$input     = JFactory::getApplication()->input;
		$requestID = $input->getInt('id', 0);

		if (!empty($requestID))
		{
			// Ensure that the requested ID is existent in the table
			$query = $this->_db->getQuery(true);
			$query->select('id')->from('#__thm_organizer_subjects')->where("id = '$requestID'");
			$this->_db->setQuery($query);

			try
			{
				return $this->_db->loadResult();
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

				return null;
			}
		}

		$externalID = $input->getString('nrmni', '');
		if (empty($externalID))
		{
			return null;
		}

		$query = $this->_db->getQuery(true);
		$query->select('id')->from('#__thm_organizer_subjects')->where("externalID = '$externalID'");
		$this->_db->setQuery($query);

		try
		{
			return $this->_db->loadResult();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return null;
		}
	}

	/**
	 * Creates a textual output for the various expenditure values
	 *
	 * @param object &$subject the object containing subject data
	 *
	 * @return  void  sets values in the references object
	 */
	private function setExpenditureText(&$subject)
	{
		$useFullText = (!empty($subject->creditpoints) AND !empty($subject->expenditure) AND !empty($subject->present));

		if ($useFullText)
		{
			$subject->expenditureOutput = THM_OrganizerHelperLanguage::sprintf('COM_THM_ORGANIZER_EXPENDITURE_FULL',
				$subject->creditpoints,
				$subject->expenditure,
				$subject->present
			);

			return;
		}

		$useMediumText = (!empty($subject->creditpoints) AND !empty($subject->expenditure));

		if ($useMediumText)
		{
			$subject->expenditureOutput = THM_OrganizerHelperLanguage::sprintf('COM_THM_ORGANIZER_EXPENDITURE_MEDIUM',
				$subject->creditpoints,
				$subject->expenditure
			);

			return;
		}

		if (!empty($subject->creditpoints))
		{
			$subject->expenditureOutput = THM_OrganizerHelperLanguage::sprintf('COM_THM_ORGANIZER_EXPENDITURE_SHORT', $subject->creditpoints);
		}
	}

	/**
	 * Loads an array of names and links into the subject model for subjects for
	 * which this subject is a prerequisite.
	 *
	 * @param object &$subject the object containing subject data
	 *
	 * @return void
	 */
	private function setTeachers(&$subject)
	{
		$teacherData = THM_OrganizerHelperTeacher::getDataBySubject($subject->id, null, true, false);

		if (empty($teacherData))
		{
			return;
		}

		$executors = [];
		$teachers  = [];
		foreach ($teacherData as $teacher)
		{
			$teacher['name'] = THM_OrganizerHelperTeacher::getDefaultName($teacher);

			if ($teacher['teacherResp'] == '1')
			{
				$executors[$teacher['id']] = $teacher;
			}
			else
			{
				$teachers[$teacher['id']] = $teacher;
			}
		}
		$subject->executors = $executors;
		$subject->teachers  = $teachers;
	}

	/**
	 * Loads an array of names and links into the subject model for subjects for
	 * which this subject is a prerequisite.
	 *
	 * @param object &$subject the object containing subject data
	 *
	 * @return  void
	 */
	private function setDependencies(&$subject)
	{
		$subjectID = $subject->id;
		$langTag   = THM_OrganizerHelperLanguage::getShortTag();
		$programs  = THM_OrganizerHelperMapping::getSubjectPrograms($subjectID);

		$query  = $this->_db->getQuery(true);
		$select = "DISTINCT pr.id AS id, ";
		$select .= "s1.id AS preID, s1.name_$langTag AS preName, s1.externalID AS preModuleNumber, ";
		$select .= "s2.id AS postID, s2.name_$langTag AS postName, s2.externalID AS postModuleNumber";
		$query->select($select);
		$query->from('#__thm_organizer_prerequisites AS pr');
		$query->innerJoin('#__thm_organizer_mappings AS m1 ON pr.prerequisite = m1.id');
		$query->innerJoin('#__thm_organizer_subjects AS s1 ON m1.subjectID = s1.id');
		$query->innerJoin('#__thm_organizer_mappings AS m2 ON pr.subjectID = m2.id');
		$query->innerJoin('#__thm_organizer_subjects AS s2 ON m2.subjectID = s2.id');

		foreach ($programs as $programID => $program)
		{
			$query->clear('where');
			$query->where("m1.lft > {$program['lft']} AND m1.rgt < {$program['rgt']}");
			$query->where("m2.lft > {$program['lft']} AND m2.rgt < {$program['rgt']}");
			$query->where("(s1.id = $subjectID OR s2.id = $subjectID)");
			$this->_db->setQuery($query);

			try
			{
				$dependencies = $this->_db->loadAssocList('id');
			}
			catch (Exception $exc)
			{
				continue;
			}

			if (empty($dependencies))
			{
				continue;
			}

			foreach ($dependencies as $dependency)
			{
				if ($dependency['preID'] == $subjectID)
				{
					if (empty($subject->postSubjects))
					{
						$subject->postSubjects = [];
					}
					if (empty($subject->postSubjects[$programID]))
					{
						$subject->postSubjects[$programID]             = [];
						$subject->postSubjects[$programID]['name']     = $program['name'];
						$subject->postSubjects[$programID]['subjects'] = [];
					}
					$name                                                                 = $dependency['postName'];
					$name                                                                 .= empty($dependency['postModuleNumber']) ? '' : " ({$dependency['postModuleNumber']})";
					$subject->postSubjects[$programID]['subjects'][$dependency['postID']] = $name;
				}
				else
				{
					if (empty($subject->preSubjects))
					{
						$subject->preSubjects = [];
					}
					if (empty($subject->preSubjects[$programID]))
					{
						$subject->preSubjects[$programID]             = [];
						$subject->preSubjects[$programID]['name']     = $program['name'];
						$subject->preSubjects[$programID]['subjects'] = [];
					}
					$name                                                               = $dependency['preName'];
					$name                                                               .= empty($dependency['preModuleNumber']) ? '' : " ({$dependency['preModuleNumber']})";
					$subject->preSubjects[$programID]['subjects'][$dependency['preID']] = $name;
				}
			}

			if (isset($subject->preSubjects[$programID]['subjects']))
			{
				asort($subject->preSubjects[$programID]['subjects']);
			}

			if (isset($subject->postSubjects[$programID]['subjects']))
			{
				asort($subject->postSubjects[$programID]['subjects']);
			}
		}
	}
}
