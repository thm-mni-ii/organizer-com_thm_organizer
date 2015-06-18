<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        curriculum view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
require_once JPATH_COMPONENT . '/helpers/language.php';

/**
 * Class loads curriculum information into the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
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
     * The maximum number of items to be displayed per line
     *
     * @var integer
     */
    public $maxItems;

    /**
     * The link to the ecollaboration platform
     *
     * @var string
     */
    public $ecollabLink;

    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $menu = JFactory::getApplication()->getMenu()->getActive();
        $this->maxItems = $menu->params->get('maxItems', 5);
        $this->ecollabLink = $menu->params->get('eCollabLink', '');

        THM_OrganizerHelperLanguage::setLanguage();
        $this->item = $this->get('Item');
        $lsParams = array('view' => 'curriculum', 'id' => $this->item->id);
        $this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($lsParams);

        parent::display($tpl);
    }

    /**
     * Sets document scripts and styles
     *
     * @return  void
     */
    private function modifyDocument()
    {
        JHtml::_('bootstrap.tooltip');
        JHtml::_('bootstrap.framework');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . '/media/com_thm_organizer/css/curriculum.css');
        $document->addScript($this->baseurl . '/media/com_thm_organizer/js/curriculum.js');
        $document->addScript($this->baseurl . '/libraries/thm_core/js/container.js');
    }
}
