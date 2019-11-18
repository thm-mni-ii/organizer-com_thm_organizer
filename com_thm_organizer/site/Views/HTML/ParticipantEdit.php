<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads participant information into the display context.
 */
class ParticipantEdit extends EditView
{

	public $item;

	public $form;

	public $course;

	protected function addToolBar()
	{
		$new   = empty($this->item->id);
		$title = $new ?
			Languages::_('THM_ORGANIZER_PARTICIPANT_NEW') : Languages::_('THM_ORGANIZER_PARTICIPANT_EDIT');
		HTML::setTitle($title, 'user');
		$toolbar   = Toolbar::getInstance();
		$applyText = $new ? Languages::_('THM_ORGANIZER_CREATE') : Languages::_('THM_ORGANIZER_APPLY');
		$toolbar->appendButton('Standard', 'save', Languages::_('THM_ORGANIZER_SAVE'), 'participant.save', false);
		$cancelText = $new ?
			Languages::_('THM_ORGANIZER_CANCEL') : Languages::_('THM_ORGANIZER_CLOSE');
		$toolbar->appendButton('Standard', 'cancel', $cancelText, 'participant.cancel', false);
	}
}
