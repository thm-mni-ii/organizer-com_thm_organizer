<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Organizer\Helpers\Courses;
use Organizer\Helpers\Terms;

/**
 * Class loads the subject into the display context.
 */
class CourseItem extends ItemView
{
	const ACCEPTED = 1;
	const EXPIRED = -1;
	const ONGOING = 1;
	const PENDING = 0;
	const UNREGISTERED = null;

	protected function addSupplement()
	{
		$course = $this->item;
		if ($course['courseStatus'] === self::EXPIRED)
		{
			$color = '';
		}
		elseif ($course['registrationStatus'] === self::ACCEPTED)
		{
			$color = 'green';
		}
		elseif ($course['courseStatus'] === self::ONGOING)
		{
			$color = 'red';
		}
		elseif ($course['registrationStatus'] === self::PENDING)
		{
			$color = 'blue';
		}
		else
		{
			$color = 'yellow';
		}

		$text = '<div class="tbox-' . $color . '">';

		$texts = [];
		if ($course['courseStatus'] === self::EXPIRED or $course['courseStatus'] === self::ONGOING)
		{
			$texts[] = $course['courseText'];
		}
		elseif ($course['courseStatus'] !== self::EXPIRED)
		{
			$texts[] = $course['registrationText'];
			if (!$course['courseStatus'] === self::ONGOING)
			{
				$texts[] = $course['registrationAllowed'];
			}
			if ($course['registrationStatus'] === self::UNREGISTERED)
			{
				$texts[] = $course['registrationType'];
			}
		}

		$text .= implode(' ', $texts);
		$text .= '</div>';

		$this->supplement = $text;
	}

	/**
	 * Creates a subtitle element from the term name and the start and end dates of the course.
	 *
	 * @return void modifies the course
	 */
	protected function setSubtitle()
	{
		$dates  = Courses::getDateDisplay($this->item['id']);
		$termID = $this->item['preparatory'] ? Terms::getNextID($this->item['termID']) : $this->item['termID'];
		$term   = Terms::getName($termID);

		$this->subtitle = "<h6 class=\"sub-title\">$term $dates</h6>";
	}
}
