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
 * Class provides methods sending mails
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelMailer extends JModelLegacy
{
	/**
	 * Sends a circular mail to all course participants
	 *
	 * @param array $data            form data
	 * @param bool  $includeWaitList send mail also to everyone on the waitlist
	 *
	 * @return bool true on success, false on error
	 */
	public function circular($data, $includeWaitList = false)
	{
		if (empty($data["sender"]) OR empty($data["text"]))
		{
			return false;
		}

		$input     = JFactory::getApplication()->input;
		$lessonID  = $input->get("lessonID", 0);
		$subjectID = $input->get("subjectID", 0);

		$userAuth = (THM_OrganizerHelperCourse::teachesCourse($subjectID) OR JFactory::getUser()->authorise('core.admin'));

		if (empty($lessonID) OR empty($userAuth))
		{
			return false;
		}

		$mailer = JFactory::getMailer();
		$sender = JFactory::getUser(JComponentHelper::getParams('com_thm_organizer')->get('mailSender'));

		if (empty($sender->id))
		{
			return false;
		}

		$senderInfo = [$sender->email, $sender->name];

		$mailer->setSender($senderInfo);
		$mailer->setSubject($data["subject"]);

		$recipients = THM_OrganizerHelperCourse::getFullParticipantData($lessonID, $includeWaitList);

		if (empty($recipients))
		{
			return false;
		}

		foreach ($recipients as $recipient)
		{
			$mailer->addRecipient($recipient["email"]);
		}

		$mailer->setBody($data["text"]);
		$send = $mailer->Send();

		if (!$send)
		{
			JFactory::getApplication()->enqueueMessage(
				THM_OrganizerHelperLanguage::getLanguage()->_("COM_THM_ORGANIZER_MESSAGE_MAIL_SEND_SUCCESS"), 'error');

			return false;
		}

		return false;
	}


	/**
	 * Notify user if registration state was changed
	 *
	 * @param int     $userID   id of User to be notified
	 * @param int     $status   of registration for user (-1 error, 0 waitlist, 1 successfully registered, 2 delete)
	 * @param int     $lessonID id of lesson the user signed into
	 * @param boolean $moveUp   Should be checked whether participants can move from waiting to free places
	 *
	 * @return boolean true on success, false on error
	 */
	public function notifyParticipant($userID, $status, $lessonID = 0)
	{
		$input      = JFactory::getApplication()->input;
		$user       = JFactory::getUser($userID);
		$userParams = json_decode($user->params, true);

		if (!empty($userParams["language"]))
		{
			$input->set('languageTag', explode("-", $userParams["language"])[0]);
		}
		else
		{
			switch (THM_OrganizerHelperCourse::getCourse($lessonID)["instructionLanguage"])
			{
				case "D":
					$tag = 'de';
					break;
				case "E":
					$tag = 'en';
					break;
				default:
					return false;
			}

			$input->set('languageTag', $tag);
		}

		$course = THM_OrganizerHelperCourse::getCourse($lessonID);
		$dates  = THM_OrganizerHelperCourse::getDates($lessonID);

		if (empty($course) OR empty($dates))
		{
			return false;
		}

		$lang   = THM_OrganizerHelperLanguage::getLanguage();
		$mailer = JFactory::getMailer();

		$sender     = JFactory::getUser(JComponentHelper::getParams('com_thm_organizer')->get('mailSender'));
		$dateFormat = JComponentHelper::getParams('com_thm_organizer')->get('dateFormat', 'd.m.Y');

		if (empty($sender->id))
		{
			return false;
		}

		$senderInfo = [$sender->email, $sender->name];

		$mailer->setSender($senderInfo);
		$mailer->addRecipient($user->email);
		$mailer->setSubject($course["name"]);

		$start = JHtml::_('date', $dates[0]["schedule_date"], $dateFormat);
		$end   = JHtml::_('date', end($dates)["schedule_date"], $dateFormat);

		$body = sprintf($lang->_("COM_THM_ORGANIZER_CIRCULAR_BODY") . "\n", $course["name"], $start, $end);

		switch ($status)
		{
			case 0:
				$body .= THM_OrganizerHelperLanguage::sprintf("COM_THM_ORGANIZER_PREP_COURSE_MAIL_WAIT_LIST", $course["name"]) . "\n";
				break;
			case 1:
				$body .= THM_OrganizerHelperLanguage::sprintf("COM_THM_ORGANIZER_PREP_COURSE_MAIL_REGISTERED", $course["name"]) . "\n";
				break;
			case 2:
				$body .= THM_OrganizerHelperLanguage::sprintf("COM_THM_ORGANIZER_PREP_COURSE_MAIL_DELETED", $course["name"]) . "\n";
				break;
			default:
				return false;
		}

		$mailer->setBody($body);
		$send = $mailer->Send();

		if (!$send)
		{
			JFactory::getApplication()->enqueueMessage("Beim Senden einer Email an {$user->email} ist ein Fehler aufgetreten.", 'error');

			return false;
		}

		return true;
	}
}