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
 * Class loads persistent information a filtered set of departments into the display context.
 */
class Departments extends ListView
{
	protected $rowStructure = ['checkbox' => '', 'shortName' => 'link', 'name' => 'link'];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_DEPARTMENTS_TITLE'), 'tree-2');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'department.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'department.edit', true);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('THM_ORGANIZER_DELETE'),
			'department.delete',
			true
		);
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
		$ordering             = $this->state->get('list.ordering');
		$direction            = $this->state->get('list.direction');
		$headers              = [];
		$headers['checkbox']  = '';
		$headers['shortName'] = HTML::sort('SHORT_NAME', 'shortName', $direction, $ordering);
		$headers['name']      = HTML::sort('NAME', 'name', $direction, $ordering);

		return $headers;
	}
}
