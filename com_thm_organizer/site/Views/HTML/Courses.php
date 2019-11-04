<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers as Helpers;
use Organizer\Helpers\Courses as Helper;
use Organizer\Helpers\Languages as Languages;

/**
 * Class which loads data into the view output context
 */
class Courses extends ListView
{
	private $allowNew = false;

	private $params = null;

	const UNREGISTERED = null, WAIT_LIST = 0;

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->params = Helpers\Input::getParams();

		if ($this->clientContext === self::BACKEND)
		{
			$this->rowStructure = [
				'checkbox' => '',
				'name'     => 'link',
				'persons'  => 'link',
				'groups'   => 'link',
				'rooms'    => 'link',
				'dates'    => 'link'
			];
		}
		else
		{
			$this->rowStructure = [
				'name'               => 'link',
				'dates'              => 'value',
				'courseStatus'       => 'value',
				'registrationStatus' => 'value',
				'tools'              => 'value'
			];
		}
	}

	/**
	 * Adds supplemental information to the display output.
	 *
	 * @return void modifies the object property supplement
	 */
	protected function addSupplement()
	{
		if (empty(Factory::getUser()->id))
		{
			$this->supplement =
				'<div class="tbox-yellow">' . Languages::_('THM_ORGANIZER_COURSE_LOGIN_WARNING') . '</div>';
		}
	}

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$backend      = $this->clientContext === self::BACKEND;
		$resourceName = '';
		if (!$backend)
		{
			if (Helpers\Input::getBool('onlyPrepCourses', false))
			{
				$resourceName .= Languages::_('THM_ORGANIZER_PREP_COURSES');
				if ($campusID = $this->state->get('filter.campusID', 0))
				{
					$resourceName .= ' ' . Helpers\Campuses::getName($campusID);
				}
			}
		}

		Helpers\HTML::setMenuTitle('THM_ORGANIZER_COURSES_TITLE', $resourceName, 'contract-2');

		$toolbar = Toolbar::getInstance();
		if ($backend or $this->allowNew)
		{
			$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'course.add', false);
		}
		if ($backend)
		{
			$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'course.edit', true);
			$toolbar->appendButton(
				'Confirm',
				Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
				'delete',
				Languages::_('THM_ORGANIZER_DELETE'),
				'course.delete',
				true
			);
		}
		else
		{
			$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT_PROFILE'), 'participant.edit',
				false);
		}
	}

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the user may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		if ($this->clientContext == self::FRONTEND)
		{
			return true;
		}

		return (Helpers\Access::isAdmin() or Helper::coordinates());
	}

	/**
	 * Function to get table headers
	 *
	 * @return array including headers
	 */
	public function getHeaders()
	{
		$backend = $this->clientContext === self::BACKEND;

		if ($backend)
		{
			$headers = [
				'checkbox' => '',
				'name'     => Languages::_('THM_ORGANIZER_NAME'),
				'persons'  => Languages::_('THM_ORGANIZER_PERSONS'),
				'groups'   => Languages::_('THM_ORGANIZER_GROUPS'),
				'rooms'    => Languages::_('THM_ORGANIZER_ROOMS'),
				'dates'    => Languages::_('THM_ORGANIZER_DATES')
			];
		}
		else
		{
			$headers = [
				'name'               => Languages::_('THM_ORGANIZER_NAME'),
				'dates'              => Languages::_('THM_ORGANIZER_DATES'),
				'courseStatus'       => Languages::_('THM_ORGANIZER_COURSE_STATE'),
				'registrationStatus' => Languages::_('THM_ORGANIZER_REGISTRATION_STATE'),
				'tools'              => Languages::_('THM_ORGANIZER_ACTIONS')
			];
		}

		return $headers;
	}

	/**
	 * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
	 *
	 * @return void processes the class items property
	 */
	protected function structureItems()
	{
		$backend          = $this->clientContext === self::BACKEND;
		$buttonTemplate   = '<a class="btn" href="XHREFX" target="_blank">XICONXXTEXTX</a>';
		$editIcon         = '<span class="icon-edit"></span>';
		$editLink         = "index.php?option=com_thm_organizer&view=course_edit&id=";
		$itemLink         = "index.php?option=com_thm_organizer&view=course_item&id=";
		$participantsLink = "index.php?option=com_thm_organizer&view=participants&courseID=";
		$participantsIcon = '<span class="icon-users"></span>';
		if (Helpers\Participants::incomplete())
		{
			$registrationIcon = '<span class="icon-user-check"></span>';
			$registrationLink = "index.php?option=com_thm_organizer&view=participant_edit&courseID=";
			$registrationText = Languages::_('THM_ORGANIZER_PROFILE_REGISTER');
		}
		else
		{
			$registrationIcon = '<span class="icon-apply"></span>';
			$registrationLink = "index.php?option=com_thm_organizer&task=participant.register&id=";
			$registrationText = Languages::_('THM_ORGANIZER_REGISTER');
		}
		$rowLink = $backend ? $editLink : $itemLink;
		$userID  = Factory::getUser()->id;

		$this->allowNew  = Helpers\Access::isAdmin();
		$personID        = Helpers\Persons::getIDByUserID();
		$structuredItems = [];

		foreach ($this->items as $course)
		{
			$courseID      = $course->id;
			$course->dates = Helper::getDateDisplay($courseID);
			$groups        = empty($course->groups) ? '' : ": {$course->groups}";
			$course->name  = Helper::getName($courseID) . $groups;
			$index         = "{$course->name}{$course->dates}{$courseID}";

			$courseCoordinator = Helper::coordinates($courseID, $personID);
			$this->allowNew    = ($this->allowNew OR $courseCoordinator);

			if ($backend)
			{
				$course->persons = implode(', ', Helper::getPersons($courseID));
				$course->groups  = implode(', ', Helper::getGroups($courseID));
				$course->rooms   = implode(', ', Helper::getRooms($courseID));
			}
			else
			{
				if (Helper::isExpired($courseID))
				{
					$course->courseStatus = Languages::_('THM_ORGANIZER_EXPIRED');
				}
				elseif (Helper::isOngoing($courseID))
				{
					$course->courseStatus = Languages::_('THM_ORGANIZER_COURSE_ONGOING');
				}
				elseif (Helper::isFull($courseID))
				{
					$course->courseStatus = Languages::_('THM_ORGANIZER_COURSE_FULL');
				}
				else
				{
					$course->courseStatus = Languages::_('THM_ORGANIZER_COURSE_OPEN');
				}

				if ($userID)
				{
					$responsible = (Helper::coordinates($courseID) or Helper::hasResponsibility($courseID));

					if ($responsible)
					{
						$course->registrationStatus = '';

						$editButton = str_replace('XHREFX', $editLink . $courseID, $buttonTemplate);
						$editButton = str_replace('XICONX', $editIcon . $courseID, $editButton);
						$editButton = str_replace('XTEXTX', Languages::_('THM_ORGANIZER_EDIT_COURSE'), $editButton);

						$partsButton = str_replace('XHREFX', $participantsLink . $courseID, $buttonTemplate);
						$partsButton = str_replace('XICONX', $participantsIcon, $partsButton);
						$partsButton = str_replace(
							'XTEXTX',
							Languages::_('THM_ORGANIZER_MANAGE_PARTICIPANTS'),
							$partsButton
						);

						$course->tools = $editButton . $partsButton;
					}
					else
					{
						if ($registrationStatus = Helper::getParticipantState($courseID))
						{
							$course->registrationStatus = '<span class="icon-checkbox-checked"></span>';
							$course->registrationStatus .= Languages::_('THM_ORGANIZER_ACCEPTED');

							$course->tools = 'DEREGISTER BUTTON';
						}
						elseif ($registrationStatus === self::WAIT_LIST)
						{
							$course->registrationStatus = '<span class="icon-checkbox-partial"></span>';
							$course->registrationStatus .= Languages::_('THM_ORGANIZER_WAIT_LIST');

							$course->tools = 'DEREGISTER BUTTON';
						}
						else
						{
							$course->registrationStatus = '<span class="icon-checkbox-unchecked"></span>';
							$course->registrationStatus .= Languages::_('THM_ORGANIZER_COURSE_NOT_REGISTERED');

							$registerButton = str_replace('XHREFX', $registrationLink . $courseID, $buttonTemplate);
							$registerButton = str_replace('XICONX', $registrationIcon, $registerButton);
							$registerButton = str_replace('XTEXTX', $registrationText, $registerButton);
							$course->tools  = $registerButton;
						}
					}
				}
				else
				{
					$course->registrationStatus = '<span class="icon-minus-circle"></span>';
					$course->registrationStatus .= Languages::_('THM_ORGANIZER_NOT_LOGGED_IN');
					$course->tools              = '';
				}

			}

			$structuredItems[$index] = $this->structureItem($index, $course, $rowLink . $courseID);
		}

		ksort($structuredItems);

		$this->items = $structuredItems;
	}
}
