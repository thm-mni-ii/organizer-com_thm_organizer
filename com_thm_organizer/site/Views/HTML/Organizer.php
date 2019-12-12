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

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Can;
use Organizer\Helpers\HTML;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Languages;

/**
 * Class modifies the document for the output of a menu like list of resource management views.
 */
class Organizer extends BaseHTMLView
{
	public $menuItems;

	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (default: null)
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		$this->addMenu();
		$this->modifyDocument();
		$this->addToolBar();

		parent::display($tpl);
	}

	/**
	 * Creates a toolbar
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		HTML::setTitle(Languages::_('THM_ORGANIZER_MAIN'), 'organizer');

		if (Can::administrate())
		{
			$toolbar = Toolbar::getInstance();
			$this->getModel()->showConfigurationMigrationButtons($toolbar);
			$this->getModel()->showScheduleMigrationButton($toolbar);
			HTML::setPreferencesButton();
		}
	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return void
	 */
	protected function modifyDocument()
	{
		parent::modifyDocument();
		Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/organizer.css');
	}
}
