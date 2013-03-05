<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizersViewthm_organizers
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
class THM_OrganizersViewTHM_Organizers extends JView
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
    	if (thm_organizerHelper::isAdmin("thm_organizers"))
    	{
    		JToolBarHelper::preferences('com_thm_organizer');
    	}
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
                        'monitor_manager' => array(),
                        'soapqueries' => array(),
                        'semesters' => array(),
                        'lecturers' => array(),
                        'assets' => array(),
                        'colors' => array(),
                        'degrees' => array(),
                        'majors' => array());

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

        $views['monitor_manager']['title'] = JText::_('COM_THM_ORGANIZER_MON_TITLE');
        $title_text = JText::_('COM_THM_ORGANIZER_MON_TITLE') . '::' . JText::_('COM_THM_ORGANIZER_MON_DESC');
        $views['monitor_manager']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['soapqueries']['title'] = JText::_('COM_THM_ORGANIZER_SUBMENU_SOAP_QUERIES');
        $title_text = JText::_('COM_THM_ORGANIZER_SUBMENU_SOAP_QUERIES') . '::' . JText::_('COM_THM_ORGANIZER_SUBMENU_SOAP_QUERIES_DESC');
        $views['soapqueries']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['semesters']['title'] = JText::_('COM_THM_ORGANIZER_SUBMENU_SEMESTERS');
        $title_text = JText::_('COM_THM_ORGANIZER_SUBMENU_SEMESTERS') . '::' . JText::_('COM_THM_ORGANIZER_SUBMENU_SEMESTERS_DESC');
        $views['semesters']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['lecturers']['title'] = JText::_('COM_THM_ORGANIZER_SUBMENU_LECTURERS');
        $title_text = JText::_('COM_THM_ORGANIZER_SUBMENU_LECTURERS') . '::' . JText::_('COM_THM_ORGANIZER_SUBMENU_LECTURERS_DESC');
        $views['lecturers']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['assets']['title'] = JText::_('COM_THM_ORGANIZER_SUBMENU_ASSETS');
        $title_text = JText::_('COM_THM_ORGANIZER_SUBMENU_ASSETS') . '::' . JText::_('COM_THM_ORGANIZER_SUBMENU_ASSETS_DESC');
        $views['assets']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['colors']['title'] = JText::_('COM_THM_ORGANIZER_SUBMENU_COLORS');
        $title_text = JText::_('COM_THM_ORGANIZER_SUBMENU_COLORS') . '::' . JText::_('COM_THM_ORGANIZER_SUBMENU_COLORS_DESC');
        $views['colors']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['degrees']['title'] = JText::_('COM_THM_ORGANIZER_SUBMENU_DEGREES');
        $title_text = JText::_('COM_THM_ORGANIZER_SUBMENU_DEGREES') . '::' . JText::_('COM_THM_ORGANIZER_SUBMENU_DEGREES_DESC');
        $views['degrees']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        $views['majors']['title'] = JText::_('COM_THM_ORGANIZER_SUBMENU_MAJORS');
        $title_text = JText::_('COM_THM_ORGANIZER_SUBMENU_MAJORS') . '::' . JText::_('COM_THM_ORGANIZER_SUBMENU_MAJORS_DESC');
        $views['majors']['link_start'] = str_replace("TITLETEXT", $title_text, $linkStart);

        // Setting correct html attributes and the images
        foreach ($views as $k => $view)
        {
            $views[$k]['link_start'] = str_replace("VIEWTEXT", $k, $views[$k]['link_start']);
            $views[$k]['image'] = JHTML::_('image',
                                           "components/com_thm_organizer/assets/images/$k.png",
                                           $view['title'],
                                           array( 'class' => 'thm_organizer_main_image')
                                          );
            $views[$k]['text'] = '<span>' . $view['title'] . '</span>';
            $views[$k]['link_end'] = '</a>';
        }
        $this->views = $views;
    }
}
