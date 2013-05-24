<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelDegree_Program
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Provides persistence handling for degree programs
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelDegree_Program extends JModel
{
	/**
	 * Method to save degree programs
	 *
	 * @return  Boolean
	 */
	public function save()
	{
		$dbo = JFactory::getDbo();
        $data = JRequest::getVar('jform', null, null, null, 4);
		$dbo->transactionStart();
        $table = JTable::getInstance('degree_programs', 'thm_organizerTable');
		$dpSuccess = $table->save($data);
		if ($dpSuccess)
		{
            $model = JModel::getInstance('mapping', 'THM_OrganizerModel');
            $mappingSuccess = $model->saveProgram($table->id);
            if ($mappingSuccess)
            {
                $dbo->transactionCommit();
                return true;
            }
		}
        $dbo->transactionRollback();
        return false;
	}

	/**
	 * Method to save existing degree programs as copies
	 *
	 * @return  Boolean
	 */
	public function save2copy()
	{
		$dbo = JFactory::getDbo();
        $data = JRequest::getVar('jform', null, null, null, 4);
        if (isset($data['id']))
        {
            unset($data['id']);
        }
		$dbo->transactionStart();
        $table = JTable::getInstance('degree_programs', 'thm_organizerTable');
		$dpSuccess = $table->save($data);
		if ($dpSuccess)
		{
            $model = JModel::getInstance('mapping', 'THM_OrganizerModel');
            $mappingSuccess = $model->saveProgram($table->id);
            if ($mappingSuccess)
            {
                $dbo->transactionCommit();
                return true;
            }
		}
        $dbo->transactionRollback();
        return false;
	}
	
	/**
	 * Method to delete one or more records. Due to foreign key references the
	 * associated module pools and pool to subject associations areautomatically deleted.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 */
	public function delete()
	{
		$programIDs = JRequest::getVar('cid', array(), 'post', 'array');
		
		$dbo = JFactory::getDbo();
		$dbo->transactionStart();
        $table = JTable::getInstance('degree_programs', 'thm_organizerTable');
		foreach ($programIDs as $programID)
		{
			$success = $table->delete($programID);
			if (!$success)
			{
				$dbo->transactionRollback();
				return false;
			}
		}
		$dbo->transactionCommit();
		return true;
	}
}
