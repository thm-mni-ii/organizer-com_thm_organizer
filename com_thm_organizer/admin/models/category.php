<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        category model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_SITE . '/components/com_thm_organizer/models/event.php';

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
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');

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
        $categoryIDs = JFactory::getApplication()->input->get('cid', array(), 'array');
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
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }

            if (count($eventIDs))
            {
                $eventsModel = new THM_OrganizerModelEvent;
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

    /**
     * Toggles the user's association with a role
     *
     * @return  boolean  true on success, otherwise false
     */
    public function toggle()
    {
        $input = JFactory::getApplication()->input;
        $categoryID = $input->getInt('id', 0);
        if (empty($categoryID))
        {
            return false;
        }

        $attribute = $input->getString('attribute', '');
        if (empty($attribute))
        {
            return false;
        }

        $value = $input->getInt('value', 1)? 0 : 1;

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_categories');
        $query->set("$attribute = '$value'");
        $query->where("id = '$categoryID'");
        $this->_db->setQuery((string) $query);
        try
        {
            return (bool) $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }
    }
}
