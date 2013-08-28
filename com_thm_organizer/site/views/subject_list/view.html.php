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
require_once JPATH_COMPONENT . DS . 'helper' . DS . 'language.php';

/**
 * Class loads a list of subjects sorted according to different criteria into
 * the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSubject_List extends JView
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
        JFactory::getDocument()->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");

        $this->state = $this->get('State');
        $this->items = $this->get('items');
        $this->pagination = $this->get('Pagination');
        $this->_layout = ($this->state->get('groupBy'))? 'grouped_list' : 'ungrouped_list';

        $model = $this->getModel();
        $this->groups = $model->groups;
        $this->programName = $model->programName;

        $this->otherLanguageTag = ($this->state->get('languageTag') == 'de') ? 'en' : 'de';
        $this->langURI = THM_OrganizerHelperLanguage::languageSwitch(
                'subject_list', $this->otherLanguageTag, $this->state->get('menuID'), $this->state->get('groupBy')
            );

        parent::display($tpl);
    }


}
