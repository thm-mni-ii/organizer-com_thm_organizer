<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Can;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads subject information into the display context.
 */
class SubjectSelection extends ListView
{
	protected $_layout = 'list_modal';

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'new', Languages::_('ORGANIZER_ADD'), 'pools.addSubject', true);
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
	 * Modifies document variables and adds links to external files
	 *
	 * @return void
	 */
	protected function modifyDocument()
	{
		parent::modifyDocument();

		Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/modal.css');
	}

	/**
	 * Function to set the object's headers property
	 *
	 * @return void sets the object headers property
	 */
	protected function setHeaders()
	{
		$direction = $this->state->get('list.direction');
		$ordering  = $this->state->get('list.ordering');
		$headers   = [
			'checkbox' => HTML::_('grid.checkall'),
			'name'     => HTML::sort('NAME', 'name', $direction, $ordering),
			'program'  => Languages::_('ORGANIZER_PROGRAMS')
		];

		$this->headers = $headers;
	}

	/**
	 * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
	 *
	 * @return void processes the class items property
	 */
	protected function structureItems()
	{
		$index           = 0;
		$structuredItems = [];

		foreach ($this->items as $subject)
		{
			if (!Can::document('subject', $subject->id))
			{
				continue;
			}

			$name = $subject->name;
			$name .= empty($subject->code) ? '' : " - $subject->code";

			$structuredItems[$index]             = [];
			$structuredItems[$index]['checkbox'] = HTML::_('grid.id', $index, $subject->id);
			$structuredItems[$index]['name']     = $name;
			$structuredItems[$index]['programs'] = $name;

			$index++;
		}

		$this->items = $structuredItems;
	}
}
