<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/pools.php';

use Joomla\CMS\Uri\Uri;
use OrganizerHelper;
use THM_OrganizerHelperHTML as HTML;
use THM_OrganizerHelperLanguages as Languages;

/**
 * Class loads curriculum information into the display context.
 */
class THM_OrganizerViewCurriculum extends \Joomla\CMS\MVC\View\HtmlView
{
    public $disclaimer;

    public $disclaimerData;

    public $ecollabLink;

    public $item;

    public $lang;

    public $languageLinks;

    public $languageParams;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $menu = OrganizerHelper::getApplication()->getMenu()->getActive();

        if (!is_object($menu)) {
            $this->ecollabLink = '';

            $this->lang = Languages::getLanguage();
        } else {
            $this->ecollabLink = $menu->params->get('eCollabLink', '');

            $this->lang = Languages::getLanguage($menu->params->get('initialLanguage', ''));
        }

        $this->item = $this->get('Item');

        $this->languageLinks  = new \JLayoutFile('language_links', JPATH_ROOT . '/components/com_thm_organizer/Layouts');
        $this->languageParams = ['id' => $this->item->id, 'view' => 'curriculum'];

        $this->disclaimer = new \JLayoutFile('disclaimer', JPATH_ROOT . '/components/com_thm_organizer/Layouts');

        $this->disclaimerData = ['language' => $this->lang];

        parent::display($tpl);
    }

    /**
     * Sets document scripts and styles
     *
     * @return void
     */
    private function modifyDocument()
    {
        HTML::_('bootstrap.tooltip');
        HTML::_('bootstrap.framework');

        $document = \JFactory::getDocument();
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/curriculum.css');
        $document->addScript(Uri::root() . 'components/com_thm_organizer/js/curriculum.js');
        $document->addScript(Uri::root() . 'components/com_thm_organizer/js/container.js');
    }
}
