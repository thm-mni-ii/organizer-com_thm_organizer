<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelUser
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class handling data management for THM Organizer users.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerModelUser extends JModelLegacy
{
    /**
     * Adds the selected users to THM Organizer
     *
     * @return  bool  true on success, otherwise false
     */
    public function add()
    {
        $input = JFactory::getApplication()->input;
        $userIDs = $input->get('cid', array(), 'array');
        if (empty($userIDs))
        {
            return true;
        }

        $query = $this->_db->getQuery(true);
        $query->insert('#__thm_organizer_users')->columns('userID');
        foreach ($userIDs as $userID)
        {
            $query->clear('values');
            $query->values($userID);
            $this->_db->setQuery((string) $query);
            try
            {
                $this->_db->execute();
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return false;
            }
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
        $userID = $input->getInt('id', 0);
        if (empty($userID))
        {
            return false;
        }
        $role = $input->getString('attribute', '');
        if (empty($role) OR !in_array($role, array('program_manager', 'planner')))
        {
            return false;
        }
        $oldValue = $input->getInt('value', 1);
        $newValue = empty($oldValue)? 1 : 0;

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_users');
        $query->set("$role = '$newValue'");
        $query->where("userid = '$userID'");
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

    /**
     * Removes the selected users from THM Organizer
     *
     * @return  bool  true on success, otherwise false
     */
    public function delete()
    {
        $input = JFactory::getApplication()->input;
        $userIDs = $input->get('cid', array(), 'array');
        $query = $this->_db->getQuery(true);
        $query->delete('#__thm_organizer_users');
        foreach ($userIDs as $userID)
        {
            $query->clear('where');
            $query->where("userid = '$userID'");
            $this->_db->setQuery((string) $query);
            try
            {
                $this->_db->execute();
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return false;
            }
        }
        return true;
    }
}
