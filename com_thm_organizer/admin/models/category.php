<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        category model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_SITE . '/components/com_thm_organizer/models/events.php';

/**
 * Class storing/deleting category item information 
 * 
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerModelcategory extends JModel
{
    /**
     * saves the event category
     *
     * @return bool true on success, otherwise false
     */
    public function save()
    {
        $dbo = $this->getDbo();
        $data = JRequest::getVar('jform', null, null, null, 4);
        $category = JTable::getInstance('categories', 'thm_organizerTable');
        if (isset($data['id']) and empty($data['id']))
        {
            unset($data['id']);
        }
        elseif (!empty($data['id']))
        {
            $success = $category->load($data['id']);
            if (!$success)
            {
                return $success;
            }
        }
        $data['description'] = $dbo->escape($data['description']);
        $success = $category->save($data);
        return $success;
    }

    /**
     * deletes event categories and associated events/event-resource associations
     *
     * @return bool true on success, otherwise false
     */
    public function delete()
    {
        $categoryIDs = JRequest::getVar('cid', array(0), 'post', 'array');
        if (count($categoryIDs))
        {
            $dbo = $this->getDbo();
            $dbo->transactionStart();

            // Remove events / event resources / content dependant upon this category
            $query = $dbo->getQuery(true);
            $query->select("DISTINCT (id)");
            $query->from("#__thm_organizer_events");
            $query->where("categoryID IN ( '" . implode("', '", $categoryIDs) . "' )");
            $dbo->setQuery((string) $query);
            $eventIDs = $dbo->loadResultArray();
            if (count($eventIDs))
            {
                $eventsModel = new THM_OrganizerModelevents;
                foreach ($eventIDs as $eventID)
                {
                    $success = $eventsModel->delete($eventID);
                    if (!$success)
                    {
                        $dbo->transactionRollback();
                        return false;
                    }
                }
            }

            $category = JTable::getInstance('schedules', 'thm_organizerTable');
            foreach ($categoryIDs as $categoryID)
            {
                $success = $category->delete($categoryID);
                if (!$success)
                {
                    $dbo->transactionRollback();
                    return false;
                }
            }
            return true;
        }
        return true;
    }
}
