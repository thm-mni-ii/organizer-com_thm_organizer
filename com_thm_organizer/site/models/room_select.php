<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelRoom_select
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
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
        $ipAddress = JFactory::getApplication()->input->server->get('REMOTE_ADDR', '');
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("r.longname AS name");
        $query->from("#__thm_organizer_monitors AS m");
        $query->innerJoin("#__thm_organizer_rooms AS r ON m.roomID = r.id");
        $query->where("ip = '$ipAddress'");
        $dbo->setQuery((string) $query);
        $room = $dbo->loadResult();
        if (isset($room) AND $room != '')
        {
            $application = JFactory::getApplication();
            $menuID = JFactory::getApplication()->input->getInt('Itemid');
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
