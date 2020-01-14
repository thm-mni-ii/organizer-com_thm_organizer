<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Organizer\Helpers\Input;

/**
 * Class loads the resource form into display context. Specific resource determined by extending class.
 */
abstract class EditView extends FormView
{
	public $item = null;

	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (default: null)
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		$this->item = $this->getModel()->getItem(Input::getSelectedID());
		parent::display($tpl);
	}
}
