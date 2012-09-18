<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        view thm organizer main menu
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined( '_JEXEC' ) or die;
jimport( 'joomla.application.component.view');
jimport('joomla.html.pane');
class thm_organizersViewthm_organizers extends JView
{
    public function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));

        JHtml::_('behavior.tooltip');

        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $pane = JPane::getInstance('sliders');
        $this->pane = $pane;

        $application = JFactory::getApplication("administrator");
        $this->option = $application->scope;

        $this->addToolBar();

        $this->addViews();

	parent::display($tpl);
    }

    /**
     * addToolBar
     *
     * creates a joomla administrative tool bar
     */
    private function addToolBar()
    {
    	JToolBarHelper::title( JText::_( 'COM_THM_ORGANIZER' ).": ".JText::_( "COM_THM_ORGANIZER_MAIN_TITLE" ), 'mni' );
    	if (thm_organizerHelper::isAdmin("thm_organizers"))
    	{
    		JToolBarHelper::preferences('com_thm_organizer');
    	}
    }
    
    /**
     * addViews
     *
     * creates html elements for the main menu
     */
    private function addViews()
    {
        $linkStart = "<a href='index.php?option={$this->option}&view=VIEWTEXT' class='hasTip' title='TITLETEXT' >";
        $views = array( 'category_manager' => array(),
                        'semester_manager' => array(),
                        'schedule_manager' => array(),
                        'virtual_schedule_manager' => array(),
                        'description_manager' => array(),
                        'department_manager' => array(),
                        'class_manager' => array(),
                        'teacher_manager' => array(),
                        'room_manager' => array(),
                        'monitor_manager' => array(),
                        'soapqueries' => array(),
                        'semesters' => array(),
                        'lecturers' => array(),
                        'assets' => array(),
                        'colors' => array(),
                        'degrees' => array(),
                        'majors' => array());
        
        // the single menu entries
        $views['category_manager']['title'] = JText::_('COM_THM_ORGANIZER_CAT_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_CAT_TITLE')."::".JText::_('COM_THM_ORGANIZER_CAT_DESC');
        $views['category_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['semester_manager']['title'] = JText::_('COM_THM_ORGANIZER_SEM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_SEM_TITLE')."::".JText::_('COM_THM_ORGANIZER_SEM_DESC');
        $views['semester_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['schedule_manager']['title'] = JText::_('COM_THM_ORGANIZER_SCH_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_SCH_TITLE')."::".JText::_('COM_THM_ORGANIZER_SCH_DESC');
        $views['schedule_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['virtual_schedule_manager']['title'] = JText::_('COM_THM_ORGANIZER_VSM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_VSM_TITLE')."::".JText::_('COM_THM_ORGANIZER_VSM_DESC');
        $views['virtual_schedule_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['description_manager']['title'] = JText::_('COM_THM_ORGANIZER_DSM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_DSM_TITLE')."::".JText::_('COM_THM_ORGANIZER_DSM_DESC');
        $views['description_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['department_manager']['title'] = JText::_('COM_THM_ORGANIZER_DPM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_DPM_TITLE')."::".JText::_('COM_THM_ORGANIZER_DPM_DESC');
        $views['department_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['class_manager']['title'] = JText::_('COM_THM_ORGANIZER_CLM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_CLM_TITLE')."::".JText::_('COM_THM_ORGANIZER_CLM_DESC');
        $views['class_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['teacher_manager']['title'] = JText::_('COM_THM_ORGANIZER_TRM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_TRM_TITLE')."::".JText::_('COM_THM_ORGANIZER_TRM_DESC');
        $views['teacher_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['room_manager']['title'] = JText::_('COM_THM_ORGANIZER_RMM_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_RMM_TITLE')."::".JText::_('COM_THM_ORGANIZER_RMM_DESC');
        $views['room_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['monitor_manager']['title'] = JText::_('COM_THM_ORGANIZER_MON_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_MON_TITLE')."::".JText::_('COM_THM_ORGANIZER_MON_DESC');
        $views['monitor_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['soapqueries']['title'] = JText::_('COM_THM_CURRICULUM_SUBMENU_SOAP_QUERIES');
        $title_text = JText::_('COM_THM_CURRICULUM_SUBMENU_SOAP_QUERIES')."::".JText::_('COM_THM_CURRICULUM_SUBMENU_SOAP_QUERIES_DESC');
        $views['soapqueries']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['semesters']['title'] = JText::_('COM_THM_CURRICULUM_SUBMENU_SEMESTERS');
        $title_text = JText::_('COM_THM_CURRICULUM_SUBMENU_SEMESTERS')."::".JText::_('COM_THM_CURRICULUM_SUBMENU_SEMESTERS_DESC');
        $views['semesters']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['lecturers']['title'] = JText::_('COM_THM_CURRICULUM_SUBMENU_LECTURERS');
        $title_text = JText::_('COM_THM_CURRICULUM_SUBMENU_LECTURERS')."::".JText::_('COM_THM_CURRICULUM_SUBMENU_LECTURERS_DESC');
        $views['lecturers']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['assets']['title'] = JText::_('COM_THM_CURRICULUM_SUBMENU_ASSETS');
        $title_text = JText::_('COM_THM_CURRICULUM_SUBMENU_ASSETS')."::".JText::_('COM_THM_CURRICULUM_SUBMENU_ASSETS_DESC');
        $views['assets']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['colors']['title'] = JText::_('COM_THM_CURRICULUM_SUBMENU_COLORS');
        $title_text = JText::_('COM_THM_CURRICULUM_SUBMENU_COLORS')."::".JText::_('COM_THM_CURRICULUM_SUBMENU_COLORS_DESC');
        $views['colors']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['degrees']['title'] = JText::_('COM_THM_CURRICULUM_SUBMENU_DEGREES');
        $title_text = JText::_('COM_THM_CURRICULUM_SUBMENU_DEGREES')."::".JText::_('COM_THM_CURRICULUM_SUBMENU_DEGREES_DESC');
        $views['degrees']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);
        
        $views['majors']['title'] = JText::_('COM_THM_CURRICULUM_SUBMENU_MAJORS');
        $title_text = JText::_('COM_THM_CURRICULUM_SUBMENU_MAJORS')."::".JText::_('COM_THM_CURRICULUM_SUBMENU_MAJORS_DESC');
        $views['majors']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        // setting correct html attributes and the images
        foreach($views as $k => $view)
        {
            $views[$k]['link_start'] = str_replace("VIEWTEXT", $k, $views[$k]['link_start']);
            $views[$k]['image'] = JHTML::_("image",
                                           "components/com_thm_organizer/assets/images/$k.png",
                                           $view['title'],
                                           array( 'class' => 'thm_organizer_main_image'));
            $views[$k]['text'] = "<span>".$view['title']."</span>";
            $views[$k]['link_end'] = "</a>";
        }
        $this->views = $views;
    }
}
