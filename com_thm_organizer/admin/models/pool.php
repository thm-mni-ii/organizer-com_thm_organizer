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
        if ((empty($stub->pordid) OR empty($stub->nrhis))
         AND (empty($stub->modulid) OR empty($stub->modulnrhis)))
        {
            return false;
        }
        $lsfID = empty($stub->pordid)? (string) $stub->modulid: (string) $stub->pordid;
        $hisID = empty($stub->nrhis)? (string) $stub->modulnrhis : (string) $stub->nrhis;

        $table = JTable::getInstance('pools', 'thm_organizerTable');
        $table->load(array('lsfID' => $lsfID, 'hisID' => $hisID));

        $data = array();
        $data['lsfID'] = $lsfID;
        $data['hisID'] = $hisID;
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
            foreach ($stub->modulliste->modul as $subStub)
            {
                if (isset($subStub->modulliste->modul))
                {
                    $stubProcessed = $this->processLSFStub($subStub);
                }
                else
                {
                    $stubProcessed = $subjectModel->processLSFStub($subStub);
                }
                if (!$stubProcessed)
                {echo "<pre>" . print_r($subStub, true) . "</pre>"; die;
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
