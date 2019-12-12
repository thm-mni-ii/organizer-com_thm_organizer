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

use Organizer\Helpers\Languages;
use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\HTML;

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
		$new   = empty($this->item->id);
		$title = $new ?
			Languages::_('THM_ORGANIZER_COURSE_NEW') : Languages::_('THM_ORGANIZER_COURSE_EDIT');
		HTML::setTitle($title, 'contract-2');
		$toolbar   = Toolbar::getInstance();
		$applyText = $new ? Languages::_('THM_ORGANIZER_CREATE') : Languages::_('THM_ORGANIZER_APPLY');
		$toolbar->appendButton('Standard', 'apply', $applyText, 'courses.apply', false);
		$toolbar->appendButton('Standard', 'save', Languages::_('THM_ORGANIZER_SAVE'), 'courses.save', false);
		$cancelText = $new ? Languages::_('THM_ORGANIZER_CANCEL') : Languages::_('THM_ORGANIZER_CLOSE');
		$toolbar->appendButton('Standard', 'cancel', $cancelText, 'courses.cancel', false);
	}
}