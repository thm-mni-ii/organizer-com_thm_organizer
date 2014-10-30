<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        subject details view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @author      James Antrim,  <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/helper/language.php';

/**
 * Class loads information about a subject into the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSubject_Details extends JViewLegacy
{
    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . '/components/com_thm_organizer/assets/css/thm_organizer.css');

        $itemID = JFactory::getApplication()->input->get('Itemid');
        if (!empty($itemID))
        {
            JFactory::getApplication()->getMenu()->setActive($itemID);
        }

        $model = $this->getModel();
        $this->subject = $model->subject;
        $this->session = JFactory::getSession();

        // Comma seperated lecturer data */
        $this->moduleNavigation = json_decode($this->session->get('navi_json'));
        $this->lang = JRequest::getVar('languageTag');
        $this->otherLanguageTag = ($this->lang == 'de') ? 'en' : 'de';
        $this->langUrl = self::languageSwitcher($this->otherLanguageTag);
 
 
        parent::display($tpl);
    }

    /**
     * Method to build the url for the language switcher butto
     *
     * @param   String  $langLink  Language link
     *
     * @return  String
     */
    private function languageSwitcher($langLink)
    {
        $itemid = JRequest::getVar('Itemid');
        $group = JRequest::getVar('view');
        $URI = JURI::getInstance('index.php');
        $moduleID = JFactory::getApplication()->input->get('id');

        $switchParams = array('option' => 'com_thm_organizer',
                'view' => $group,
                'Itemid' => $itemid,
                'id' => $moduleID,
                'languageTag' => $langLink
        );

        $URIparams = array_merge($URI->getQuery(true), $switchParams);
        $query = $URI->buildQuery($URIparams);
        $URI->setQuery($query);

        return $URI->toString();
    }
}
