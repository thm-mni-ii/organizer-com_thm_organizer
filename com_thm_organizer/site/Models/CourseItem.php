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
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Organizer\Helpers as Helpers;
use Organizer\Helpers\Courses;
use Organizer\Helpers\Languages;
use Organizer\Tables\Courses as CoursesTable;

/**
 * Class which retrieves subject information for a detailed display of subject attributes.
 */
class CourseItem extends ItemModel
{
	const EXPIRED = -1, PLANNED = 0, ONGOING = 1, UNREGISTERED = null;

	/**
	 * Provides a strict access check which can be overwritten by extending classes.
	 *
	 * @return bool  true if the user can access the view, otherwise false
	 */
	protected function allowView()
	{
		return true;
	}

	/**
	 * Loads subject information from the database
	 *
	 * @return array  subject data on success, otherwise empty
	 * @throws Exception
	 */
	public function getItem()
	{
		$allowView = $this->allowView();
		if (!$allowView)
		{
			throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
		}

		$courseID = Helpers\Input::getID();
		if (empty($courseID))
		{
			return [];
		}

		$courseTable = new CoursesTable;
		if (!$courseTable->load($courseID))
		{
			return [];
		}

		$campusID = $courseTable->campusID;
		$course   = $this->getStructure();
		$tag      = Languages::getTag();

		$course['campus']['value']  = Helpers\Campuses::getPin($campusID) . ' ' . Helpers\Campuses::getName($campusID);
		$course['deadline']         = $courseTable->deadline;
		$course['id']               = $courseID;
		$course['name']['value']    = $courseTable->{"name_$tag"};
		$course['registrationType'] = $courseTable->registrationType;
		$course['termID']           = $courseTable->termID;

		$this->setRegistrationTexts($course);
		$this->setEvents($course);


		/*$courseData = [
			'description'     => $courseTable->{"description_$tag"},
			'fee'             => $courseTable->fee,
			'groups'          => $courseTable->groups,
			'maxParticipants' => $courseTable->maxParticipants,
		];*/

		return $course;
	}

	/**
	 * Creates a template for course attributes
	 *
	 * @return array the course template
	 */
	private function getStructure()
	{
		$option   = 'THM_ORGANIZER_';
		$template = [
			'id'                  => 0,
			'name'                => ['label' => Languages::_($option . 'NAME'), 'type' => 'text', 'value' => ''],
			'campus'              => ['label' => Languages::_($option . 'CAMPUS'), 'type' => 'text', 'value' => ''],
			'speakers'            => ['label' => Languages::_($option . 'SPEAKERS'), 'type' => 'list', 'value' => []],
			'teachers'            => ['label' => Languages::_($option . 'TEACHERS'), 'type' => 'list', 'value' => []],
			'tutors'              => ['label' => Languages::_($option . 'TUTORS'), 'type' => 'list', 'value' => []],
			'description'         => [
				'label' => Languages::_($option . 'SHORT_DESCRIPTION'),
				'type'  => 'text',
				'value' => ''
			],
			'content'             => ['label' => Languages::_($option . 'CONTENT'), 'type' => 'text', 'value' => ''],
			'organization'        => [
				'label' => Languages::_($option . 'COURSE_ORGANIZATION'),
				'type'  => 'text',
				'value' => ''
			],
			'pretests'            => ['label' => Languages::_($option . 'PRETESTS'), 'type' => 'text', 'value' => ''],
			'courseContact'       => [
				'label' => Languages::_($option . 'COURSE_CONTACTS'),
				'type'  => 'text',
				'value' => ''
			],
			'contact'             => ['label' => Languages::_($option . 'CONTACTS'), 'type' => 'text', 'value' => ''],
			'courseStatus'        => null,
			'courseText'          => null,
			'deadline'            => null,
			'events'              => [],
			'preparatory'         => false,
			'registrationStatus'  => null,
			'registrationAllowed' => null,
			'registrationText'    => null,
			'registrationType'    => null,
			'termID'              => null,
		];

		return $template;
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
		return new CoursesTable;
	}

