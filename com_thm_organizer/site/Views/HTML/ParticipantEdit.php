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
	protected function addToolBar()
	{
		$new   = empty($this->item->id);
		$title = $new ?
			Languages::_('ORGANIZER_PARTICIPANT_NEW') : Languages::_('ORGANIZER_PARTICIPANT_EDIT');
		HTML::setTitle($title, 'user');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'save', Languages::_('ORGANIZER_SAVE'), 'participants.save', false);
		$cancelText = $new ?
			Languages::_('ORGANIZER_CANCEL') : Languages::_('ORGANIZER_CLOSE');
		$toolbar->appendButton('Standard', 'cancel', $cancelText, 'participants.cancel', false);
	}
}
