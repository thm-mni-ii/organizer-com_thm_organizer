<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelEvent_Edit
 * @description create/edit appointment/event model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('thm_core.edit.model');

/**
 * Retrieves persistent data for output in the event edit view.
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelEvent_Edit extends THM_CoreModelEdit
{
    public $event = null;

    public $categories = null;

    public $eventLink = "";

    public $listLink = "";

    /**
     * calls functions to set model data
     */
    public function __construct()
    {
        parent::__construct();
        //$this->setLinks();
    }

    /**
     * Sets links if the item id belongs to a menu type of event manager and/or if the
     * event is not new.
     *
     * @return void  sets object variables
     */
    private function setLinks()
    {
        $app = JFactory::getApplication();
        $menuID = $app->input->getInt('Itemid', 0);
        $eventID = $this->getForm()->getValue('id', 0);
        if ($eventID)
        {
            $eventLink = "index.php?option=com_thm_organizer&view=event_details&eventID=$eventID";
            $eventLink .= empty($menuID)? '' : "&Itemid=$menuID";
            $this->eventLink = JRoute::_($eventLink);
        }

        $dbo = JFactory::getDbo();

        $query = $dbo->getQuery(true);
        $query->select("link");
        $query->from("#__menu AS eg");
        $query->where("id = '$menuID''");
        $query->where("link LIKE '%event_manager%'");
        $dbo->setQuery((string) $query);
        
        try
        {
            $result = $dbo->loadResult();
            $this->listLink = empty($result)? '' : JRoute::_($result);
        }
        catch (Exception $exc)
        {
            $app->enqueueMessage($exc->getMessage(), 'error');
            $this->listLink = '';
        }
    }
}
