<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Table\Table;
use Organizer\Helpers\Can;
use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Tables\Subjects as SubjectsTable;

/**
 * Class which manages stored subject data.
 */
class Subject extends BaseModel
{
	const COORDINATES = 1;

	const TEACHES = 2;

	/**
	 * Adds a prerequisite association. No access checks => this is not directly accessible and requires differing
	 * checks according to its calling context.
	 *
	 * @param   int    $subjectID       the id of the subject
	 * @param   array  $prerequisiteID  the id of the prerequisite
	 *
	 * @return bool  true on success, otherwise false
	 */
	private function addPrerequisite($subjectID, $prerequisiteID)
	{
		$query = $this->_db->getQuery(true);
		$query->insert('#__thm_organizer_prerequisites')->columns('subjectID, prerequisiteID');
		$query->values("'$subjectID', '$prerequisiteID'");
		$this->_db->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Adds a Subject => Event association. No access checks => this is not directly accessible and requires
	 * differing checks according to its calling context.
	 *
	 * @param   int    $subjectID  the id of the subject
	 * @param   array  $courseIDs  the id of the planSubject
	 *
	 * @return bool  true on success, otherwise false
	 */
	private function addSubjectMappings($subjectID, $courseIDs)
	{
		$query = $this->_db->getQuery(true);
		$query->insert('#__thm_organizer_subject_mappings')->columns('subjectID, courseID');
		foreach ($courseIDs as $courseID)
		{
			$query->values("'$subjectID', '$courseID'");
		}

		$this->_db->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Adds a person association. No access checks => this is not directly accessible and requires differing checks
	 * according to its calling context.
	 *
	 * @param   int    $subjectID  the id of the subject
	 * @param   array  $personID   the id of the person
	 * @param   int    $role       the person's role for the subject
	 *
	 * @return bool  true on success, otherwise false
	 */
	public function addPerson($subjectID, $personID, $role)
	{
		$query = $this->_db->getQuery(true);
		$query->insert('#__thm_organizer_subject_persons')->columns('subjectID, personID, role');
		$query->values("$subjectID, $personID, $role");
		$this->_db->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Checks if the property should be displayed. Setting it to NULL if not.
	 *
	 * @param   array  &$data      the form data
	 * @param   string  $property  the property name
	 *
	 * @return void  can change the &$data value at the property name index
	 */
	private function cleanStarProperty(&$data, $property)
	{
		if (!isset($data[$property]))
		{
			return;
		}

		if ($data[$property] == '-1')
		{
			$data[$property] = 'NULL';
		}
	}

	/**
	 * Attempts to delete the selected subject entries and related mappings
	 *
	 * @return boolean true on success, otherwise false
	 * @throws Exception => unauthorized access
	 */
	public function delete()
	{
		if (!Can::documentTheseDepartments())
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		if ($subjectIDs = Input::getSelectedIDs())
		{
			foreach ($subjectIDs as $subjectID)
			{
				if (!Can::document('subject', $subjectID))
				{
					throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
				}

				if (!$this->deleteSingle($subjectID))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Deletes an individual subject entry in the mappings and subjects tables. No access checks => this is not directly
	 * accessible and requires differing checks according to its calling context.
	 *
	 * @param   int  $subjectID  the id of the subject to be deleted
	 *
	 * @return boolean  true if successful, otherwise false
	 */
	public function deleteSingle($subjectID)
	{
		$table        = new SubjectsTable;
		$mappingModel = new Mapping;

		if (!$mappingModel->deleteByResourceID($subjectID, 'subject'))
		{
			return false;
		}

		if (!$table->delete($subjectID))
		{
			return false;
		}

		return true;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return Table A Table object
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function getTable($name = '', $prefix = '', $options = [])
	{
		return new SubjectsTable;
	}

	/**
	 * Processes the mappings of the subject selected
	 *
	 * @param   array &$data  the post data
	 *
	 * @return boolean  true on success, otherwise false
	 */
	private function processFormMappings(&$data)
	{
		$model = new Mapping;

		// No mappings desired
		if (empty($data['parentID']))
		{
			return $model->deleteByResourceID($data['id'], 'subject');
		}

		return $model->saveSubject($data);
	}

	/**
	 * Processes the subject pre- & postrequisites selected for the subject
	 *
	 * @param   array &$data  the post data
	 *
	 * @return bool  true on success, otherwise false
	 */
	private function processFormPrerequisites(&$data)
	{
		if (!isset($data['prerequisites']) and !isset($data['postrequisites']))
		{
			return true;
		}

		$subjectID = $data['id'];

		if (!$this->removePrerequisites($subjectID))
		{
			return false;
		}

		if (!empty($data['prerequisites']))
		{
			foreach ($data['prerequisites'] as $prerequisiteID)
			{
				if (!$this->addPrerequisite($subjectID, $prerequisiteID))
				{
					return false;
				}
			}
		}

		if (!empty($data['postrequisites']))
		{
			foreach ($data['postrequisites'] as $postrequisiteID)
			{
				if (!$this->addPrerequisite($postrequisiteID, $subjectID))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Processes the subject mappings selected for the subject
	 *
	 * @param   array &$data  the post data
	 *
	 * @return bool  true on success, otherwise false
	 */
	private function processFormSubjectMappings(&$data)
	{
		if (!isset($data['courseIDs']))
		{
			return true;
		}

		$subjectID = $data['id'];

		if (!$this->removeSubjectMappings($subjectID))
		{
			return false;
		}
		if (!empty($data['planSubjectIDs']))
		{
			if (!$this->addSubjectMappings($subjectID, $data['courseIDs']))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Processes the persons selected for the subject
	 *
	 * @param   array &$data  the post data
	 *
	 * @return bool  true on success, otherwise false
	 */
	private function processFormPersons(&$data)
	{
		if (!isset($data['coordinators']) and !isset($data['persons']))
		{
			return true;
		}

		$subjectID = $data['id'];

		if (!$this->removePersons($subjectID))
		{
			return false;
		}

		$coordinators = array_filter($data['coordinators']);
		if (!empty($coordinators))
		{
			foreach ($coordinators as $coordinatorID)
			{
				if (!$this->addPerson($subjectID, $coordinatorID, self::COORDINATES))
				{
					return false;
				}
			}
		}

		$persons = array_filter($data['persons']);
		if (!empty($persons))
		{
			foreach ($persons as $personID)
			{
				if (!$this->addPerson($subjectID, $personID, self::TEACHES))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Removes pre- & postrequisite associations for the given subject. No access checks => this is not directly
	 * accessible and requires differing checks according to its calling context.
	 *
	 * @param   int  $subjectID  the subject id
	 *
	 * @return boolean
	 */
	private function removePrerequisites($subjectID)
	{
		$query = $this->_db->getQuery(true);
		$query->delete('#__thm_organizer_prerequisites')
			->where("subjectID = '$subjectID' OR prerequisiteID ='$subjectID'");
		$this->_db->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Removes planSubject associations for the given subject. No access checks => this is not directly accessible and
	 * requires differing checks according to its calling context.
	 *
	 * @param   int  $subjectID  the subject id
	 *
	 * @return boolean
	 */
	private function removeSubjectMappings($subjectID)
	{
		$query = $this->_db->getQuery(true);
		$query->delete('#__thm_organizer_subject_mappings')->where("subjectID = '$subjectID'");
		$this->_db->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Removes person associations for the given subject and role. No access checks => this is not directly
	 * accessible and requires differing checks according to its calling context.
	 *
	 * @param   int  $subjectID  the subject id
	 * @param   int  $role       the person role
	 *
	 * @return boolean
	 */
	public function removePersons($subjectID, $role = null)
	{
		$query = $this->_db->getQuery(true);
		$query->delete('#__thm_organizer_subject_persons')->where("subjectID = '$subjectID'");
		if (!empty($role))
		{
			$query->where("role = $role");
		}

		$this->_db->setQuery($query);

		return (bool) OrganizerHelper::executeQuery('execute');
	}

	/**
	 * Attempts to save the resource.
	 *
	 * @param   array  $data  form data which has been preprocessed by inheriting classes.
	 *
	 * @return mixed int id of the resource on success, otherwise boolean false
	 * @throws Exception => unauthorized access
	 */
	public function save($data = [])
	{
		$data = empty($data) ? Input::getFormItems()->toArray() : $data;

		if (!isset($data['id']))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
		}
		elseif (!Can::document('subject', $data['id']))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$data['creditpoints'] = (float) $data['creditpoints'];

		$starProperties = ['expertise', 'selfCompetence', 'methodCompetence', 'socialCompetence'];
		foreach ($starProperties as $property)
		{
			$this->cleanStarProperty($data, $property);
		}

		$table = new SubjectsTable;

		if (!$table->save($data))
		{
			return false;
		}

		$processMappings = (!empty($data['id']) and isset($data['parentID']));
		$data['id']      = $table->id;

		if (!$this->processFormPersons($data))
		{
			return false;
		}

		if (!$this->processFormSubjectMappings($data))
		{
			return false;
		}

		if (!$this->processFormPrerequisites($data))
		{
			return false;
		}

		if ($processMappings and !$this->processFormMappings($data))
		{
			return false;
		}

		$lessonID = Input::getInt('lessonID');
		if (!empty($lessonID))
		{
			Courses::refreshWaitList($lessonID);
		}

		return $table->id;
	}
}
