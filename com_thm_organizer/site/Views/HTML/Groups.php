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

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Can;
use Organizer\Helpers\Grids;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of (scheduled subject) pools into the display context.
 */
class Groups extends ListView
{
	protected $rowStructure = [
		'checkbox' => '',
		'fullName' => 'link',
		'name'     => 'link',
		'grid'     => 'link',
		'untisID'  => 'link'
	];

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_GROUPS'), 'list-2');
		$toolbar = Toolbar::getInstance();
		$toolbar->appendButton('Standard', 'edit', Languages::_('THM_ORGANIZER_EDIT'), 'groups.edit', true);

		$if          = "alert('" . Languages::_('THM_ORGANIZER_LIST_SELECTION_WARNING') . "');";
		$else        = "jQuery('#modal-publishing').modal('show'); return true;";
		$script      = 'onclick="if(document.adminForm.boxchecked.value==0){' . $if . '}else{' . $else . '}"';
		$batchButton = '<button id="group-publishing" data-toggle="modal" class="btn btn-small" ' . $script . '>';

		$title       = Languages::_('THM_ORGANIZER_BATCH');
		$batchButton .= '<span class="icon-stack" title="' . $title . '"></span>' . " $title";

		$batchButton .= '</button>';

		$toolbar->appendButton('Custom', $batchButton, 'batch');

		if (Can::administrate())
		{
			$toolbar->appendButton(
				'Standard',
				'attachment',
				Languages::_('THM_ORGANIZER_MERGE'),
				'groups.mergeView',
				true
			);
			$toolbar->appendButton(
				'Standard',
				'eye-open',
				Languages::_('THM_ORGANIZER_PUBLISH_EXPIRED_TERMS'),
				'groups.publishPast',
				false
			);
		}
	}

	/**
	 * Function determines whether the user may access the view.
	 *
	 * @return bool true if the use may access the view, otherwise false
	 */
	protected function allowAccess()
	{
		return (bool) Can::scheduleTheseDepartments();
	}

	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (default: null)
	 *
	 * @return void
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		// Set batch template path
		$this->batch = ['batch_group_publishing'];

		parent::display($tpl);
	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return void
	 */
	protected function modifyDocument()
	{
		parent::modifyDocument();

		Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/group_publishing.css');
		Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/modal.css');
	}

	/**
	 * Function to set the object's headers property
	 *
	 * @return void sets the object headers property
	 */
	protected function setHeaders()
	{
		$ordering  = $this->state->get('list.ordering');
		$direction = $this->state->get('list.direction');
		$headers   = [
			'checkbox' => HTML::_('grid.checkall'),
			'fullName' => HTML::sort('FULL_NAME', 'gr.fullName', $direction, $ordering),
			'name'     => HTML::sort('SELECT_BOX_DISPLAY', 'gr.name', $direction, $ordering),
			'grid'     => Languages::_('THM_ORGANIZER_GRID'),
			'untisID'  => HTML::sort('UNTIS_ID', 'gr.untisID', $direction, $ordering)
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
		$link            = 'index.php?option=com_thm_organizer&view=group_edit&id=';
		$structuredItems = [];

		foreach ($this->items as $item)
		{
			$item->grid              = Grids::getName($item->gridID);
			$structuredItems[$index] = $this->structureItem($index, $item, $link . $item->id);
			$index++;
		}

		$this->items = $structuredItems;
	}
}
