<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        room display view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');
class thm_organizerViewroom_display extends JView
{
    function display($tpl = null)
    {
        $model = $this->getModel();
        $this->assignRef( 'name', $model->name );
        if(count($model->blocks) > 0)
        {
            $this->assignRef('blocks', $model->blocks);
            $this->assignRef('lessonsExist', $model->lessonsExist);
        }
        $this->displayDate = "$model->dayName, $model->displayDate";
        $this->day = $model->dayName;
        $this->date = $model->displayDate;
        $this->assignRef('eventsExist', $model->eventsExist);
        $this->assignRef('appointments', $model->appointments);
        $this->assignRef('notices', $model->notices);
        $this->assignRef('information', $model->information);
        $this->assignRef('upcoming', $model->upcoming);
        $this->setLayout($model->layout);
        $this->setHTMLElements();
 
        parent::display($tpl);
    }
    
    function setHTMLElements()
    {
        if($this->getLayout() == 'default')
        {
            $model = $this->getModel();
            JHTML::_('behavior.tooltip');
            $document = & JFactory::getDocument();
            $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
            $title = JText::_('COM_THM_ORGANIZER_RD_TITLE');
            $title .= $model->name;
            $title .= JText::_('COM_THM_ORGANIZER_RD_ON');
            $title .= $this->displayDate;
            $document->setTitle($title);

            if(isset($model->roomSelectLink))
            {
                $backSpan = "<span id='thm_organizer_back_span' class='thm_organizer_action_span'></span>";
                $backTip = JText::_('COM_THM_ORGANIZER_RD_RS_LINK_TITLE');
                $backTip .= "::";
                $backTip .= JText::_('COM_THM_ORGANIZER_RD_RS_LINK_TEXT');
                $attributes = array();
                $attributes['title'] = $backTip;
                $attributes['class'] = "hasTip thm_organizer_action_link";
                $backLink = JHtml::link($model->roomSelectLink, $backSpan.JText::_('COM_THM_ORGANIZER_RD_RS_LINK_TITLE'), $attributes);
                $this->backLink = $backLink;
            }
        }
        elseif($this->getLayout() == 'registered')
        {
            $this->thm_logo_image =
                    JHtml::image('components/com_thm_organizer/assets/images/thm_logo_giessen.png', JText::_('COM_THM_ORGANIZER_RD_LOGO_GIESSEN'));
            $this->thm_text_image =
                    JHtml::image('components/com_thm_organizer/assets/images/thm_text_dinpro_compact.png', JText::_('COM_THM_ORGANIZER_RD_THM'));
        }
    }
}