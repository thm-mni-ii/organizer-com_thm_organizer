<?php
/**
 * 
 * view.html.php
 * view = viewnote
 * 
 */
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for the Giessen Scheduler Component
 *
 * @package    Giessen Scheduler
 */
 
class thm_organizerViewEvent extends JView
{
    function display($tpl = null)
    {
    	$model =& $this->getModel();
       	$user =& JFactory::getUser();
        $userid = $user->id;
        $usergid = $user->gid;
        
    	//data specific to one event
	$data = & $model->data;
        $this->assignRef( 'event', $data );

        //joomla menu item #
        $itemid = JRequest::getVar('Itemid');
        $this->assignRef( 'itemid', $itemid );

        //assign specifics
        $authorid = $data->authorid;
        if($authorid == $userid || $usergid >= 24) $this->setLinks();

        parent::display($tpl);
    }
    
    function setLinks()
    {
        JHTML::_('behavior.tooltip');

        $editimage = JHTML::_( 'image.site', 'edit.png', 'components/com_thm_organizer/assets/images/',
                               NULL, NULL, JText::_( 'Bearbeiten' ));
        $edittiptext = JText::_( 'diesen Termin bearbeiten' );
        $edittiptitle = JText::_( 'Termin bearbeiten' );
        $editurl = 'index.php?option=com_thm_organizer&view=editevent&eventid='
                    .$this->event->eventid.'&Itemid='.$this->itemid;
        $editlink = '<a href="'.JRoute::_($editurl).'" class="editEventLink hasTip"
                        title="'.$edittiptitle.'::'.$edittiptext.'">'.$editimage.'</a>';
        $this->assignRef( 'editlink', $editlink );
        $deleteimage = JHTML::_('image.site', 'delete.png', 'components/com_thm_organizer/assets/images/',
                            NULL, NULL, JText::_( 'L&ouml;schen' ));
        $deletetiptext = JText::_( 'diesen Termin l&ouml;schen?' );
        $deletetiptitle = JText::_( 'Termin l&ouml;schen' );
        $deleteurl = 'index.php?option=com_thm_organizer&controller=editevent&task=delete_event&eventid='.$this->event->eventid.'&Itemid='.$this->itemid;
        $deletelink = '<a href="'.JRoute::_($deleteurl).'" class="deleteEventLink hasTip"
                          title="'.$deletetiptitle.'::'.$deletetiptext.'">'.$deleteimage.'</a>';
        $this->assignRef( 'deletelink', $deletelink );

        return;
    }
    
	

}