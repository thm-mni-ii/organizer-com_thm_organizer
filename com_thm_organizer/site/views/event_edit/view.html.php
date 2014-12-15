<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        create/edit appointment/event view
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Loads model data into context and sets variables used for html output
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class Thm_OrganizerViewEvent_Edit extends JViewLegacy
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
        $this->form = $this->get('Form');

        // Allows for view specific toolbar handling
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
        JHtml::_('jquery.framework');
        JHtml::_('behavior.formvalidation');
        JHtml::_('formbehavior.chosen', 'select');

        $document = Jfactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/libraries/thm_core/fonts/iconfont.css");
        $document->addStyleSheet($this->baseurl . '/media/com_thm_organizer/css/event_edit.css');
        //$document->addScript($this->baseurl . '/media/com_thm_organizer/js/event_edit.js');
    }

    /**
     * Creates HTML elements from saved data
     *
     * @return void
     */
    protected function addToolbar()
    {
        $model = $this->getModel();
        if (!empty($model->listLink))
        {
            $listButton = '<a href="' . JRoute::_($model->listLink) . '" class="btn">';
            $listButton .= '<span class="icon-list-view"></span>&#160;' . JText::_('COM_THM_ORGANIZER_ACTION_LIST_VIEW') . '</button>';
            $this->buttons[] = $listButton;
        }

        $cancelButton = '<button type="button" class="btn" onclick="Joomla.submitbutton(\'event.cancel\')">';
        $eventID = $this->getForm()->getValue('id', 0);
        if ($eventID)
        {
            $menuID = JFactory::getApplication()->input->getInt('Itemid', 0);
            $eventLink = "index.php?option=com_thm_organizer&view=event_details&eventID=$eventID";
            $eventLink .= empty($menuID)? '' : "&Itemid=$menuID";
            $detailsButton = '<a href="' . JRoute::_($eventLink) . '" class="btn">';
            $detailsButton .= '<span class="icon-info-2"></span>&#160;' . JText::_('COM_THM_ORGANIZER_ACTION_DETAILS_VIEW') . '</button>';
            $this->buttons[] = $detailsButton;

            $cancelButton .= '<span class="icon-cancel"></span>&#160;' . JText::_('COM_THM_ORGANIZER_ACTION_CANCEL') . '</button>';
        }
        else
        {
            $cancelButton .= '<span class="icon-cancel"></span>&#160;' . JText::_('COM_THM_ORGANIZER_ACTION_CLOSE') . '</button>';
        }

        $previewButton = '<button type="button" class="btn" onclick="Joomla.submitbutton(\'event.preview\')">';
        $previewButton .= '<span class="icon-eye"></span>&#160;' . JText::_('COM_THM_ORGANIZER_ACTION_PREVIEW') . '</button>';
        $this->buttons[] = $previewButton;

        $saveButton = '<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton(\'event.save\')">';
        $saveButton .= '<span class="icon-save"></span>&#160;' . JText::_('JSAVE') . '</button>';
        $this->buttons[] = $saveButton;

        $resetButton = '<button type="reset" class="btn">';
        $resetButton .= '<span class="icon-undo-2"></span>&#160;' . JText::_('COM_THM_ORGANIZER_ACTION_RESET') . '</button>';
        $this->buttons[] = $resetButton;

        $this->buttons[] = $cancelButton;
    }
}
