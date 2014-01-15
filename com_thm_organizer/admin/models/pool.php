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

/**
 * Provides persistence handling for subject pools
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPool extends JModel
{
    private $_scheduleModel = null;

    /**
     * Attempts to delete the selected subject pool entries and related mappings
     *
     * @return  boolean true on success, otherwise false
     */
    public function delete()
    {
        $poolIDs = JRequest::getVar('cid', array(0), 'post', 'array');
        if (!empty($poolIDs))
        {
            $this->_db->transactionStart();
            foreach ($poolIDs as $poolID)
            {
                $deleted = $this->deleteEntry($poolID);
                if (!$deleted)
                {
                    $this->_db->transactionRollback();
                    return false;
                }
            }
            $this->_db->transactionCommit();
        }
        return true;
    }

    /**
     * Removes a single pool and mappings
     * 
     * @param   int  $poolID  the pool id
     * 
     * @return  boolean  true on success, otherwise false
     */
    public function deleteEntry($poolID)
    {
        $table = JTable::getInstance('pools', 'thm_organizerTable');
        $model = JModel::getInstance('mapping', 'THM_OrganizerModel');
        $mappingsDeleted = $model->deleteByResourceID($poolID, 'pool');
        if (!$mappingsDeleted)
        {
            return false;
        }

        $poolDeleted = $table->delete($poolID);
        if (!$poolDeleted)
        {
            return false;
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
 
        $this->_db->transactionStart();

        $success = $table->save($data);

        // Successfully inserted a new pool
        if ($success AND empty($data['id']))
        {
            $this->_db->transactionCommit();
            return $table->id;
        }
 
        // New pool unsuccessfully inserted
        elseif (empty($data['id']))
        {
            $this->_db->transactionRollback();
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
                    $this->_db->transactionCommit();
                    return $table->id;
                }
                else
                {
                    $this->_db->transactionRollback();
                    return false;
                }
            }
            else
            {
                $mappingSaved = $model->savePool($data);
                if ($mappingSaved)
                {   
                    $this->_db->transactionCommit();
                    return $table->id;
                }
                else
                {
                    $this->_db->transactionRollback();
                    return false;
                }
            }
        }
    }

    /**
     * Checks whether pool nodes have the expected structure and required
     * information
     *
     * @param   object  &$scheduleModel  the validating schedule model
     * @param   object  &$poolNode       the pool node to be validated
     *
     * @return void
     */
    public function validate(&$scheduleModel, &$poolNode)
    {
        $this->_scheduleModel = $scheduleModel;

        $gpuntisID = $this->validateGPUntisID($poolNode);
        if (empty($gpuntisID))
        {
            return;
        }

        $poolID = str_replace('CL_', '', $gpuntisID);
        $this->_scheduleModel->schedule->pools->$poolID = new stdClass;
        $this->_scheduleModel->schedule->pools->$poolID->gpuntisID = $gpuntisID;
        $this->_scheduleModel->schedule->pools->$poolID->name = $poolID;
        $this->_scheduleModel->schedule->pools->$poolID->localUntisID = str_replace('CL_', '', trim((string) $poolNode[0]['id']));

        $longname = trim((string) $poolNode->longname);
        if (empty($longname))
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_CL_LN_MISSING', $poolID);
            return;
        }
        $this->_scheduleModel->schedule->pools->$poolID->longname = $poolID;

        $degreeID = $this->validateDegree($poolNode, $longname, $poolID);
        if (empty($degreeID))
        {
            return;
        }
        $this->_scheduleModel->schedule->pools->$poolID->degree = $degreeID;

        $warningString = '';
        $this->validateRestriction($poolNode, $poolID, $warningString);
        $this->validateField($poolNode, $poolID, $warningString);

        if (!empty($warningString))
        {
            $this->_scheduleModel->scheduleWarnings[]
                = JText::sprintf('COM_THM_ORGANIZER_CL_FIELD_MISSING', $longname, $poolID, $warningString);
        }
    }

    /**
     * Validates the pools's gp untis id
     * 
     * @param   object  &$poolNode  the pool node object
     * 
     * @return  mixed  string id if valid, otherwise false
     */
    private function validateGPUntisID(&$poolNode)
    {
        $externalName = trim((string) $poolNode->external_name);
        $internalName = trim((string) $poolNode[0]['id']);
        $gpuntisID = empty($externalName)? $internalName : $externalName;
        if (empty($gpuntisID))
        {
            if (!in_array(JText::_("COM_THM_ORGANIZER_CL_ID_MISSING"), $this->_scheduleModel->scheduleErrors))
            {
                $this->_scheduleModel->scheduleErrors[] = JText::_("COM_THM_ORGANIZER_CL_ID_MISSING");
            }
            return false;
        }
        return $gpuntisID;
    }

    /**
     * Validates the pools's degree
     * 
     * @param   object  &$poolNode  the pool node object
     * @param   string  $longname   the name of the pool
     * @param   string  $poolID     the pool's id
     * 
     * @return  mixed  string longname if valid, otherwise false
     */
    private function validateDegree(&$poolNode, $longname, $poolID)
    {
        $degreeID = str_replace('DP_', '', trim((string) $poolNode->class_department[0]['id']));
        if (empty($degreeID))
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_CL_DEGREE_MISSING', $longname, $poolID);
            return false;
        }
        elseif (empty($this->_scheduleModel->schedule->degrees->$degreeID))
        {
            $this->_scheduleModel->scheduleErrors[] = JText::sprintf('COM_THM_ORGANIZER_CL_DEGREE_LACKING', $longname, $poolID, $degreeID);
            return false;
        }
        return $degreeID;
    }

    /**
     * Validates the pool's restriction (classlevel) attribute
     * 
     * @param   object  &$poolNode       the pool node object
     * @param   string  $poolID          the pool's id
     * @param   string  &$warningString  a string listing inconsistent fields
     * 
     * @return  void
     */
    private function validateRestriction(&$poolNode, $poolID, &$warningString)
    {
        $restriction = trim((string) $poolNode->classlevel);
        if (empty($restriction))
        {
            $warningString .= JText::_('COM_THM_ORGANIZER_RESTRICTION');
        }
        $this->_scheduleModel->schedule->pools->$poolID->restriction = empty($restriction)? '' : $restriction;
    }

    /**
     * Validates the pool's field (description) attribute
     * 
     * @param   object  &$poolNode       the pool node object
     * @param   string  $poolID          the pool's id
     * @param   string  &$warningString  a string listing inconsistent fields
     * 
     * @return  void
     */
    private function validateField(&$poolNode, $poolID, &$warningString)
    {
        $descriptionID = str_replace('DS_', '', trim((string) $poolNode->class_description[0]['id']));
        if (empty($descriptionID)
         OR empty($this->_scheduleModel->schedule->fields->$descriptionID))
        {
            $warningString .= empty($warningString)? '' : ', ';
            $warningString .= JText::_('COM_THM_ORGANIZER_DESCRIPTION_PROPERTY');
        }
        $this->_scheduleModel->schedule->pools->$poolID->description = empty($descriptionID)? '' : $descriptionID;
    }
}
