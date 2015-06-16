<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelDepartment
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class THM_OrganizerModelField for component com_thm_organizer
 * Class provides methods to deal with color
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelDepartment extends JModelLegacy
{
    /**
     * Attempts to save the form data
     *
     * @return mixed  int department id on success, otherwise false
     */
    public function save()
    {
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');

        $this->_db->transactionStart();
        $department = JTable::getInstance('departments', 'thm_organizerTable');
        try
        {
            $deptSuccess = $department->save($data);
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            $this->_db->transactionRollback();
            return false;
        }

        if (!$deptSuccess)
        {
            $this->_db->transactionRollback();
            return false;
        }

        return $department->id;
    }

    /**
     * Attempts to save altered form data as a new entry
     *
     * @return mixed  int department id on success, otherwise false
     */
    public function save2copy()
    {
        $data = JFactory::getApplication()->input->get('jform', array(), 'array');
        if (isset($data['id']))
        {
            unset($data['id']);
        }

        $this->_db->transactionStart();
        $department = JTable::getInstance('departments', 'thm_organizerTable');
        try
        {
            $deptSuccess = $department->save($data);
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            $this->_db->transactionRollback();
            return false;
        }

        if (!$deptSuccess)
        {
            $this->_db->transactionRollback();
            return false;
        }

        return $department->id;
    }

    /**
     * Removes departments entries from the database
     *
     * @return  boolean true on success, otherwise false
     */
    public function delete()
    {
        return THM_OrganizerHelper::delete('departments');
    }
}
