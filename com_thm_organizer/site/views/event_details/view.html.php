<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewEvent
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/helpers/event.php';

/**
 * Retrieves event data and loads it into the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewEvent_Details extends JViewLegacy
{
    public $buttons = array();

    /**
     * Loads model data into context and sets variables used for html output
     *
     * @param   object  $tpl  the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        $this->modifyDocument();

        $this->item = $this->get('Item');
        if (empty($this->item))
        {
            JFactory::getApplication()->enqueueMessage('Something is fucked.', 'error');
            return false;
        }

        $authorised = true;
        if ($authorised !== true)
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_NO_ACCESS_VIEW'), 'error');
            return false;
        }
        $this->addToolBar();
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

        $document = Jfactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/libraries/thm_core/fonts/iconfont.css");
        $document->addStyleSheet($this->baseurl . '/media/com_thm_organizer/css/event_details.css');
    }

    /**
     * Creates HTML elements from saved data
     *
     * @return void
     */
    protected function addToolbar()
    {
        if ($this->item->isManager)
        {
            $listButton = '<a href="' . JRoute::_($this->item->managerLink) . '" class="btn">';
            $listButton .= '<span class="icon-list-view"></span>&#160;' . JText::_('COM_THM_ORGANIZER_ACTION_LIST_VIEW') . '</a>';
            $this->buttons[] = $listButton;
        }

        $menuID = JFactory::getApplication()->input->getInt('Itemid', 0);
        $menuLink = empty($menuID)? '' : "&Itemid=$menuID";

        $canCreate = $this->item->params->get('access-create');
        if ($canCreate)
        {
            $newLink = "index.php?option=com_thm_organizer&view=event_edit" . $menuLink;
            $newButton = '<a href="' . JRoute::_($newLink) . '" class="btn">';
            $newButton .= '<span class="icon-new"></span>&#160;' . JText::_('COM_THM_ORGANIZER_ACTION_ADD') . '</a>';
            $this->buttons[] = $newButton;
        }

        $canEdit = $this->item->params->get('access-edit');
        if ($canEdit)
        {
            $editLink = "index.php?option=com_thm_organizer&view=event_edit&id=" . $this->item->id . $menuLink;
            $editButton = '<a href="' . JRoute::_($editLink) . '" class="btn">';
            $editButton .= '<span class="icon-edit"></span>&#160;' . JText::_('COM_THM_ORGANIZER_ACTION_EDIT') . '</a>';
            $this->buttons[] = $editButton;
        }


    }
}
