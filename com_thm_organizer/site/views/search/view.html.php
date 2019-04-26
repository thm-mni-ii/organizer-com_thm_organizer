<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;
define('ALL', 0);
define('DEPARTMENTS', 1);
define('POOLS', 2);
define('PROGRAMS', 3);
define('ROOMS', 4);
define('SUBJECTS', 5);
define('TEACHERS', 6);

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/planning_periods.php';

use Joomla\CMS\Uri\Uri;
use THM_OrganizerHelperHTML as HTML;

/**
 * Class loads the query's results into the display context.
 */
class THM_OrganizerViewSearch extends \Joomla\CMS\MVC\View\HtmlView
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
        $this->languageLinks = new \JLayoutFile('language_links', JPATH_ROOT . '/components/com_thm_organizer/Layouts');
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

        $document = \JFactory::getDocument();
        $document->setTitle(Languages::_('THM_ORGANIZER_SEARCH_VIEW_TITLE'));
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/search.css');
    }
}
