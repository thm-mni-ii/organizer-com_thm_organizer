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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Uri\Uri;

/**
 * Class loads the query's results into the display context.
 */
class Search extends BaseHTMLView
{
    public $languageLinks;

    public $languageTag;

    public $query;

    public $results;

    /**
     * loads model data into view context
     *
     * @param string $tpl the name of the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->languageTag   = Languages::getShortTag();
        $this->languageLinks = new LayoutFile('language_links', JPATH_ROOT . '/components/com_thm_organizer/Layouts');
        $this->query         = OrganizerHelper::getInput()->getString('search', '');
        $this->results       = $this->getModel()->getResults();

        $this->modifyDocument();
        parent::display($tpl);
    }

    /**
     * Modifies document variables and adds links to external files
     *
     * @return void
     */
    private function modifyDocument()
    {
        HTML::_('bootstrap.framework');
        HTML::_('bootstrap.tooltip');
        HTML::_('jquery.ui');

        $document = Factory::getDocument();
        $document->setTitle(Languages::_('THM_ORGANIZER_SEARCH'));
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/search.css');
    }
}
