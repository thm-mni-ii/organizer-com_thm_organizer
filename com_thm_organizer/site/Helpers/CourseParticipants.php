<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Provides general functions for course access checks, data retrieval and display.
 */
class CourseParticipants extends ResourceHelper
{
	const UNREGISTERED = null, WAIT_LIST = 0;

	/**
	 * Retrieves the participant's state for the given course
	 *
	 * @param   int  $courseID       the course id
	 * @param   int  $eventID        the id of the specific course event
	 * @param   int  $participantID  the id of the participant
	 *
	 * @return  mixed int if the user has a course participant state, otherwise null
	 */
	public static function getState($courseID, $participantID, $eventID = 0)
	{
		if (empty($courseID) or empty($participantID))
		{
			return self::UNREGISTERED;
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('status')
			->from('#__thm_organizer_course_participants AS cp')
			->where("cp.courseID = $courseID")
			->where("cp.participantID = $participantID");

		if ($eventID)
		{
			$query->innerJoin('#__thm_organizer_units AS u ON u.courseID = cp.courseID')
				->innerJoin('#__thm_organizer_instances AS i ON i.unitID = u.id')
				->innerJoin('#__thm_organizer_instance_participants AS ip ON ip.instanceID = i.id')
				->where("i.eventID = $eventID")
				->where("ip.participantID = $participantID");
		}

		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadResult', self::UNREGISTERED);
	}

	/**
	 * Generates a status text for the course itself.
	 *
	 * @param   int  $courseID       the id of the course
	 * @param   int  $participantID  the id of the participant, defaults to the user id
	 * @param   int  $eventID        the id of the event, if relevant
	 *
	 * @return string the course status text
	 */
	public static function getStatusText($courseID, $participantID = 0, $eventID = 0)
	{
		$participantID = $participantID ? $participantID : Factory::getUser()->id;
		if (Can::manage('course', $courseID) or Courses::isExpired($courseID))
		{
			return '';
		}

		if ($state = self::getState($courseID, $participantID, $eventID))
		{
			return '<span class="icon-checkbox-checked"></span>' . Languages::_('THM_ORGANIZER_ACCEPTED');
		}
		elseif ($state === self::WAIT_LIST)
		{
			return '<span class="icon-checkbox-partial"></span>' . Languages::_('THM_ORGANIZER_WAIT_LIST');
		}

		return '<span class="icon-checkbox-unchecked"></span>' . Languages::_('THM_ORGANIZER_COURSE_NOT_REGISTERED');
	}

	/**
	 * Generates a status text for the course itself.
	 *
	 * @param   int  $courseID       the id of the course
	 * @param   int  $participantID  the id of the participant, defaults to the user id
	 * @param   int  $eventID        the id of the event, if relevant
	 *
	 * @return string the course status text
	 */
	public static function getToolBar($courseID, $participantID = 0, $eventID = 0)
	{
		$baseURL        = Uri::base() . '?option=com_thm_organizer';
		$buttons        = '';
		$buttonTemplate = '<a class="btn" href="XHREFX">XICONXXTEXTX</a>';
		$participantID  = $participantID ? $participantID : Factory::getUser()->id;
		$personID       = Persons::getIDByUserID($participantID);

		if (Can::administrate() or ($personID and Courses::hasResponsibility($courseID, $personID)))
		{
			if (OrganizerHelper::getApplication()->isClient('site'))
			{
				$button  = str_replace('XHREFX', $baseURL . "&view=course_edit&id=$courseID", $buttonTemplate);
				$button  = str_replace('XICONX', '<span class="icon-options"></span>', $button);
				$button  = str_replace('XTEXTX', Languages::_('THM_ORGANIZER_MANAGE_COURSE'), $button);
				$buttons .= $button;
			}

			$button  = str_replace('XHREFX', $baseURL . "&view=course_participants&id=$courseID", $buttonTemplate);
			$button  = str_replace('XICONX', '<span class="icon-users"></span>', $button);
			$button  = str_replace('XTEXTX', Languages::_('THM_ORGANIZER_MANAGE_PARTICIPANTS'), $button);
			$buttons .= $button;
		}
		elseif (!Courses::isExpired($courseID))
		{
			$registrationURL = $baseURL . "&task=participant.register&courseID=$courseID";
			if (self::getState($courseID, $participantID, $eventID) !== self::UNREGISTERED)
			{
				$button  = str_replace('XHREFX', $registrationURL, $buttonTemplate);
				$button  = str_replace('XICONX', '<span class="icon-out-2"></span>', $button);
				$button  = str_replace('XTEXTX', Languages::_('THM_ORGANIZER_DEREGISTER'), $button);
				$buttons .= $button;
			}
			elseif (Participants::incomplete())
			{
				$button  = str_replace('XHREFX', $baseURL . "&view=participant_edit", $buttonTemplate);
				$button  = str_replace('XICONX', '<span class="icon-user-plus"></span>', $button);
				$button  = str_replace('XTEXTX', Languages::_('THM_ORGANIZER_COMPLETE_PROFILE'), $button);
				$buttons .= $button;
			}
			else
			{
				$button  = str_replace('XHREFX', $registrationURL, $buttonTemplate);
				$button  = str_replace('XICONX', '<span class="icon-apply"></span>', $button);
				$button  = str_replace('XTEXTX', Languages::_('THM_ORGANIZER_REGISTER'), $button);
				$buttons .= $button;
			}
		}

		return $buttons;
	}
}
