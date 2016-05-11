<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewColor_Manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;;
jimport('thm_core.list.view');

/**
 * Class loads persistent information about all colors into display context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewColor_Manager extends THM_CoreViewList
{
    public $items;

    public $pagination;

    public $state;

    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return  void
     * 
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function display($tpl = null)
    {
        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return  void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_COLOR_MANAGER_VIEW_TITLE'), 'organizer_colors');

        $actions = $this->getModel()->actions;

        if ($actions->{'core.create'})
        {
            JToolbarHelper::addNew('color.add');
        }

        if ($actions->{'core.edit'})
        {
            JToolbarHelper::editList('color.edit');
        }

        if ($actions->{'core.delete'})
        {
            JToolbarHelper::deleteList('COM_THM_ORGANIZER_ACTION_DELETE_CONFIRM', 'color.delete');
        }

        if ($actions->{'core.admin'})
        {
            JToolbarHelper::divider();
            JToolbarHelper::preferences('com_thm_organizer');
        }
    }
}
