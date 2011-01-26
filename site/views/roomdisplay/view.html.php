<?php
/**
 * Room View Class for the Giessen Scheduler Component
 *
 * @package    Giessen Scheduler
 */
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
class thm_organizerViewRoomDisplay extends JView
{
    function display($tpl = null)
    {
        $model =& $this->getModel();
        $data = $model->data;
        //var_dump($data);
        $this->assignRef( 'roomname', $data->roomname );
        if(isset($data->blocks) && count($data->blocks) > 0 )
            $this->assignRef( 'blocks', $data->blocks );
        $this->assignRef( 'day', $data->dayname);
        $this->assignRef( 'date', $data->displaydate);
        if(isset($data->reservingevents) && count($data->reservingevents) > 0 )
            $this->assignRef( 'reservingevents', $data->reservingevents);
        if(isset($data->notes) && count($data->notes) > 0 )
            $this->assignRef( 'notes', $data->notes);
        if(isset($data->globalevents) && count($data->globalevents) > 0 )
            $this->assignRef( 'globalevents', $data->globalevents);
        if(isset($data->futureevents) && count($data->futureevents) > 0 )
            $this->assignRef( 'futureevents', $data->futureevents);
        //$this->assignRef( 'url', $data->url);
        $this->setLayout($data->layout);
        //$document =& JFactory::getDocument();
        ///if($data->layout != registered)
        //    $document->addStyleSheet(JURI::base().'/components/com_thm_organizer/assets/css/thm_organizer.css');
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