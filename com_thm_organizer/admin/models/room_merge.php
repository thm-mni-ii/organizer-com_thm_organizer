<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelRoom_Merge
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Loads room entry information to be merged
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelRoom_Merge extends JModelLegacy
{
    /**
     * Array holding room entry information
     *
     * @var array
     */
    public $roomInformation = null;

    /**
     * Pulls a list of room data from the database
     *
     * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $select = "r.id, r.gpuntisID, r.name, r.longname, r.typeID, ";
        $parts = array("t.type","', '", "t.subtype");
        $select .= $query->concatenate($parts, "") . " AS type";
        $query->select($select);
        $query->from('#__thm_organizer_rooms AS r');
        $query->leftJoin('#__thm_organizer_room_types AS t ON r.typeID = t.id');

        $cids = JFactory::getApplication()->input->get('cid', array(), 'array');
        $selectedRooms = "'" . implode("', '", $cids) . "'";
        $query->where("r.id IN ( $selectedRooms )");

        $query->order('r.id ASC');

        $dbo->setQuery((string) $query);
        
        try 
        {
            $this->roomInformation = $dbo->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
    }
}
