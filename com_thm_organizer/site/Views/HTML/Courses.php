<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers;
use Organizer\Helpers\Courses as Helper;
use Organizer\Helpers\Languages as Languages;

/**
 * Class which loads data into the view output context
 */
class Courses extends ListView
{
	private $allowNew = false;

	private $params = null;

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->params = Helpers\Input::getParams();
		$userID       = Factory::getUser()->id;

		$structure = ['id' => 'link', 'name' => 'link', 'dates' => 'value', 'courseStatus' => 'value'];

		if ($this->clientContext === self::BACKEND)
		{
			$structure = ['checkbox' => ''] + $structure;
			$structure += ['persons' => 'link', 'groups' => 'link'];
		}

		if ($userID)
		{
			$structure ['userContent'] = 'value';
		}

		$this->rowStructure = $structure;
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
				'<div class="tbox-yellow">' . Languages::_('ORGANIZER_COURSE_LOGIN_WARNING') . '</div>';
		}
	}

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$frontend     = $this->clientContext !== self::BACKEND;
		$resourceName = '';
		if ($frontend)
		{
			if (Helpers\Input::getBool('onlyPrepCourses', false))
			{
				$resourceName .= Languages::_('ORGANIZER_PREP_COURSES');
				if ($campusID = $this->state->get('filter.campusID', 0))
				{
					$resourceName .= ' ' . Helpers\Campuses::getName($campusID);
				}
			}
		}

		Helpers\HTML::setMenuTitle('ORGANIZER_COURSES', $resourceName, 'contract-2');

		if (Factory::getUser()->id)
		{
			$toolbar = Toolbar::getInstance();
			if ($frontend)
			{
				$buttonText = Helpers\Participants::exists() ?
					Languages::_('ORGANIZER_PROFILE_EDIT') : Languages::_('ORGANIZER_PROFILE_NEW');
				$toolbar->appendButton(
					'Standard',
					'vcard',
					$buttonText,
					'participants.edit',
					false
				);
			}

			if (Helper::coordinates())
			{
				$toolbar->appendButton('Standard', 'new', Languages::_('ORGANIZER_ADD'), 'courses.add', false);
			}

			if (!$frontend)
			{
				$toolbar->appendButton('Standard', 'edit', Languages::_('ORGANIZER_EDIT'), 'courses.edit', true);
				$toolbar->appendButton(
					'Confirm',
					Languages::_('ORGANIZER_DELETE_CONFIRM'),
					'delete',
					Languages::_('ORGANIZER_DELETE'),
					'courses.delete',
					true
				);
			}

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

		return (Helpers\Can::administrate() or Helper::coordinates());
	}

	/**
	 * Function to set the object's headers property
	 *
	 * @return void sets the object headers property
	 */
	public function setHeaders()
	{
		$backend = $this->clientContext === self::BACKEND;
		$userID  = Factory::getUser()->id;

		$headers = [
			'id'           => '#',
			'name'         => Languages::_('ORGANIZER_NAME'),
			'dates'        => Languages::_('ORGANIZER_DATES'),
			'courseStatus' => Languages::_('ORGANIZER_COURSE_STATUS')
		];

		if ($backend)
		{
			$headers = ['checkbox' => ''] + $headers;
			$headers += [
				'persons' => Languages::_('ORGANIZER_PERSONS'),
				'groups'  => Languages::_('ORGANIZER_GROUPS')
			];
		}

		if ($userID)
		{
			$headers ['userContent'] = '';
		}

		$this->headers = $headers;
	}

	/**
	 * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
	 *
	 * @return void processes the class items property
	 */
	protected function structureItems()
	{
		$backend = $this->clientContext === self::BACKEND;
		$URL     = Uri::base() . '?option=com_thm_organizer';
		$URL     .= $backend ? '&view=course_edit&id=' : '&view=course_item&id=';
		$userID  = Factory::getUser()->id;

		$this->allowNew  = Helper::coordinates();
		$structuredItems = [];

		foreach ($this->items as $course)
		{
			$courseID             = $course->id;
			$course->dates        = Helper::getDateDisplay($courseID);
			$course->name         = Helper::getNames($courseID);
			$index                = "{$course->name}{$course->dates}{$courseID}";
			$course->courseStatus = Helper::getStatusText($courseID);

			if ($backend)
			{
				$course->persons = implode(', ', Helper::getPersons($courseID));
				$course->groups  = implode(', ', Helper::getGroups($courseID));
			}

			if ($userID)
			{
				$course->userContent = $this->getUserContent($courseID);
			}

			$structuredItems[$index] = $this->structureItem($index, $course, $URL . $courseID);
		}

		ksort($structuredItems);

		$this->items = $structuredItems;
	}

	/**
	 * Generates content for individual courses based on the user's relation to it.
	 *
	 * @param   int  $courseID  the id of the course
	 *
	 * @return string the user specific HTML to display
	 */
	private function getUserContent($courseID)
	{
		$participantID = Factory::getUser()->id;
		$personID      = Helpers\Persons::getIDByUserID($participantID);
		if (Helpers\Can::administrate() or ($personID and Helper::hasResponsibility($courseID, $personID)))
		{
			$baseURL  = Uri::base() . '?option=com_thm_organizer';
			$buttons  = '';
			$template = '<a class="btn" href="XHREFX">XICONXXTEXTX</a>';
			if ($this->clientContext === self::FRONTEND)
			{
				$button  = str_replace('XHREFX', $baseURL . "&view=course_edit&id=$courseID", $template);
				$button  = str_replace('XICONX', '<span class="icon-options"></span>', $button);
				$button  = str_replace('XTEXTX', Languages::_('ORGANIZER_COURSE_MANAGE'), $button);
				$buttons .= $button;
			}

			$button  = str_replace('XHREFX', $baseURL . "&view=course_participants&id=$courseID", $template);
			$button  = str_replace('XICONX', '<span class="icon-users"></span>', $button);
			$button  = str_replace('XTEXTX', Languages::_('ORGANIZER_MANAGE_PARTICIPANTS'), $button);
			$buttons .= $button;

			return $buttons;
		}

		if (Helper::isExpired($courseID))
		{
			return '';
		}

		if ($state = Helpers\CourseParticipants::getState($courseID, $participantID))
		{
			return '<span class="icon-checkbox-checked"></span>' . Languages::_('ORGANIZER_ACCEPTED');
		}

		if ($state === self::WAIT_LIST)
		{
			return '<span class="icon-checkbox-partial"></span>' . Languages::_('ORGANIZER_WAIT_LIST');
		}

		return '<span class="icon-checkbox-unchecked"></span>' . Languages::_('ORGANIZER_COURSE_NOT_REGISTERED');
	}
}
