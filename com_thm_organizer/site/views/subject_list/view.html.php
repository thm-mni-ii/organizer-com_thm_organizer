<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        list view for subject resources
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
jimport('thm_core.helpers.corehelper');
require_once JPATH_COMPONENT . '/helpers/language.php';

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
    public $languageSwitches = array();

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
        THM_OrganizerHelperLanguage::setLanguage();

        $this->state = $this->get('State');
        $this->items = $this->get('items');
        $this->pagination = $this->get('Pagination');

        $this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches(array('view' => 'subject_list'));

        $model = $this->getModel();
        $this->programName = $model->programName;
        parent::display($tpl);
    }

    /**
     * Modifies document variables and adds links to external files
     *
     * @return  void
     */
    private function modifyDocument()
    {
        JHtml::_('bootstrap.framework');
        JHtml::_('bootstrap.tooltip');
        JHtml::_('jquery.ui');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . '/media/com_thm_organizer/css/subject_list.css');
    }
}
