<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.administrator
 * @name        THM_OrganizerViewthm_organizer
 * @description view output class for the component splash page
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/views/form.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class defining view output
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.administrator
 */
class THM_OrganizerViewTHM_Organizer extends THM_OrganizerViewForm
{
    /**
     * Loads model data into view context
     *
     * @param string $tpl the template type to be used
     *
     * @return  void or JError on unauthorized access
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.tooltip');

        //THM_OrganizerHelperComponent::addSubmenu($this);

        parent::display($tpl);
    }

    /**
     * creates a joomla administratoristrative tool bar
     *
     * @return void
     */
    protected function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_MAIN_VIEW_TITLE'), 'organizer');

        if ($this->getModel()->actions->{'core.admin'}) {
            JToolbarHelper::divider();
            JToolbarHelper::preferences('com_thm_organizer');
        }
    }

    /**
     * Adds styles and scripts to the document
     *
     * @return  void  modifies the document
     */
    protected function modifyDocument()
    {
        JHtml::_('bootstrap.tooltip');
        JHtml::_('behavior.framework', true);
        JHtml::_('behavior.formvalidation');
        JHtml::_('formbehavior.chosen', 'select');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JUri::root() . "/media/com_thm_organizer/fonts/iconfont.css");
        $document->addStyleSheet(JUri::root() . "/media/com_thm_organizer/css/backend.css");
    }
}
