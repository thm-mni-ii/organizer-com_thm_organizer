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
use Organizer\Helpers\Can;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of degree programs into the display context.
 */
class Programs extends ListView
{
	protected $rowStructure = [
		'checkbox'    => '',
		'programName' => 'link',
		'degree'      => 'link',
		'version'     => 'link',
		'department'  => 'link'
	];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_PROGRAMS'), 'list');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'program.add', false);
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'program.edit', true);
		$toolbar->appendButton(
			'Standard',
			'upload',
			Languages::_('THM_ORGANIZER_IMPORT_LSF'),
			'program.import',
			true
		);
		$toolbar->appendButton(
			'Standard',
			'loop',
			Languages::_('THM_ORGANIZER_UPDATE_SUBJECTS'),
			'program.update',
			true
		);
		$toolbar->appendButton(
			'Confirm',
			Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
			'delete',
			Languages::_('THM_ORGANIZER_DELETE'),
			'program.delete',
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
		return (bool) Can::documentTheseDepartments();
	}

	/**
	 * Function to set the object's headers property
	 *
	 * @return void sets the object headers property
	 */
	public function setHeaders()
	{
		$ordering  = $this->state->get('list.ordering');
		$direction = $this->state->get('list.direction');
		$headers   = [
			'checkbox'    => '',
			'programName' => HTML::sort('NAME', 'programName', $direction, $ordering),
			'degree'      => HTML::sort('DEGREE', 'degree', $direction, $ordering),
			'version'     => HTML::sort('VERSION', 'version', $direction, $ordering),
			'department'  => HTML::sort('DEPARTMENT', 'department', $direction, $ordering)
		];

		$this->headers = $headers;
	}
}
