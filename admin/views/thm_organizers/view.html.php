<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        view thm organizer main menu
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined( '_JEXEC' ) or die;
jimport( 'joomla.application.component.view');
class thm_organizersViewthm_organizers extends JView
{
    public function display($tpl = null)
    {
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        jimport('joomla.html.pane');
        $pane = JPane::getInstance('sliders');
        $this->pane = $pane;

        $application = JFactory::getApplication("administrator");
        $this->option = $application->scope;

        JToolBarHelper::title( JText::_( 'COM_THM_ORGANIZER' ).": ".JText::_( "COM_THM_ORGANIZER_MAIN_TITLE" ), 'home.png' );

        $this->addViews();

	parent::display($tpl);
    }

    /**
     * addViews
     *
     * creates html elements for links to component views
     */
    private function addViews()
    {
        $linkStart = "<a href='index.php?option={$this->option}&view=VIEWTEXT' >";
        $views = array( 'category_manager' => array(),
                        'monitor_manager' => array(),
                        'semester_manager' => array(),
                        'schedule_manager' => array(),
                        'scheduler_application_settings' => array(),
                        'virtual_schedule_manager' => array());
        $views['category_manager']['title'] = JText::_('COM_THM_ORGANIZER_CAT_TITLE');
        $views['monitor_manager']['title'] = JText::_('COM_THM_ORGANIZER_MON_TITLE');
        $views['semester_manager']['title'] = JText::_('COM_THM_ORGANIZER_SEM_TITLE');
        $views['schedule_manager']['title'] = JText::_('COM_THM_ORGANIZER_SCH_TITLE');
        $views['scheduler_application_settings']['title'] = JText::_('COM_THM_ORGANIZER_RIA_TITLE');
        $views['virtual_schedule_manager']['title'] = JText::_('COM_THM_ORGANIZER_VSM_TITLE');
        foreach($views as $k => $view)
        {
            $views[$k]['link_start'] = str_replace("VIEWTEXT", $k, $linkStart);
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
