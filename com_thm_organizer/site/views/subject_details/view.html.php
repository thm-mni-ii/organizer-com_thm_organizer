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

    public $lang;

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
        $this->lang = THM_OrganizerHelperLanguage::getLanguage();
        $this->item = $this->get('Item');
        if (!empty($this->item->id))
        {
            $params = array('view' => 'subject_details', 'id' => $this->item->id);
            $this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);
        }
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
}
