<?php
/**
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
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelcategory extends JModelLegacy
{
    /**
     * saves the event category
     *
     * @return bool true on success, otherwise false
     */
    public function save()
    {
        $data = JRequest::getVar('jform', null, null, null, 4);

        $dbo = $this->getDbo();
        $dbo->transactionStart();

        $category = JTable::getInstance('categories', 'thm_organizerTable');
        if (isset($data['id']) AND empty($data['id']))
        {
            unset($data['id']);
        }
        $data['description'] = $dbo->escape($data['description']);
        $success = $category->save($data);
        if (!$success)
        {
            $dbo->transactionRollback();
            return false;
        }
        else
        {
            $dbo->transactionCommit();
            return $category->id;
        }
    }

    /**
     * deletes event categories and associated events/event-resource associations
     *
     * @return bool true on success, otherwise false
     */
    public function delete()
    {
        $categoryIDs = JFactory::getApplication()->input->post->get('cid', array(0), 'array');
        if (count($categoryIDs))
        {
            $dbo = $this->getDbo();
            $dbo->transactionStart();

            // Remove events / event resources / content dependent upon this category
            $query = $dbo->getQuery(true);
            $query->select("DISTINCT (id)");
            $query->from("#__thm_organizer_events");
            $query->where("categoryID IN ( '" . implode("', '", $categoryIDs) . "' )");
            $dbo->setQuery((string) $query);
            
            try 
            {
                $eventIDs = $dbo->loadColumn();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_EVENT"), 500);
            }

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

            $category = JTable::getInstance('categories', 'thm_organizerTable');
            foreach ($categoryIDs as $categoryID)
            {
                $success = $category->delete($categoryID);
                if (!$success)
                {
                    $dbo->transactionRollback();
                    return false;
                }
            }
            $dbo->transactionCommit();
            return true;
        }
        return true;
    }
}
