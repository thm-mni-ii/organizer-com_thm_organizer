<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of degrees into the display context.
 */
class Degrees extends ListView
{
	protected $rowStructure = ['checkbox' => '', 'name' => 'link', 'abbreviation' => 'link', 'code' => 'link'];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_DEGREES_TITLE'), 'info-cap');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'degree.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'degree.edit', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('THM_ORGANIZER_DELETE'),
			'degree.delete',
			true
		);
		HTML::setPreferencesButton();
	}

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the use may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		return Access::isAdmin();
	}

	/**
	 * Function to get table headers
	 *
	 * @return array including headers
	 */
	public function getHeaders()
	{
		$ordering            = $this->state->get('list.ordering');
		$direction           = $this->state->get('list.direction');
		$headers             = [];
		$headers['checkbox'] = '';

		$headers['name'] = HTML::sort('NAME', 'name', $direction, $ordering);

		$headers['abbreviation']
			= HTML::sort('ABBREVIATION', 'abbreviation', $direction, $ordering);

		$headers['code'] = HTML::sort('DEGREE_CODE', 'code', $direction, $ordering);

		return $headers;
	}
}
