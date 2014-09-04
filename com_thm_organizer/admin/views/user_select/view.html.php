<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewUser_Manager
 * @description view output file for user lists
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('jquery.jquery');
/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewUser_Select extends JViewLegacy
{
    /**
     * loads data into view output context and initiates functions creating html
     * elements
     *
     * @param   string  $tpl  the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        if (JFactory::getUser()->authorise('core.admin'))
        {
            JHtml::_('behavior.tooltip');
            JHtml::_('behavior.multiselect');

            $this->model = $this->getModel();
            $this->items = $this->get('Items');
            $this->state = $this->get('State');
            $this->pagination = $this->get('Pagination');
            $document = JFactory::getDocument();
            $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/user_select.css');

            parent::display($tpl);
        }

    }
}
