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

	/**
	 * Creates a disclaimer for use in documentation views.
	 *
	 * @return void sets the disclaimer property
	 */
	protected function addDisclaimer()
	{
		$disclaimer = '<div class="disclaimer">';
		$disclaimer .= '<h4>' . Languages::_('THM_ORGANIZER_DISCLAIMER_DATA') . '</h4>';
		$disclaimer .= '<p>' . Languages::_('THM_ORGANIZER_DISCLAIMER_DATA_TEXT') . '</p>';
		$disclaimer .= '</div>';

		$this->disclaimer = $disclaimer;
	}

	protected function addToolBar()
	{
		$new   = empty($this->item->id);
		$title = $new ?
			Languages::_('THM_ORGANIZER_PARTICIPANT_NEW') : Languages::_('THM_ORGANIZER_PARTICIPANT_EDIT');
		HTML::setTitle($title, 'user');
		$toolbar   = Toolbar::getInstance();
		$applyText = $new ? Languages::_('THM_ORGANIZER_CREATE') : Languages::_('THM_ORGANIZER_APPLY');
		$toolbar->appendButton('Standard', 'apply', $applyText, 'participant.apply', false);
		$toolbar->appendButton('Standard', 'save', Languages::_('THM_ORGANIZER_SAVE'), 'participant.save', false);
		$cancelText = $new ?
			Languages::_('THM_ORGANIZER_CANCEL') : Languages::_('THM_ORGANIZER_CLOSE');
		$toolbar->appendButton('Standard', 'cancel', $cancelText, 'participant.cancel', false);
	}

	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (default: null)
	 *
	 * @return void
	 * @throws Exception => unauthorized access
	 */
	public function display($tpl = null)
	{
		if (empty(Factory::getUser()->id))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
		}

		$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		parent::display($tpl);
	}
}
