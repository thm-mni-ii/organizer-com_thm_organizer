<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Uri\Uri;
use Organizer\Helpers as Helpers;
use Joomla\CMS\Toolbar\Toolbar;

/**
 * Class loads persistent information about a course into the display context.
 */
class CourseEdit extends EditView
{
	/**
	 * Concrete classes are supposed to use this method to add a toolbar.
	 *
	 * @return void  adds toolbar items to the view
	 */

	protected function addToolBar()
	{
		$courseID = $this->item->id;
		$new      = empty($courseID);
		$title    = Helpers\Languages::_('THM_ORGANIZER_MANAGE_COURSE');
		Helpers\HTML::setTitle($title, 'contract-2');
		$toolbar   = Toolbar::getInstance();
		$applyText = $new ? Helpers\Languages::_('THM_ORGANIZER_CREATE') : Helpers\Languages::_('THM_ORGANIZER_APPLY');
		$toolbar->appendButton('Standard', 'apply', $applyText, 'courses.apply', false);
		$toolbar->appendButton('Standard', 'save', Helpers\Languages::_('THM_ORGANIZER_SAVE'), 'courses.save', false);
		$cancelText = $new ? Helpers\Languages::_('THM_ORGANIZER_CANCEL') : Helpers\Languages::_('THM_ORGANIZER_CLOSE');
		$toolbar->appendButton('Standard', 'cancel', $cancelText, 'courses.cancel', false);

		$baseLink       = Uri::base() . "?option=com_thm_organizer&courseID=$courseID";
		$buttonTemplate = '<a class="btn" href="XHREFX" target="_blank">XICONXXTEXTX</a>';

		$icon = '<span class="icon-users"></span>';
		$link = "$baseLink&view=participants";
		$text = Helpers\Languages::_('THM_ORGANIZER_MANAGE_PARTICIPANTS');

		$button = str_replace('XHREFX', $link, $buttonTemplate);
		$button = str_replace('XICONX', $icon, $button);
		$button = str_replace('XTEXTX', $text, $button);
		$toolbar->appendButton('Custom', $button, 'participants');

		$icon = '<span class="icon-user-check"></span>';
		$link = "$baseLink&view=attendance&format=pdf";
		$text = Helpers\Languages::_('THM_ORGANIZER_PRINT_ATTENDANCE');

		$button = str_replace('XHREFX', $link, $buttonTemplate);
		$button = str_replace('XICONX', $icon, $button);
		$button = str_replace('XTEXTX', $text, $button);
		$toolbar->appendButton('Custom', $button, 'attendance');

		$icon = '<span class="icon-tags"></span>';
		$link = "$baseLink&view=badges&format=pdf";
		$text = Helpers\Languages::_('THM_ORGANIZER_PRINT_BADGES');

		$button = str_replace('XHREFX', $link, $buttonTemplate);
		$button = str_replace('XICONX', $icon, $button);
		$button = str_replace('XTEXTX', $text, $button);
		$toolbar->appendButton('Custom', $button, 'attendance');

		$icon = '<span class="icon-bars"></span>';
		$link = "$baseLink&view=department_participants&format=pdf";
		$text = Helpers\Languages::_('THM_ORGANIZER_PRINT_DEPARTMENT_PARTICIPANTS');

		$button = str_replace('XHREFX', $link, $buttonTemplate);
		$button = str_replace('XICONX', $icon, $button);
		$button = str_replace('XTEXTX', $text, $button);
		$toolbar->appendButton('Custom', $button, 'departmentparticipants');
	}

	/**
	 * Creates a subtitle element from the term name and the start and end dates of the course.
	 *
	 * @return void modifies the course
	 */
	protected function setSubtitle()
	{
		$course = $this->item;
		$name   = Helpers\Courses::getName($course->id);
		$dates  = Helpers\Courses::getDateDisplay($course->id);
		$termID = $course->preparatory ? Helpers\Terms::getNextID($course->termID) : $course->termID;
		$term   = Helpers\Terms::getName($termID);

		$this->subtitle = "<h6 class=\"sub-title\">$name ($course->id)<br>$term - $dates</h6>";
	}
}