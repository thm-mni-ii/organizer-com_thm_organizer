<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewcategory_manager
 * @description view output file for event category lists
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';
/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerViewCategory_Manager extends JView
{
    /**
     * loads persistent information into the view context
     *
     * @param   string  $tpl  the name of the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");

        $model = $this->getModel();
        $this->state = $this->get('State');
        $this->categories = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->contentCategories = $model->contentCategories;
        $this->addToolBar();

        parent::display($tpl);
    }

    /**
     * generates joomla toolbar elements
     *
     * @return void
     */
    private function addToolBar()
    {
        $title = JText::_('COM_THM_ORGANIZER') . ': ' . JText::_('COM_THM_ORGANIZER_CAT_TITLE');
        JToolBarHelper::title($title, 'organizer_categories');
        JToolBarHelper::addNew('category.add');
        JToolBarHelper::editList('category.edit');
        JToolBarHelper::deleteList(JText::_('COM_THM_ORGANIZER_CAT_DELETE_CONFIRM'), 'category.delete');
        JToolBarHelper::divider();
        JToolBarHelper::preferences('com_thm_organizer');
    }
}