	/**
	 * Sets event information for the course.
	 *
	 * @param   array &$course  the course to be modified
	 *
	 * @return void modifies the course
	 */
	private function setEvents(&$course)
	{
		// If the course has its own name, do not create it dynamically
		$setName = empty($course['name']['value']);

		$events = Courses::getEvents($course['id']);
		foreach ($events as $key => $attributes)
		{
			$course['preparatory'] = ($course['preparatory'] or $attributes['preparatory']);
			foreach ($attributes as $name => $value)
			{
				if ($name == 'id')
				{
					continue;
				}

				if ($name == 'name')
				{
					if (!$setName)
					{
						continue;
					}

					if ($course['name']['value'] and strpos($course['name']['value'], $value) === false)
					{
						$course['name']['value'] .= " / $value";
					}
					elseif (empty($course['name']['value']))
					{
						$course['name']['value'] .= $value;
					}
					continue;
				}

				if ($name == 'preparatory')
				{
					unset($events[$key][$name]);
					continue;
				}

				if ($course[$name]['value'] === $value)
				{
					continue;
				}
				elseif (is_string($value) and $course[$name]['value'] === '')
				{
					$course[$name]['value'] = $value;
					continue;
				}
				elseif (is_array($value) and $course[$name]['value'] === [])
				{
					$course[$name]['value'] = $value;
					continue;
				}
				else
				{
					$course[$name]['value'] = null;
					continue;
				}
			}
		}

		foreach ($events as $attributes)
		{
			foreach ($attributes as $name => $value)
			{
				if ($name === 'id')
				{
					continue;
				}

				if ($name === 'name' and $course[$name]['value'] !== $value)
				{
					continue;
				}

				if ($course[$name]['value'] or empty($value))
				{
					unset($attributes[$name]);
					continue;
				}
				else
				{
					unset($course[$name]);
				}
			}

			$event = $this->getStructure();
			foreach (array_keys($event) as $attribute)
			{

				// Course relevant attribute, attribute with the same attribute for all events, attribute with no value
				if (empty($attributes[$attribute]))
				{
					unset($event[$attribute]);
					continue;
				}


				if (is_array($event[$attribute]))
				{
					$event[$attribute]['value'] = $attributes[$attribute];
					continue;
				}
				else
				{
					$event[$attribute] = $attributes[$attribute];
					continue;
				}
			}
			$course['events'][] = $attributes;
		}

		// If there is only one event there will be no event display and only one register/deregister button.
		if (count($course['events']) === 1)
		{
			$course['events'] = [];
		}
	}

	/**
	 * Sets texts pertaining to the registration process.
	 *
	 * @param $course
	 */
	private function setRegistrationTexts(&$course)
	{
		$courseID = $course['id'];
		$dates    = Courses::getDates($courseID);
		$deadline = date('Y-m-d', strtotime("{$dates['startDate']} - {$course['deadline']} days"));
		$option   = 'THM_ORGANIZER_';

		if (Courses::isExpired($courseID))
		{
			$course['courseStatus'] = self::EXPIRED;
			$course['courseText']   = Languages::_($option . 'COURSE_EXPIRED');

			return;
		}

		$course['courseStatus'] = Courses::isOngoing($courseID) ? self::ONGOING : self::PLANNED;
		$full                   = Courses::isFull($courseID);
		$userID                 = Factory::getUser()->id;
		if ($userID)
		{
			$course['registrationStatus'] = Helpers\CourseParticipants::getState($courseID, 0, $userID);
			if ($course['registrationStatus'] !== self::UNREGISTERED)
			{
				$course['registrationText'] = $course['registrationStatus'] ?
					Languages::_($option . 'REGISTRATION_REGISTERED') : Languages::_($option . 'REGISTRATION_WAIT');
			}
			else
			{
				$course['registrationText'] = Languages::_($option . 'REGISTRATION_NONE');
			}
		}
		else
		{
			$course['registrationStatus'] = self::UNREGISTERED;
			$course['registrationText']   = Languages::_($option . 'COURSE_LOGIN_WARNING');
		}

		if ($course['courseStatus'] or $deadline <= date('Y-m-d'))
		{
			if ($course['courseStatus'])
			{
				$course['courseText'] = Languages::_($option . 'COURSE_ONGOING');
			}
			if ($course['registrationStatus'] === self::UNREGISTERED)
			{
				$course['registrationText'] = Languages::_($option . 'DEADLINE_EXPIRED');
				if (!$full)
				{
					$course['registrationType'] = Languages::_($option . 'REGISTRATION_IN_PERSON');
				}
			}

			return;
		}

		if ($course['registrationStatus'] === self::UNREGISTERED)
		{
			$deadline = sprintf(Languages::_($option . 'DEADLINE_TEXT'), Helpers\Dates::formatDate($deadline));

			$course['deadline'] = sprintf(Helpers\Languages::_('THM_ORGANIZER_REGISTRATION_DEADLINE'), $deadline);

			$course['registrationAllowed'] = $full ?
				Languages::_($option . 'COURSE_FULL') : Languages::_($option . 'COURSE_OPEN');

			$course['registrationType'] = $course['registrationType'] ?
				Languages::_($option . 'REGISTRATION_MANUAL') : Languages::_($option . 'REGISTRATION_FIFO');
		}
	}
}
