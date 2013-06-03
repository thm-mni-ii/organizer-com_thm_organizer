<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSubject
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Class THM_OrganizerModelSubject for component com_thm_organizer
 * Class provides methods to deal with asset
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPool extends JModel
{
 	/**
	 * Saves
	 *
	 * @return  mixed  integer on successful pool creation, otherwise boolean
     *                 true/false on success/failure
	 */
	public function save()
	{
        $data = JRequest::getVar('jform', null, null, null, 4);
        $table = JTable::getInstance('pools', 'thm_organizerTable');
        
        $dbo = JFactory::getDbo();
        $dbo->transactionStart();

        $success = $table->save($data);

        // Successfully inserted a new pool
        if ($success AND empty($data['id']))
        {
            $dbo->transactionCommit();
            return $table->id;
        }
        
        // New pool unsuccessfully inserted
        elseif (empty($data['id']))
        {
            $dbo->transactionRollback();
            return false; 
        }
        
        // Process mapping information
        else
        {
            $model = JModel::getInstance('mapping', 'THM_OrganizerModel');

            // No mappings desired
            if (empty($data['parentID']))
            {
                $mappingsDeleted = $model->deleteByResourceID($table->id, 'pool');
                if ($mappingsDeleted)
                {
                    $dbo->transactionCommit();
                    return $table->id;
                }
                else
                {
                    $dbo->transactionRollback();
                    return false;
                }
            }
            else
            {
                $mappingSaved = $model->savePool($data);
                if ($mappingSaved)
                {
                    $dbo->transactionCommit();
                    return $table->id;
                }
                else
                {
                    $dbo->transactionRollback();
                    return false;
                }
            }
        }
	}

    /**
     * Attempts to delete the selected subject entries
     *
     * @return  boolean true on success, otherwise false
     */
    public function delete()
    {
        $success = true;
        $poolIDs = JRequest::getVar('cid', array(0), 'post', 'array');
        $table = JTable::getInstance('pools', 'thm_organizerTable');
        if (!empty($poolIDs))
        {
            $dbo = JFactory::getDbo();
            $dbo->transactionStart();
            foreach ($poolIDs as $poolID)
            {
                $success = $table->delete($poolID);
                if (!$success)
                {
                    $dbo->transactionRollback();
                    return $success;
                }
            }
            $dbo->transactionCommit();
        }
        return $success;
    }
}
