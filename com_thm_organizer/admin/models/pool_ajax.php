<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPool_Ajax
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Class provides methods to retrieve data for pool ajax calls
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPool_Ajax extends JModel
{
    /**
     * Retrieves the parent ids of the resource in question
     * 
     * @return  array  an array of integer values
     */
    public function getParentIDs()
    {
        $ownID = JRequest::getInt('ownID');
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('parentID')->from('#__thm_organizer_mappings')->where("poolID = '$ownID'");
        $dbo->setQuery((string) $query);
        return $dbo->loadResultArray();
    }

    /**
     * Creates a list of pool options dependent upon the chosen degree program
     * 
     * @return  array  contains arrays with id and name of program pools
     */
    public function getProgramPools()
    {
        $ownID = JRequest::getInt('ownID');
        $programIDs = "'" . str_replace(",", "', '", JRequest::getString('programID')) . "'";

        $dbo = JFactory::getDbo();

        $IDQuery = $dbo->getQuery(true);
        $IDQuery->select('id')->from('#__thm_organizer_mappings')->where("poolID = '$ownID'");
        $dbo->setQuery((string) $IDQuery);
        $ownIDs = $dbo->loadResultArray();
        
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
                        $indent .= "&nbsp;&nbsp;&nbsp;";
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
        
        return  array_merge($selectPools, empty($programMappings)? array() : $programMappings);
    }
}
