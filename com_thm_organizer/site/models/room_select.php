<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        room select model
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.modelform');
class thm_organizerModelroom_select extends JModelForm
{
    function __construct()
    {
        $this->checkIP();
        parent::__construct();
        $this->getForm();
    }

    /**
     * Checks if the clients ip matches one of those stored in the db, and
     * redirects directly to room display view if the ip is stored
     *
     * @access private
     */
    private function checkIP()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("r.name AS name");
        $query->from("#__thm_organizer_monitors AS m");
        $query->innerJoin("#__thm_organizer_rooms AS r ON m.roomID = r.id");
        $query->where("ip = '$ip'");
        $dbo->setQuery((string)$query);
        $room = $dbo->loadResult();
        if(isset($room) && $room != '')
        {
            $application = JFactory::getApplication();
            $menuID = JRequest::getInt('Itemid');
            $rd_string = 'index.php?option=com_thm_organizer&view=room_display';
            $rd_string .= "&room=$room&template=thm_organizer_infoscreen&Itemid=$menuID";
            $application->redirect($rd_string);
        }
    }

    /**
     * Method to get the record form.
     *
     * @param array   $data Data for the form.
     * @param boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return mixed A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_thm_organizer.room_select', 'room_select',
                                array('control' => 'jform', 'load_data' => $loadData));
        if(empty($form)) return false;
        return $form;
    }
}