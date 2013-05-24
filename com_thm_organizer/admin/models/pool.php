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
     * Creates a list of pool options dependent upon the chosen degree program
     * 
     * @return  string  string containing a list of pools
     */
    public function byDegree()
    {
        $requestedPrograms = JRequest::getString('programID');
        if (empty($requestedPrograms))
        {
            return '';
        }
        $ownID = JRequest::getInt('ownID');
        $programIDs = "'" . str_replace(",", "', '", $requestedPrograms) . "'";

        $dbo = JFactory::getDbo();

        $parentIDQuery = $dbo->getQuery(true);
        $parentIDQuery->select('id, parentID')->from('#__thm_organizer_mappings')->where("poolID = '$ownID'");
        $dbo->setQuery((string) $parentIDQuery);
        $ownIDs = $dbo->loadResultArray();
        $parentIDs = $dbo->loadResultArray(1);
        
        $bordersQuery = $dbo->getQuery(true);
        $bordersQuery->select('DISTINCT lft, rgt');
        $bordersQuery->from('#__thm_organizer_mappings');
        $bordersQuery->where("programID IN ( $programIDs )");
        $bordersQuery->order('lft ASC');
        $dbo->setQuery((string) $bordersQuery);
        $borders = $dbo->loadAssocList();
        
        $programMappings = array();
        $programMappingsQuery = $dbo->getQuery(true);
        $programMappingsQuery->select('*');
        $programMappingsQuery->from('#__thm_organizer_mappings');
        foreach ($borders as $border)
        {
            $programMappingsQuery->clear('where');
            $programMappingsQuery->where("lft >= '{$border['lft']}'");
            $programMappingsQuery->where("rgt <= '{$border['rgt']}'");
            $programMappingsQuery->order('lft ASC');
            $dbo->setQuery((string) $programMappingsQuery);
            $results = $dbo->loadAssocList();
            $programMappings = array_merge($programMappings, empty($results)? array() : $results);
        }

        $language = explode('-', JFactory::getLanguage()->getTag());
        $poolsTable = JTable::getInstance('pools', 'THM_OrganizerTable');
        foreach ($programMappings as $key => $mapping)
        {
            if (in_array($mapping['id'], $ownIDs))
            {
                unset($programMappings[$key]);
                continue;
            }
            if (!empty($mapping['poolID']))
            {
                $poolsTable->load($mapping['poolID']);
                $name = $language[0] == 'de'? $poolsTable->name_de : $poolsTable->name_en;
                
                $level = 0;
                if ($mapping['level'] != 0)
                {
                    $indent = '';
                    while ($level < $mapping['level'])
                    {
                        $indent .= "   ";
                        $level++;
                    }
                    $programMappings[$key]['name'] = $indent . "|_" . $name;
                }
            }
            else
            {
                $programNameQuery = $dbo->getQuery(true);
                $programNameQuery->select(" CONCAT( dp.subject, ', (', d.abbreviation, ' ', dp.version, ')') AS name");
                $programNameQuery->from('#__thm_organizer_degree_programs AS dp');
                $programNameQuery->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
                $programNameQuery->where("dp.id = '{$mapping['programID']}'");
                $dbo->setQuery((string) $programNameQuery);
                $programMappings[$key]['name'] = $dbo->loadResult();
            }
        }

        $selectPools = array();
        $selectPools[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_POM_SEARCH_PARENT'));
        $selectPools[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_POM_NO_PARENT'));
        
        $optionPools = array_merge($selectPools, empty($programMappings)? array() : $programMappings);
        return JHTML::_('select.options', $optionPools, 'id', 'name', $parentIDs);
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
                $poolSaved = $model->savePool($data);
                if ($poolSaved)
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
