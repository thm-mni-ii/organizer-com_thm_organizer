<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        list view for subject resources
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/helper/language.php';
jimport('jquery.jquery');

/**
 * Class loads a list of subjects sorted according to different criteria into
 * the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSubject_List extends JViewLegacy
{
    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        JFactory::getDocument()->addStyleSheet($this->baseurl . "/media/com_thm_organizer/css/thm_organizer.css");

        $this->state = $this->get('State');
        $this->items = $this->get('items');
        $this->pagination = $this->get('Pagination');
        $this->_layout = ($this->state->get('groupBy'))? 'grouped_list' : 'ungrouped_list';

        $model = $this->getModel();
        $this->programName = $model->programName;
        $this->groups = $model->groups;
        $this->setTextsAndLinks();
        parent::display($tpl);
    }

    /**
     * Sets text and link values used by both templates
     * 
     * @return  void
     */
    private function setTextsAndLinks()
    {
        $this->otherLanguageTag = ($this->state->get('languageTag') == 'de') ? 'en' : 'de';
        $this->langURI = THM_OrganizerHelperLanguage::languageSwitch(
                'subject_list', $this->otherLanguageTag, $this->state->get('menuID'), $this->state->get('groupBy')
            );
        $this->baseLink = "index.php?option=com_thm_organizer&view=subject_list&view=subject_list&Itemid={$this->state->get('menuID')}&groupBy=";
        $languageTag = $this->state->get('languageTag');
        $this->subjectListText = ($languageTag == 'de')? 'Modulhandbuch' : 'Subject List';
        $this->alphabeticalTabText = ($languageTag == 'de')? "...im Übersicht" : "...in overview";
        $this->alphabeticalLink = JRoute::_($this->baseLink . NONE);
        $this->poolTabText = ($languageTag == 'de')? "...nach Modulpool" : "...by subject pool";
        $this->poolLink = JRoute::_($this->baseLink . POOL);
        $this->teacherTabText = ($languageTag == 'de')? "...nach Dozent" : "...by teacher";
        $this->teacherLink = JRoute::_($this->baseLink . TEACHER);
        $this->fieldTabText = ($languageTag == 'de')? "...nach Fachgruppe" : "...by field of study";
        $this->fieldLink = JRoute::_($this->baseLink . FIELD);
        $this->searchText = ($languageTag == 'de')? "Suchen" : "Search";
        $this->resetText = ($languageTag == 'de')? "Löschen" : "Reset";
        $this->flagPath = "media/com_thm_organizer/images/{$this->otherLanguageTag}.png";
        switch ($this->state->get('groupBy', NONE))
        {
            case NONE:
                $this->alphabeticalActive = 'active';
                $this->poolActive = $this->teacherActive = $this->fieldActive = 'inactive';
                break;
            case POOL:
                $this->poolActive = 'active';
                $this->alphabeticalActive = $this->teacherActive = $this->fieldActive = 'inactive';
                break;
            case TEACHER:
                $this->teacherActive = 'active';
                $this->alphabeticalActive = $this->poolActive = $this->fieldActive = 'inactive';
                break;
            case FIELD:
                $this->fieldActive = 'active';
                $this->alphabeticalActive = $this->poolActive = $this->teacherActive = 'inactive';
                break;
        }
    }


}
