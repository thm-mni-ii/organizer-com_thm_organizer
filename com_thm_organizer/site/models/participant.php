<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/course.php';

/**
 * Class provides methods for handling and saving information about participants of preparatory courses
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelParticipant extends JModelLegacy
{
	/**
	 *    Saves data for participants when administrator changes state in manager
	 *
	 * @return bool true on success, false on error
	 */
	public function changeStatus()
	{
		$user       = JFactory::getUser();
		$subjectID  = JFactory::getApplication()->input->get("subjectID");
		$authorized = (THM_OrganizerHelperCourse::teachesCourse($subjectID) OR JFactory::getUser()->authorise('core.admin'));

		if (empty($user->id) OR !$authorized)
		{
			return false;
		}

		$data = JFactory::getApplication()->input->getArray();

		if (empty($data["checked"]))
		{
			return true;
		}

		$return = true;
		foreach ($data["checked"] as $userID)
		{
			$status = $this->changeRegistrationState($userID, $data["lessonID"], $data["actions"]);
			$return = ($return AND $status);
		}

		return $return;
	}

	/**
	 * Saves user information to database
	 *
	 * @param array $data with form data
	 *
	 * @return boolean true on success, false on error
	 */
	public function save($data)
	{
		if (empty($data))
		{
			return false;
		}

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_thm_organizer/tables');
		$table = JTable::getInstance('user_data', 'THM_OrganizerTable');

		if (empty($table))
		{
			return false;
		}

		$table->load($data["id"]);

		return $table->save($data);
	}
	/**
	 * Signs User in or out of a specific course
	 *
	 * @param array  $data     data from form
	 * @param string $action   either sign or unSign
	 * @param int    $lessonID id of lesson
	 *
	 * @return boolean true on success, false on error
	 */
	public function register($data = [], $action = '', $lessonID = 0)
	{
		if (empty($data) OR empty($action) OR empty($lessonID))
		{
			return false;
		}

		$lang       = THM_OrganizerHelperLanguage::getLanguage();
		$courseFull = THM_OrganizerHelperCourse::isCourseFull($lessonID) ? 0 : 1;
		$query      = $this->_db->getQuery(true);

		switch ($action)
		{
			case REGISTER:

				$status = $courseFull;

				$data = [
					"lessonID"    => $lessonID,
					"userID"      => $data["userID"],
					"status"      => $status,
					"user_date"   => date('Y-m-d H:i:s'),
					"status_date" => date('Y-m-d H:i:s')
				];

				JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_thm_organizer/tables');
				$table = JTable::getInstance('user_lessons', 'THM_OrganizerTable');

				$return = $table->save($data, '', ['order', 'configuration']);

				break;
			case DEREGISTER:

				$status = 2;

				$query->delete("#__thm_organizer_user_lessons");
				$query->where("lessonID = '$lessonID' and userID = '{$data["userID"]}'");

				$this->_db->setQuery($query);

				try
				{
					$return = $this->_db->execute();
				}
				catch (Exception $exc)
				{
					JFactory::getApplication()->enqueueMessage($lang->_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

					return false;
				}

				break;
			default:
				return false;
		}

		if (!empty($return))
		{
			JModelLegacy::getInstance("mailer", "THM_OrganizerModel")->notifyParticipant($data['userID'], $status, $lessonID);

			return true;
		}

		return false;
	}

	/**
	 * Might move users from state waiting to registered
	 *
	 * @param int $lessonID lesson id of lesson where participants have to be moved up
	 *
	 * @return void
	 */
	public function moveUpWaitingUsers($lessonID)
	{
		$courseFull = THM_OrganizerHelperCourse::isCourseFull($lessonID);
		$lang       = THM_OrganizerHelperLanguage::getLanguage();

		if (!$courseFull)
		{
			$query = $this->_db->getQuery(true);

			$query->select('userID');
			$query->from('#__thm_organizer_user_lessons');
			$query->where("lessonID = '$lessonID' and status = '0'");
			$query->order('status_date, user_date');

			$this->_db->setQuery($query);

			try
			{
				$nextUser = $this->_db->loadAssoc();
			}
			catch (Exception $exc)
			{
				JFactory::getApplication()->enqueueMessage($lang->_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

				return;
			}

			if (!empty($nextUser))
			{
				$this->changeRegistrationState($nextUser["userID"], $lessonID, 1);
			}
		}
	}

	/**
	 * Change a students state of registration to either waitlist, registered or delete the entry
	 *
	 * @param int     $userID        id of user to be changed
	 * @param int     $lessonID      id of lesson the user is signed in
	 * @param int     $status        0 for waitlist, 1 for registered and 2 for deletion
	 * @param boolean $moveAndNotify Should be checked whether participants can move from waiting to free places
	 *
	 * @return bool true if change of state was successful, false otherwise
	 */
	public function changeRegistrationState($userID, $lessonID, $status, $moveAndNotify = true)
	{
		if (empty($userID) OR empty($lessonID))
		{
			return false;
		}

		$lang  = THM_OrganizerHelperLanguage::getLanguage();
		$query = $this->_db->getQuery(true);

		switch ($status)
		{
			case 0:
			case 1:

				$query->update('#__thm_organizer_user_lessons');
				$query->set("status = '$status'");
				$query->set("status_date = '" . date('Y-m-d H:i:s') . "'");
				$query->where("userID = '$userID'");
				$query->where("lessonID = '$lessonID'");

				break;
			case 2:

				$query->delete("#__thm_organizer_user_lessons");
				$query->where("userID = '$userID'");
				$query->where("lessonID = '$lessonID'");

				break;
			default:
				return false;
		}

		$this->_db->setQuery($query);

		try
		{
			$return = $this->_db->execute();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage($lang->_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return false;
		}

		if (!$moveAndNotify)
		{
			return true;
		}

		if (!empty($return))
		{
			JModelLegacy::getInstance("mailer", "THM_OrganizerModel")->notifyParticipant($userID, $status, $lessonID);
		}

		$this->moveUpWaitingUsers($lessonID);

		return true;
	}

	/**
	 * Clears all participants from a specific course
	 *
	 * @param int $lessonID ID of lesson
	 *
	 * @return bool true if successful, false otherwise
	 */
	public function clearParticipants($lessonID)
	{
		$user  = JFactory::getUser();
		$admin = $user->authorise('core.admin');

		if (!$admin)
		{
			return false;
		}

		$participants = THM_OrganizerHelperCourse::getRegisteredStudents($lessonID, 2);
		$return       = true;

		foreach ($participants as $participant)
		{
			$success = $this->changeRegistrationState($participant["userID"], $lessonID, 2, false);
			$return  = ($return AND $success);
		}

		return $return;
	}
}