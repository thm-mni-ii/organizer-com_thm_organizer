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
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads a non-item based resource form (merge) into the display context. Specific resource determined by
 * extending class.
 */
abstract class FormView extends BaseHTMLView
{
	protected $_layout = 'form';

	public $params = null;

	public $form = null;

	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (default: null)
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		$this->form = $this->get('Form');

		// Allows for view specific toolbar handling
		$this->addToolBar();

		if (method_exists($this, 'setSubtitle'))
		{
			$this->setSubtitle();
		}
		if (method_exists($this, 'addSupplement'))
		{
			$this->addSupplement();
		}

		$this->modifyDocument();
		parent::display($tpl);
	}

	/**
	 * Concrete classes are supposed to use this method to add a toolbar.
	 *
	 * @return void  adds toolbar items to the view
	 */
	abstract protected function addToolBar();

	/**
	 * Adds styles and scripts to the document
	 *
	 * @return void  modifies the document
	 */
	protected function modifyDocument()
	{
		parent::modifyDocument();

		HTML::_('behavior.formvalidator');

		$document = Factory::getDocument();
		$document->addScript(Uri::root() . 'components/com_thm_organizer/js/validators.js');
		$document->addScript(Uri::root() . 'components/com_thm_organizer/js/submitButton.js');
		$document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/form.css');
	}
}
