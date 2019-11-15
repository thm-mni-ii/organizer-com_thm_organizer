<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Organizer\Tables\CourseParticipants;

/**
 * Class provides generalized functions useful for several component files.
 */
class Users
{
	static $user;

	/**
	 * Deletes events from the user's personal schedule
	 *
	 * @return string the deleted calendar configuration map IDs
	 * @throws Exception => invalid request / unauthorized access
	 */
	public static function deleteUserLesson()
	{
		$userID = self::getID();
		if (empty($userID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$ccmID = Input::getInt('ccmID');
		if (empty($ccmID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
		}

		$mode     = Input::getInt('mode', self::BLOCK_MODE);
		$mappings = self::getMatchingLessons($mode, $ccmID);

		$deletedCcmIDs = [];
		foreach ($mappings as $lessonID => $ccmIDs)
		{
			$userLessonTable = new CourseParticipants;

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
	 * Resolves the user id
	 *
	 * @return int the id of the user
	 */
	public static function getID()
	{
		$user = empty(self::$user) ? self::getUser() : self::$user;

		return $user->id;
	}

	/**
	 * Get a user object.
	 *
	 * Returns the global {@link User} object, only creating it if it doesn't already exist.
	 *
	 * @param   integer  $id  The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 *
	 * @return  User object
	 */
	public static function getUser($id = null)
	{
		// A user was specifically requested by id.
		if (!empty($id))
		{
			self::$user = Factory::getUser($id);

			return self::$user;
		}

		// A static user already exists.
		if (self::$user)
		{
			return self::$user;
		}

		$defaultUser    = Factory::getUser();
		$userName       = Input::getString('username');
		$authentication = urldecode(Input::getString('auth', ''));

		// No authentication parameters => use Joomla
		if (empty($userName) or empty($authentication))
		{
			self::$user = $defaultUser;

			return self::$user;
		}

		$requestedUser = Factory::getUser($userName);
		if (empty($requestedUser->id))
		{
			self::$user = $defaultUser;

			return self::$user;
		}

		$authenticates = password_verify($requestedUser->email . $requestedUser->registerDate, $authentication);
		self::$user    = $authenticates ? $requestedUser : $defaultUser;

		return self::$user;
	}

	/**
	 * Saves event instance references in the personal schedule of the user
	 *
	 * @return array saved ccmIDs
	 * @throws Exception => invalid request / unauthorized access
	 */
	public static function saveEvent()
	{
		$userID = Factory::getUser()->id;
		if (empty($userID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
		}

		$ccmID = Input::getInt('ccmID');
		if (empty($ccmID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
		}

		$savedCcmIDs = [];
		$mode        = Input::getInt('mode', self::BLOCK_MODE);
		$mappings    = self::getMatchingLessons($mode, $ccmID);

		foreach ($mappings as $lessonID => $ccmIDs)
		{
			try
			{
				$userLessonTable = new CourseParticipants;
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
