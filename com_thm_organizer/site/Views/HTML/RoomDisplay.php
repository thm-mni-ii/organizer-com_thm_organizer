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

/**
 * Class loads a room's daily schedule into the display context.
 */
class RoomDisplay extends BaseHTMLView
{
	/**
	 * Loads persistent data into the view context
	 *
	 * @param   string  $tpl  the name of the template to load
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		$this->modifyDocument();
		$model       = $this->getModel();
		$this->model = $model;
		$layout      = $model->params['layout'];
		$this->setLayout($layout);
		parent::display($tpl);
	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return void
	 */
	protected function modifyDocument()
	{
		Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/room_display.css');
	}
}
