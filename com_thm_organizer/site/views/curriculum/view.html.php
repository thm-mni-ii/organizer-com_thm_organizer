<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        curriculum view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
jimport('joomla.application.component.view');
jimport('joomla.application.component.helper');
require_once JPATH_COMPONENT . DS . 'helper' . DS . 'language.php';
jimport('joomla.error.profiler');

/**
 * Class loasd curriculum information into the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewCurriculum extends JView
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
        JHtml::_('behavior.tooltip');
        jimport('extjs4.extjs4');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . '/media/com_thm_organizer/css/curriculum.css');
        $document->addScript($this->baseurl . '/media/com_thm_organizer/js/curriculum.js');

        // Get the parameters of the current view
        $this->params = JFactory::getApplication()->getMenu()->getActive()->params;
        $this->programName = $this->getModel()->getProgramName($this->params->get('programID'));
        $this->ecollabLink = JComponentHelper::getParams('com_thm_organizer')->get('eCollabLink');
        $this->languageTag = JRequest::getVar('languageTag', $this->params->get('language'));
        $this->langUrl = THM_OrganizerHelperLanguage::languageSwitch(
                'curriculum',
                ($this->languageTag == 'de') ? 'en' : 'de',
                JRequest::getInt('Itemid')
            );

        parent::display($tpl);
    }
}
