<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
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
 * @subpackage  com_thm_organizer.admin
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
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . '/components/com_thm_organizer/assets/css/thm_organizer.css');

        $pane = JPane::getInstance('sliders');
        $this->pane = $pane;

        $application = JFactory::getApplication("administrator");
        $this->option = $application->scope;

        $this->addToolBar();

        $this->addViews();

	parent::display($tpl);
    }

    /**
     * creates a joomla administrative tool bar
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
        $linkStart = "<a href='index.php?option={$this->option}&view=VIEWTEXT' class='hasTip' title='TITLETEXT' >";
        $views = array( 'category_manager' => array(),
                        'schedule_manager' => array(),
                        'virtual_schedule_manager' => array(),
                        'degree_manager' => array(),
                        'color_manager' => array(),
                        'field_manager' => array(),
                        'degree_program_manager' => array(),
                        /*'term_manager' => array(),
                        'module_manager' => array(),*/
                        'teacher_manager' => array(),
                        /*'room_manager' => array(),*/
                        'monitor_manager' => array());

        // Individual view menu entries
        $views['category_manager']['title'] = JText::_('COM_THM_ORGANIZER_CAT_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_CAT_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_CAT_DESC');
        $views['category_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['schedule_manager']['title'] = JText::_('COM_THM_ORGANIZER_SCH_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_SCH_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_SCH_DESC');
        $views['schedule_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['virtual_schedule_manager']['title'] = JText::_('COM_THM_ORGANIZER_VSM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_VSM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_VSM_DESC');
        $views['virtual_schedule_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['degree_manager']['title'] = JText::_('COM_THM_ORGANIZER_DEG_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_DEG_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_DEG_DESC');
        $views['degree_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
		
        $views['color_manager']['title'] = JText::_('COM_THM_ORGANIZER_CLM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_CLM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_CLM_DESC');
        $views['color_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['field_manager']['title'] = JText::_('COM_THM_ORGANIZER_FLM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_FLM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_FLM_DESC');
        $views['field_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
		
        $views['degree_program_manager']['title'] = JText::_('COM_THM_ORGANIZER_DGP_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_DGP_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_DGP_DESC');
        $views['degree_program_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        /*$views['term_manager']['title'] = JText::_('COM_THM_ORGANIZER_SEM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_SEM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_SEM_DESC');
        $views['term_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['module_manager']['title'] = JText::_('COM_THM_ORGANIZER_MPM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_MPM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_MPM_DESC');
        $views['module_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);*/

        $views['teacher_manager']['title'] = JText::_('COM_THM_ORGANIZER_TRM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_TRM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_TRM_DESC');
        $views['teacher_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
		
        /*$views['room_manager']['title'] = JText::_('COM_THM_ORGANIZER_RMM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_RMM_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_RMM_DESC');
        $views['room_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);*/

        $views['monitor_manager']['title'] = JText::_('COM_THM_ORGANIZER_MON_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_MON_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_MON_DESC');
        $views['monitor_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        // Setting correct html attributes and the images
        foreach ($views as $k => $view)
        {
            $views[$k]['link_start'] = str_replace("VIEWTEXT", $k, $views[$k]['link_start']);
            $views[$k]['image'] = JHTML::_('image',
                                           "components/com_thm_organizer/assets/images/" . $k . ".png",
                                           $view['title'],
                                           array( 'class' => 'thm_organizer_main_image')
                                          );
            $views[$k]['text'] = '<span>' . $view['title'] . '</span>';
            $views[$k]['link_end'] = '</a>';
        }
        $this->views = $views;
    }
}
