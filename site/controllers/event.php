<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        create/edit appointment/event controller
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');

class thm_organizerControllerevent extends JController
{
    function display(){ parent::display(); }

    /**
    * Saves the event
    *
    */
    function save_event()
    {
        $eventid = JRequest::getInt( 'eventid', 0 );
        $dbo =& JFactory::getDBO();
        $user =& JFactory::getUser();
        $userid = $user->get('id');
        $gid = $user->get('gid');
        $itemid = JRequest::getVar('Itemid');
        $mysched = JRequest::getVar('mysched');

        //get author
        if($eventid != 0)
        {
            $query = "SELECT created_by FROM #__thm_organizer_events WHERE eid = $eventid";
            $dbo->setQuery($query);
            $author = $dbo->loadResult();
        }

        // Make sure user has the necessary access rights
        if ($gid < 19 || ($gid < 21 && $author != $userid && $eventid != 0))
        {
            JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
            return;
        }

        //get data from the request
        $model = $this->getModel('editevent');
        //$eventid = $model->save();
        $eventid = $model->save();
        if($eventid > 0)
        {
            $msg = JText::_( 'Termin gespeichert' );
            if($mysched) $link = JRoute::_('index.php?option=com_thm_organizer&view=editevent&eventid=0&tmpl=component', false);
            else $link = JRoute::_("index.php?option=com_thm_organizer&view=event&eventid=$eventid&Itemid=$itemid", false);
        }
        else
        {
            $msg = JText::_( 'Ein Fehler ist beim Speichern des Termins aufgetretten.' );
            if($mysched) $link = JRoute::_('index.php?option=com_thm_organizer&view=editevent&eventid=0&tmpl=component', false);
            else $link = JRoute::_("index.php?option=com_thm_organizer&view=eventlist&Itemid=$itemid", false);
        }
        $this->setRedirect($link, $msg);
    }

    /**
     * Deletes the event everything created with it
     *
     */
    function delete_event()
    {
		// Check for request forgeries
		//how does this work?
		//JRequest::checkToken() or jexit( 'Invalid Token' );
    
		// Initialize variables
		$dbo	=& JFactory::getDBO();
		$user =& JFactory::getUser();
		$userid = $user->get('id');
		$gid = $user->get('gid');
		$eventid = JRequest::getVar('eventid');
		$itemid = JRequest::getVar('Itemid');
		
		//get author
    	$query = "SELECT created_by FROM #__thm_organizer_events WHERE eid = $eventid";
	  	$dbo->setQuery($query);
	  	$author = $dbo->loadResult();

		// Make sure user has the necessary access rights
		if ($gid < 19 || ($gid < 21 && $author != $userid))
		{
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}
		
		//get data from the request
		$model = $this->getModel('editevent');
   		if ($model->delete($eventid)) $msg = JText::_( 'Ereignis gel&ouml;scht' );
		else $msg = JText::_( 'Ein Fehler ist beim L&ouml;schen des Ereignisses aufgetretten.' );
		$eventlist = JRoute::_("index.php?option=com_thm_organizer&view=eventlist&Itemid=$itemid", false);
    	$this->setRedirect($eventlist, $msg);	
    }
    


	function cancelevent()
	{
            $itemid = JRequest::getVar('Itemid');
            $eventlist = JRoute::_("index.php?option=com_thm_organizer&view=eventlist&Itemid=$itemid", false);
            $this->setRedirect( $eventlist, JText::_("Aktion Abgebrochen").".");
	}
}