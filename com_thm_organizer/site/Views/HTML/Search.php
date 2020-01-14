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
 * Class loads the query's results into the display context.
 */
class Search extends BaseHTMLView
{
	public $query;

	public $results;

	/**
	 * loads model data into view context
	 *
	 * @param   string  $tpl  the name of the template to be used
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		$this->tag = Languages::getTag();
		// Use language_selection layout
		$this->query   = OrganizerHelper::getInput()->getString('search', '');
		$this->results = $this->getModel()->getResults();

		$this->modifyDocument();
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

		$document = Factory::getDocument();
		$document->setTitle(Languages::_('THM_ORGANIZER_SEARCH'));
		$document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/search.css');
	}
}
