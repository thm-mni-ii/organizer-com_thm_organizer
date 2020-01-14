<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;
use Organizer\Tables;

/**
 * Provides general functions for participant access checks, data retrieval and display.
 */
class Participants extends ResourceHelper
{
	// Course participant status codes
	const WAIT_LIST = 0, REGISTERED = 1, REMOVED = 2;

	// Constants providing context for adding/removing instances to/from personal schedules.
	const SEMESTER_MODE = 1, BLOCK_MODE = 2, INSTANCE_MODE = 3;

	/**
	 * Determines whether the necessary participant properties have been set to register for a course.
	 *
	 * @param   int  $participantID  the id of the participant
	 *
	 * @return bool true if the necessary participant information has been set, otherwise false
	 */
	public static function canRegister($participantID)
	{
		$table = new Tables\Participants;
		if ($table->load($participantID))
		{
			$valid = true;
			$valid = ($valid and (bool) $table->address);
			$valid = ($valid and (bool) $table->city);
			$valid = ($valid and (bool) $table->forename);
			$valid = ($valid and (bool) $table->programID);
			$valid = ($valid and (bool) $table->surname);

			return ($valid and (bool) $table->zipCode);
		}

		return false;
	}

	/**
	 * Changes a participants state.
	 *
	 * @param   int  $participantID  the participant's id
	 * @param   int  $courseID       the course's id
	 * @param   int  $state          the requested state
	 *
	 * @return bool true on success, otherwise false
	 */
	public static function changeState($participantID, $courseID, $state)
	{
		switch ($state)
		{
			case self::WAIT_LIST:
			case self::REGISTERED:
				$table = new Tables\CourseParticipants;

				$data = [
					'lessonID' => $courseID,
					'userID'   => $participantID
				];

				$table->load($data);

				$now                   = date('Y-m-d H:i:s');
				$data['user_date']     = $now;
				$data['status_date']   = $now;
				$data['status']        = $state;
				$data['configuration'] = Courses::getInstances($courseID);

				$success = $table->save($data);

				break;

			case self::REMOVED:
				$dbo   = Factory::getDbo();
				$query = $dbo->getQuery(true);
				$query->delete('#__thm_organizer_user_lessons');
				$query->where("userID = '$participantID'");
				$query->where("lessonID = '$courseID'");
				$dbo->setQuery($query);
				$success = (bool) OrganizerHelper::executeQuery('execute');
				if (!$success)
				{
					return false;
				}

				break;
		}

		if (empty($success))
		{
			return false;
		}

		self::notify($participantID, $courseID, $state);

		return true;
	}

	/**
	 * Retrieves the ids of the courses with which the participant is associated.
	 *
	 * @param   int  $participantID  the id of the participant
	 *
	 * @return array the associated course ids if existent, otherwise empty
	 */
	public static function getCourses($participantID)
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('courseID')
			->from('#__thm_organizer_course_participants')
			->where("participantID = $participantID");
		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadColumn', []);
	}

	/**
	 * Checks whether all the necessary participant information has been entered.
	 *
	 * @param   int     $participantID  the id of the participant to validate
	 * @param   string  $context        the name of the validation context
	 *
	 * @return bool true if the participant entry is incomplete, otherwise false
	 */
	public static function incomplete($participantID = 0, $context = 'preparatory')
	{
		$participantID = empty($participantID) ? Factory::getUser()->id : $participantID;

		if (empty($participantID))
		{
			return true;
		}

		$table = new Tables\Participants;
		if (!$table->load($participantID))
		{
			return true;
		}

		if ($context === 'preparatory')
		{
			$requiredProperties = ['address', 'city', 'forename', 'programID', 'surname', 'zipCode'];
		}
		else
		{
			$requiredProperties = [];
		}

		foreach ($requiredProperties as $property)
		{
			if (empty($table->get($property)))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * deletes lessons in the personal schedule of a logged in user
	 *
	 * @return string JSON coded and deleted ccmIDs
	 * @throws Exception => invalid request / unauthorized access
	 */
	public static function removeInstance()
	{
		$ccmID = Input::getInt('ccmID');
		if (empty($ccmID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
		}

		$userID = Factory::getUser()->id;
		if (empty($userID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$mode     = Input::getInt('mode', self::BLOCK_MODE);
		$mappings = self::getMatchingLessons($mode, $ccmID);

		$deletedCcmIDs = [];
		foreach ($mappings as $lessonID => $ccmIDs)
		{
			$userLessonTable = new Tables\CourseParticipants;

			if (!$userLessonTable->load(['userID' => $userID, 'lessonID' => $lessonID]))
			{
				continue;
			}

			$deletedCcmIDs = array_merge($deletedCcmIDs, $ccmIDs);

			// Delete a lesson completely? delete whole row in database
			if ($mode == self::SEMESTER_MODE)
			{
				$userLessonTable->delete($userLessonTable->id);
			}
			else
			{
				$configurations = array_flip(json_decode($userLessonTable->configuration));
				foreach ($ccmIDs as $ccmID)
				{
					unset($configurations[$ccmID]);
				}

				$configurations = array_flip($configurations);
				if (empty($configurations))
				{
					$userLessonTable->delete($userLessonTable->id);
				}
				else
				{
					$conditions = [
						'id'            => $userLessonTable->id,
						'userID'        => $userID,
						'lessonID'      => $userLessonTable->lessonID,
						'configuration' => array_values($configurations),
						'user_date'     => date('Y-m-d H:i:s')
					];
					$userLessonTable->bind($conditions);
				}

				$userLessonTable->store();
			}
		}

		return $deletedCcmIDs;
	}

	/**
	 * Saves lesson instance references in the personal schedule of the user
	 *
	 * @return array saved ccmIDs
	 * @throws Exception => invalid request / unauthorized access
	 */
	public static function saveUserLesson()
	{
		$ccmID = Input::getInt('ccmID');
		if (empty($ccmID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
		}

		$userID = Factory::getUser()->id;
		if (empty($userID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$savedCcmIDs = [];
		$mode        = Input::getInt('mode', self::BLOCK_MODE);
		$mappings    = self::getMatchingLessons($mode, $ccmID);

		foreach ($mappings as $lessonID => $ccmIDs)
		{
			try
			{
				$userLessonTable = new Tables\CourseParticipants;
				$hasUserLesson   = $userLessonTable->load(['userID' => $userID, 'lessonID' => $lessonID]);
			}
			catch (Exception $e)
			{
				return '[]';
			}

			$conditions = [
				'userID'      => $userID,
				'lessonID'    => $lessonID,
				'user_date'   => date('Y-m-d H:i:s'),
				'status'      => (int) Courses::canAcceptParticipant($lessonID),
				'status_date' => date('Y-m-d H:i:s'),
			];

			if ($hasUserLesson)
			{
				$conditions['id'] = $userLessonTable->id;
				$oldCcmIds        = json_decode($userLessonTable->configuration);
				$ccmIDs           = array_merge($ccmIDs, array_diff($oldCcmIds, $ccmIDs));
			}

			$conditions['configuration'] = $ccmIDs;

			if ($userLessonTable->bind($conditions) and $userLessonTable->store())
			{
				$savedCcmIDs = array_merge($savedCcmIDs, $ccmIDs);
			}
		}

		return $savedCcmIDs;
	}
}
