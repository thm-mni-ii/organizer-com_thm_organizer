<?php
/**
 * Room View Class for the Giessen Scheduler Component
 *
 * @package    Giessen Scheduler
 */
 
// no direct access
 
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
        $this->assignRef('day', $model->dayName);
        $this->assignRef('date', $model->displayDate);
        $this->assignRef('eventsExist', $model->eventsExist);
        $this->assignRef('appointments', $model->appointments);
        $this->assignRef('notices', $model->notices);
        $this->assignRef('information', $model->information);
        $this->assignRef('upcoming', $model->upcoming);
        $this->setLayout($model->layout);
        $this->setLinks();
 
        parent::display($tpl);
    }
    
    function setLinks()
    {
        JHTML::_('behavior.tooltip');
        $attribs = 'style="height: 16px; width: 16px;"';
        $image = JHTML::_('image.site', 'back.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, JText::_( 'Zur&uuml;ck' ));
        $tiptitle = JText::_( 'Zur&uuml;ck' );
        $tiptext = JText::_( 'Zur&uuml;ck zur letzten Seite.' );
        $backlink = "<a href='javascript:history.go(-1)' class='backLink hasTip' title='$tiptitle::$tiptext'>$image</a>";
        $this->assignRef( 'backlink', $backlink );
        return;
    }
}