<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.administrator
 * @name        THM_OrganizerViewthm_organizer
 * @description view output class for the component splash page
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * Class defining view output
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.administrator
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewTHM_Organizer extends JViewLegacy
{
    /**
     * loads model data into view context
     *
     * @param   string  $tpl  the template type to be used
     *
     * @return void or JError on unauthorized access
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/thm_organizer.css');

        $application = JFactory::getApplication("administratoristrator");
        $this->option = $application->scope;

        $this->addToolBar();

        $this->addViews();

    parent::display($tpl);
    }

    /**
     * creates a joomla administratoristrative tool bar
     *
     * @return void
     */
    private function addToolBar()
    {
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER') . ': ' . JText::_('COM_THM_ORGANIZER_MAIN_TITLE'), 'organizer');
        JToolbarHelper::preferences('com_thm_organizer');
    }

    /**
     * creates html elements for the main menu
     *
     * @return void
     */
    private function addViews()
    {
        $views = array();
        $this->addView($views, 'user_manager', 'users', 'USM');
        $this->addView($views, 'category_manager', 'categories', 'CAT');
        $this->addView($views, 'schedule_manager', 'schedules', 'SCH');
        $this->addView($views, 'virtual_schedule_manager', 'virtual_schedules', 'VSM');
        $this->addView($views, 'degree_manager', 'degrees', 'DEG');
        $this->addView($views, 'color_manager', 'colors', 'CLM');
        $this->addView($views, 'field_manager', 'fields', 'FLM');
        $this->addView($views, 'program_manager', 'programs', 'PRM');
        $this->addView($views, 'pool_manager', 'pools', 'POM');
        $this->addView($views, 'subject_manager', 'subjects', 'SUM');
        $this->addView($views, 'teacher_manager', 'teachers', 'TRM');
        $this->addView($views, 'room_manager', 'rooms', 'RMM');
        $this->addView($views, 'monitor_manager', 'monitors', 'MON');

        $this->views = $views;
    }

    /**
     * Adds individual views to the array
     *
     * @param   array   &$views        the array holding view information
     * @param   string  $name          the name of the view
     * @param   string  $resourceName  the name of the resource managed by the view
     * @param   string  $langStub      unique identifier used for the view in the language file
     *
     * @return  void
     */
    private function addView(&$views, $name, $resourceName, $langStub)
    {
        $titleConstant = "COM_THM_ORGANIZER_{$langStub}_TITLE";
        $descConstant = "COM_THM_ORGANIZER_{$langStub}_DESC";
        $views[$name] = array();
        $views[$name]['title'] = JText::_($titleConstant);
        $views[$name]['tooltip'] = JText::_($titleConstant) . '::' . JText::_($descConstant);
        $views[$name]['url'] = "index.php?option=com_thm_organizer&view=$name";
        $views[$name]['image'] = "media/com_thm_organizer/images/{$resourceName}48.png";
    }
}
