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
jimport('joomla.html.pane');

/**
 * Class defining view output
 * 
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.administrator
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewTHM_Organizer extends JView
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
        if (!JFactory::getUser()->authorise('core.administrator'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . '/components/com_thm_organizer/assets/css/thm_organizer.css');

        $pane = JPane::getInstance('sliders');
        $this->pane = $pane;

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
    	JToolBarHelper::title(JText::_('COM_THM_ORGANIZER') . ': ' . JText::_('COM_THM_ORGANIZER_MAIN_TITLE'), 'mni');
		JToolBarHelper::preferences('com_thm_organizer');
    }

    /**
     * creates html elements for the main menu
     * 
     * @return void
     */
    private function addViews()
    {
        $views = array();

        $views['category_manager'] = array();
        $views['category_manager']['title'] = JText::_('COM_THM_ORGANIZER_CAT_TITLE');
        $views['category_manager']['tooltip'] = JText::_('COM_THM_ORGANIZER_CAT_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_CAT_DESC');
        $views['category_manager']['url'] = "index.php?option=com_thm_organizer&view=category_manager";
        $views['category_manager']['image'] = "administrator/components/com_thm_organizer/assets/images/categories48.png";

        $views['schedule_manager'] = array();
        $views['schedule_manager']['title'] = JText::_('COM_THM_ORGANIZER_SCH_TITLE');
        $views['schedule_manager']['tooltip'] = JText::_('COM_THM_ORGANIZER_SCH_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_SCH_DESC');
        $views['schedule_manager']['url'] = "index.php?option=com_thm_organizer&view=schedule_manager";
        $views['schedule_manager']['image'] = "administrator/components/com_thm_organizer/assets/images/schedules48.png";

        $views['virtual_schedule_manager'] = array();
        $views['virtual_schedule_manager']['title'] = JText::_('COM_THM_ORGANIZER_VSM_TITLE');
        $views['virtual_schedule_manager']['tooltip'] = JText::_('COM_THM_ORGANIZER_VSM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_VSM_DESC');
        $views['virtual_schedule_manager']['url'] = "index.php?option=com_thm_organizer&view=virtual_schedule_manager";
        $views['virtual_schedule_manager']['image'] = "administrator/components/com_thm_organizer/assets/images/virtual_schedules48.png";

        $views['degree_manager'] = array();
        $views['degree_manager']['title'] = JText::_('COM_THM_ORGANIZER_DEG_TITLE');
        $views['degree_manager']['tooltip'] = JText::_('COM_THM_ORGANIZER_DEG_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_DEG_DESC');
        $views['degree_manager']['url'] = "index.php?option=com_thm_organizer&view=degree_manager";
        $views['degree_manager']['image'] = "administrator/components/com_thm_organizer/assets/images/degrees48.png";

        $views['color_manager'] = array();
        $views['color_manager']['title'] = JText::_('COM_THM_ORGANIZER_CLM_TITLE');
        $views['color_manager']['tooltip'] = JText::_('COM_THM_ORGANIZER_CLM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_CLM_DESC');
        $views['color_manager']['url'] = "index.php?option=com_thm_organizer&view=color_manager";
        $views['color_manager']['image'] = "administrator/components/com_thm_organizer/assets/images/colors48.png";

        $views['field_manager'] = array();
        $views['field_manager']['title'] = JText::_('COM_THM_ORGANIZER_FLM_TITLE');
        $views['field_manager']['tooltip'] = JText::_('COM_THM_ORGANIZER_FLM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_FLM_DESC');
        $views['field_manager']['url'] = "index.php?option=com_thm_organizer&view=field_manager";
        $views['field_manager']['image'] = "administrator/components/com_thm_organizer/assets/images/fields48.png";

        $views['program_manager'] = array();
        $views['program_manager']['title'] = JText::_('COM_THM_ORGANIZER_PRM_TITLE');
        $views['program_manager']['tooltip'] = JText::_('COM_THM_ORGANIZER_PRM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_PRM_DESC');
        $views['program_manager']['url'] = "index.php?option=com_thm_organizer&view=program_manager";
        $views['program_manager']['image'] = "administrator/components/com_thm_organizer/assets/images/programs48.png";

        $views['pool_manager'] = array();
        $views['pool_manager']['title'] = JText::_('COM_THM_ORGANIZER_POM_TITLE');
        $views['pool_manager']['tooltip'] = JText::_('COM_THM_ORGANIZER_POM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_POM_DESC');
        $views['pool_manager']['url'] = "index.php?option=com_thm_organizer&view=pool_manager";
        $views['pool_manager']['image'] = "administrator/components/com_thm_organizer/assets/images/pools48.png";

        $views['subject_manager'] = array();
        $views['subject_manager']['title'] = JText::_('COM_THM_ORGANIZER_SUM_TITLE');
        $views['subject_manager']['tooltip'] = JText::_('COM_THM_ORGANIZER_SUM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_SUM_DESC');
        $views['subject_manager']['url'] = "index.php?option=com_thm_organizer&view=subject_manager";
        $views['subject_manager']['image'] = "administrator/components/com_thm_organizer/assets/images/subjects48.png";

        $views['teacher_manager'] = array();
        $views['teacher_manager']['title'] = JText::_('COM_THM_ORGANIZER_TRM_TITLE');
        $views['teacher_manager']['tooltip'] = JText::_('COM_THM_ORGANIZER_TRM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_TRM_DESC');
        $views['teacher_manager']['url'] = "index.php?option=com_thm_organizer&view=teacher_manager";
        $views['teacher_manager']['image'] = "administrator/components/com_thm_organizer/assets/images/teachers48.png";

        $views['room_manager'] = array();
        $views['room_manager']['title'] = JText::_('COM_THM_ORGANIZER_RMM_TITLE');
        $views['room_manager']['tooltip'] = JText::_('COM_THM_ORGANIZER_RMM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_RMM_DESC');
        $views['room_manager']['url'] = "index.php?option=com_thm_organizer&view=room_manager";
        $views['room_manager']['image'] = "administrator/components/com_thm_organizer/assets/images/rooms48.png";

        $views['monitor_manager'] = array();
        $views['monitor_manager']['title'] = JText::_('COM_THM_ORGANIZER_MON_TITLE');
        $views['monitor_manager']['tooltip'] = JText::_('COM_THM_ORGANIZER_MON_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_MON_DESC');
        $views['monitor_manager']['url'] = "index.php?option=com_thm_organizer&view=monitor_manager";
        $views['monitor_manager']['image'] = "administrator/components/com_thm_organizer/assets/images/monitors48.png";

        $this->views = $views;
    }
}
