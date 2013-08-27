<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPool
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper' . DS . 'lsfapi.php';

/**
 * Provides persistence handling for subject pools
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPool extends JModel
{
    /**
     * Attempts to delete the selected subject pool entries and related mappings
     *
     * @return  boolean true on success, otherwise false
     */
    public function delete()
    {
        $resourceIDs = JRequest::getVar('cid', array(0), 'post', 'array');
        if (!empty($resourceIDs))
        {
            $dbo = JFactory::getDbo();
            $dbo->transactionStart();
            $table = JTable::getInstance('pools', 'thm_organizerTable');
            $model = JModel::getInstance('mapping', 'THM_OrganizerModel');
            foreach ($resourceIDs as $resourceID)
            {
                $mappingsDeleted = $model->deleteByResourceID($resourceID, 'pool');
                if (!$mappingsDeleted)
                {
                    $dbo->transactionRollback();
                    return false;
                }

                $resourceDeleted = $table->delete($resourceID);
                if (!$resourceDeleted)
                {
                    $dbo->transactionRollback();
                    return false;
                }
            }
            $dbo->transactionCommit();
        }
        return true;
    }

    /**
     * Creates a pool entry if none exists and calls
     * 
     * @param   object  &$stub  a simplexml object containing rudimentary subject data
     * 
     * @return  mixed  int value of subject id on success, otherwise false
     */
    public function processLSFStub(&$stub)
    {
        if (empty($stub->pordid) OR empty($stub->nrhis))
        {
            return false;
        }

        $table = JTable::getInstance('pools', 'thm_organizerTable');
        $table->load(array('lsfID' => $stub->pordid));

        $data = array();
        $data['lsfID'] = (string) $stub->pordid;
        $data['hisID'] = (string) $stub->nrhis;
        $data['externalID'] = (string) $stub->alphaid;
        $data['abbreviation_de'] = (string) $stub->kuerzel;
        $data['abbreviation_en'] = (string) $stub->kuerzelen;
        $data['short_name_de'] = (string) $stub->kurzname;
        $data['short_name_en'] = (string) $stub->kurznameen;
        $data['name_de'] = (string) $stub->titelde;
        $data['name_en'] = (string) $stub->titelen;

        if (empty($data['abbreviation_en']))
        {
            $data['abbreviation_en'] = $data['abbreviation_de'];
        }
        if (empty($data['short_name_en']))
        {
            $data['short_name_en'] = $data['short_name_de'];
        }
        if (empty($data['name_en']))
        {
            $data['name_en'] = $data['name_de'];
        }

        $stubSaved = $table->save($data);
        if (!$stubSaved)
        {
            return false;
        }

        if (isset($stub->modulliste->modul))
        {
            $subjectModel = JModel::getInstance('subject', 'THM_OrganizerModel');
            foreach ($stub->modulliste->modul as $subjectStub)
            {
                $subjectProcessed = $subjectModel->processLSFStub($subjectStub);
                if (!$subjectProcessed)
                {
                    return false;
                }
            }
        }
        return true;
    }

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

}
