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

use Joomla\CMS\Uri\Uri;
use Organizer\Helpers;
use Organizer\Helpers\Languages;
use Joomla\CMS\Toolbar\Toolbar;

/**
 * Class loads persistent information about a course into the display context.
 */
class CourseEdit extends EditView
{
	protected $_layout = 'tabs';

	/**
	 * Adds a toolbar and title to the view.
	 *
	 * @return void  adds toolbar items to the view
	 */
	protected function addToolBar()
	{
		$courseID = $this->item->id;
		$toolbar  = Toolbar::getInstance();

		if ($courseID)
		{
			Helpers\HTML::setTitle(Languages::_('ORGANIZER_COURSE_EDIT'), 'contract-2');

			$toolbar->appendButton('Standard', 'apply', Languages::_('ORGANIZER_APPLY'), 'courses.apply', false);
			$toolbar->appendButton('Standard', 'save', Languages::_('ORGANIZER_SAVE'), 'courses.save', false);
			$toolbar->appendButton('Standard', 'cancel', Languages::_('ORGANIZER_CLOSE'), 'courses.cancel', false);

			$href = Uri::base() . "?option=com_thm_organizer&view=course_participants&courseID=$courseID";
			$icon = '<span class="icon-users"></span>';
			$text = Languages::_('ORGANIZER_MANAGE_PARTICIPANTS');

			$button = "<a class=\"btn\" href=\"$href\" target=\"_blank\">$icon$text</a>";
			$toolbar->appendButton('Custom', $button, 'participants');
		}
		else
		{
			Helpers\HTML::setTitle(Languages::_('ORGANIZER_COURSE_NEW'), 'contract-2');

			$toolbar->appendButton('Standard', 'apply', Languages::_('ORGANIZER_CREATE'), 'courses.apply', false);
			$toolbar->appendButton('Standard', 'save', Languages::_('ORGANIZER_SAVE'), 'courses.save', false);
			$toolbar->appendButton('Standard', 'cancel', Languages::_('ORGANIZER_CANCEL'), 'courses.cancel', false);
		}
	}

	/**
	 * Creates a subtitle element from the term name and the start and end dates of the course.
	 *
	 * @return void modifies the course
	 */
	protected function setSubtitle()
	{
		$course = $this->item;

		if (empty($course->id))
		{
			$this->subtitle = '';

			return;
		}

		$name   = Helpers\Courses::getName($course->id);
		$dates  = Helpers\Courses::getDateDisplay($course->id);
		$termID = $course->preparatory ? Helpers\Terms::getNextID($course->termID) : $course->termID;
		$term   = Helpers\Terms::getName($termID);

		$this->subtitle = "<h6 class=\"sub-title\">$name ($course->id)<br>$term - $dates</h6>";
	}
}