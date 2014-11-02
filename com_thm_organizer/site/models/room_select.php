<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelRoom_select
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.modelform');

/**
 * Retrieves data for room selection
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelRoom_Select extends JModelForm
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->checkIP();
        parent::__construct();
        $this->getForm();
    }

    /**
     * Checks if the clients ip matches one of those stored in the db, and
     * redirects directly to room display view if the ip is stored
     *
     * @return void
     */
    private function checkIP()
    {
        $ipAddress = JRequest::getVar('REMOTE_ADDR', '', 'SERVER');
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("r.longname AS name");
        $query->from("#__thm_organizer_monitors AS m");
        $query->innerJoin("#__thm_organizer_rooms AS r ON m.roomID = r.id");
        $query->where("ip = '$ipAddress'");
        $dbo->setQuery((string) $query);
        
        try 
        {
        $room = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        if (isset($room) AND $room != '')
        {
            $application = JFactory::getApplication();
            $menuID = JRequest::getInt('Itemid');
            $rd_string = 'index.php?option=com_thm_organizer&view=room_display';
            $rd_string .= "&room=$room&tmpl=component&Itemid=$menuID";
            $application->redirect($rd_string);
        }
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed A JForm object on success, false on failure
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_thm_organizer.room_select',
                                'room_select',
                                array('control' => 'jform', 'load_data' => $loadData)
                               );
        if (empty($form))
        {
            return false;
        }
        return $form;
    }
}
