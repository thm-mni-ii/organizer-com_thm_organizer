<?php
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
/**
 * RoomList Model
 *
 */
class GiessenSchedulerModelRoomList extends JModel
{
    var $rooms = null;

    /**
     * Constructor
     *
     * @since 1.5
     */
    function __construct()
    {
        parent::__construct();
        $this->checkIP();
    }

    /**
     * Checks if the clients ip matches one of those stored in the db, and
     * redirects directly to roomdisplay view if the ip is stored
     *
     * @return true if the ip was not found (the ip is not registered at the server)
     */
    function checkIP()
    {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
        $dbo = & JFactory::getDBO();
        $query = "SELECT room FROM #__thm_organizer_roomip WHERE ip = '$ipaddress'";
        $dbo->setQuery( $query );
        $result = $dbo->query();
        $room = $dbo->loadResult();
        if(isset($room) && $room != '')
        {
            $app =& JFactory::getApplication();
            $rd_string = 'index.php?option=com_thm_organizer&view=roomdisplay';
            $rd_string .= '&room='.$room.'&template=giessenstyleroomdisplay';
            $app->redirect($rd_string);
        }
        else $this->getRooms();
    }

    /**
     * Retrieves a list of rooms
     *
     * @return
     */
    function getRooms()
    {
        $dbo =& JFactory::getDBO();
        $query = "SELECT room FROM #__thm_organizer_roomip ORDER BY room";
        $dbo->setQuery( $query );
        //joomla requires objects in most library functions even where arrays
        //would have been sufficient
        $rooms = $dbo->loadObjectList();
        $this->rooms = $rooms;
    }
}