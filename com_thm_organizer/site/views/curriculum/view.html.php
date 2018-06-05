<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/component.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/pools.php';

/**
 * Class loads curriculum information into the display context.
 */
class THM_OrganizerViewCurriculum extends JViewLegacy
{
    /**
     * The HTML Strings for language switch buttons
     *
     * @var string
     */
    public $languageSwitches;

    /**
     * The data to be displayed
     *
     * @var object
     */
    public $item;

    /**
     * The link to the ecollaboration platform
     *
     * @var string
     */
    public $ecollabLink;

    public $lang;

    public $disclaimer;

    public $disclaimerData;

    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $menu = JFactory::getApplication()->getMenu()->getActive();

        if (!is_object($menu)) {
            $this->ecollabLink = '';

            $this->lang = THM_OrganizerHelperLanguage::getLanguage();
        } else {
            $this->ecollabLink = $menu->params->get('eCollabLink', '');

            $this->lang = THM_OrganizerHelperLanguage::getLanguage($menu->params->get('initialLanguage', ''));
        }


        $this->item             = $this->get('Item');
        $lsParams               = ['view' => 'curriculum', 'id' => $this->item->id];
        $this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($lsParams);

        $this->disclaimer     = new JLayoutFile('disclaimer',
            $basePath = JPATH_ROOT . '/media/com_thm_organizer/layouts');
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
        JHtml::_('bootstrap.tooltip');
        JHtml::_('bootstrap.framework');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/curriculum.css');
        $document->addScript(JUri::root() . '/media/com_thm_organizer/js/curriculum.js');
        $document->addScript(JUri::root() . '/media/com_thm_organizer/js/container.js');
    }
}
