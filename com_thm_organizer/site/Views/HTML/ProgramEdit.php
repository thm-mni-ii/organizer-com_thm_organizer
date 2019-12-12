<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads the (degree) program form into display context.
 */
class ProgramEdit extends EditView
{
	protected $_layout = 'tabs';

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$new   = empty($this->item->id);
		$title = $new ?
			Languages::_('THM_ORGANIZER_PROGRAM_NEW') : Languages::_('THM_ORGANIZER_PROGRAM_EDIT');
		HTML::setTitle($title, 'list');
		$toolbar   = Toolbar::getInstance();
		$applyText = $new ? Languages::_('THM_ORGANIZER_CREATE') : Languages::_('THM_ORGANIZER_APPLY');
		$toolbar->appendButton('Standard', 'apply', $applyText, 'programs.apply', false);
		$toolbar->appendButton('Standard', 'save', Languages::_('THM_ORGANIZER_SAVE'), 'programs.save', false);
		$toolbar->appendButton(
			'Standard',
			'save-new',
			Languages::_('THM_ORGANIZER_SAVE2NEW'),
			'programs.save2new',
			false
		);
		if (!$new)
		{
			$toolbar->appendButton(
				'Standard',
				'save-copy',
				Languages::_('THM_ORGANIZER_SAVE2COPY'),
				'programs.save2copy',
				false
			);

			$poolLink = 'index.php?option=com_thm_organizer&view=pool_selection&tmpl=component';
			$toolbar->appendButton('Popup', 'list', Languages::_('THM_ORGANIZER_ADD_POOL'), $poolLink);
		}
		$cancelText = $new ? Languages::_('THM_ORGANIZER_CANCEL') : Languages::_('THM_ORGANIZER_CLOSE');
		$toolbar->appendButton('Standard', 'cancel', $cancelText, 'programs.cancel', false);
	}

	/**
	 * Adds styles and scripts to the document
	 *
	 * @return void  modifies the document
	 */
	protected function modifyDocument()
	{
		parent::modifyDocument();

		Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/mappings.css');
	}
}
