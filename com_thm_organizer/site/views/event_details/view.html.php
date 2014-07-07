<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewEvent
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/helper/event.php';

/**
 * Retrieves event data and loads it into the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewEvent_Details extends JViewLegacy
{
    /**
     * Loads event information into the view context
     *
     * @param   string  $tpl  the name of the template to use
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.tooltip');
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");

        $model = $this->getModel();
        $this->event = $model->event;
        $this->itemID = JRequest::getVar('Itemid');
        $this->listLink = $model->listLink;
        $this->canWrite = $model->canWrite;

        $item = new stdClass;
        $dispatcher = JDispatcher::getInstance();
        $item->text = $this->event['description'];
        JPluginHelper::importPlugin('content');
        $dispatcher->trigger('onContentPrepare', array ('com_content.article', &$item, &$this->params));
        $this->event['description'] = $item->text;
        unset($item);

        THM_OrganizerHelperEvent::buildText($this->event);

        parent::display($tpl);
    }
}
