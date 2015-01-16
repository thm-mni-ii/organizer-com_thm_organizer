<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        subject details view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @author      James Antrim,  <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
jimport('thm_core.helpers.corehelper');
require_once JPATH_COMPONENT . '/helpers/language.php';

/**
 * Class loads information about a subject into the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSubject_Details extends JViewLegacy
{
    public $languageSwitches = array();

    /**
     * Method to get display
     *
     * @param   Object $tpl template  (default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();
        $this->setLanguage();
        $this->item = $this->get('Item');
        $this->getLanguageSwitches();
        parent::display($tpl);
    }

    /**
     * Modifies document variables and adds links to external files
     *
     * @return  void
     */
    private function modifyDocument()
    {
        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.framework', true);

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . '/media/com_thm_organizer/css/subject_details.css');
    }

    /**
     * Sets the Joomla Language
     */
    private function setLanguage()
    {
        $app = JFactory::getApplication();
        $requested = $app->input->get('languageTag', '');
        $supportedLanguages = array('en', 'de');
        if (in_array($requested, $supportedLanguages))
        {
            $lang = JFactory::getApplication()->getLanguage();
            if ($requested == 'en')
            {
                $lang->setLanguage('en-GB');
                return;
            }
            if ($requested == 'de')
            {
                $lang->setLanguage('de-DE');
                return;
            }
            $lang->setLanguage('en-GB');
        }
    }

    /**
     * Sets the language to the one requested
     *
     * @return  void  sets the default language for joomla
     */
    private function getLanguageSwitches()
    {
        $input = JFactory::getApplication()->input;
        $this->menuID = $input->getInt('Itemid', 0);
        $current = THM_CoreHelper::getLanguageShortTag();
        $supportedLanguages = array('en', 'de');
        $url = "index.php?option=com_thm_organizer&view=subject_details&id={$this->item->id}";
        foreach ($supportedLanguages AS $supported)
        {
            if ($current != $supported)
            {
                $this->languageSwitches[] = THM_OrganizerHelperLanguage::languageSwitch($url, $supported);
            }
        }
    }
}
