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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Class loads filtered events into the display context.
 */
class EventList extends BaseHTMLView
{
	public $form = null;

	public $model;

	public $state;

	/**
	 * Loads persistent data into the view context
	 *
	 * @param   string  $tpl  the name of the template to load
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		$this->model = $this->getModel();
		$layout      = $this->model->params['layout'];
		$this->state = $this->get('State');
		$this->form  = $this->get('Form');
		$this->form->setValue('startDate', null, $this->state->get('startDate'));

		$this->form->setValue('interval', null, $this->state->get('interval'));

		$this->modifyDocument();
		$this->setLayout($layout);
		parent::display($tpl);
	}

	/**
	 * Adds css and javascript files to the document
	 *
	 * @return void  modifies the document
	 */
	protected function modifyDocument()
	{
		parent::modifyDocument();;
		HTML::_('bootstrap.framework');

		Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/event_list.css');
	}
}
