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
require_once JPATH_COMPONENT_ADMINISTRATOR . '/assets/helpers/mapping.php';

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
     * Retrieves pool options for a given curriculum element
     * 
     * @return string
     */
    public function getOptions()
    {
        $isSubject = JRequest::getBool('subject');
        $programEntries = $this->getProgramEntries($isSubject);

        // Selected programs have not been mapped, should not happen
        if (empty($programEntries))
        {
            return '';
        }

        $programMappings = THM_OrganizerHelperMapping::getProgramMappings($programEntries);
        $programIDs = JRequest::getString('programID');
        $programIDArray = explode(',', $programIDs);

        /**
         * No program mappings or only programs have been mapped. Subjects
         * should not be mapped to programs.
         */
        if (empty($programMappings) OR (count($programIDArray) == count($programMappings) AND $isSubject))
        {
            return '';
        }
        $ownID = JRequest::getInt('ownID');
        $resourceID = $this->getResourceID($ownID, $isSubject);
        $parentIDs = array();
        $ownIDs = $isSubject? null : array();
        $mappings = $isSubject? null : array();
        THM_OrganizerHelperMapping::getMappingData($resourceID, $mappings, $parentIDs, $ownIDs);
        if (!$isSubject)
        {
            $children = THM_OrganizerHelperMapping::getChildren($mappings);
            $unwantedMappings = array_merge($ownIDs, $children);
        }
        $unwantedMappings = $isSubject? null : array_merge($ownIDs, $children);

        $options = array();
        $options[] = '<option value="-1">' . JText::_('COM_THM_ORGANIZER_POM_NO_PARENT') . '</option>';

        $this->fillOptions($options, $programMappings, $unwantedMappings, $parentIDs, $isSubject);
        return implode('', $options);
    }

    /**
     * Retrieves the mappings of superordinate programs
     * 
     * @return  array  the superordinate program mappings
     */
    private function getProgramEntries()
    {
        $programIDs = "'" . str_replace(",", "', '", JRequest::getString('programID')) . "'";

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, programID, lft, rgt');
        $query->from('#__thm_organizer_mappings');
        $query->where("programID IN ( $programIDs )");
        $query->order('lft ASC');
        $dbo->setQuery((string) $query);
        return $dbo->loadAssocList();
    }

    /**
     * Fills the options array with HTML pool options
     * 
     * @param   array    &$options           an array to store the options in
     * @param   array    &$programMappings   mappings belonging to one of the requested programs
     * @param   array    &$unwantedMappings  mappings which would lead to data inconsistency
     * @param   array    &$parentIDs         previously mapped parents
     * @param   boolean  $isSubject          whether the calling element is a subject
     * 
     * @return  void
     */
    private function fillOptions(&$options, &$programMappings, &$unwantedMappings, &$parentIDs, $isSubject)
    {
        $language = explode('-', JFactory::getLanguage()->getTag());
        foreach ($programMappings as $mapping)
        {
            if (!empty($mapping['subjectID'])
             OR (!empty($unwantedMappings) AND in_array($mapping['id'], $unwantedMappings)))
            {
                continue;
            }
            if (!empty($mapping['poolID']))
            {
                $options[] = THM_OrganizerHelperMapping::getPoolOption($mapping, $language, $parentIDs);
            }
            else
            {
                $options[] = THM_OrganizerHelperMapping::getProgramOption($mapping, $parentIDs, $isSubject);
            }
        }
    }

    /**
     * Retrieves the resource id
     * 
     * @param   int      $mappingID  the mapping id
     * @param   boolean  $isSubject  if the calling element is a subject
     * 
     * @return  int
     */
    private function getResourceID($mappingID, $isSubject)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select($isSubject? 'subjectID' : 'poolID');
        $query->from('#__thm_organizer_mappings');
        $query->where("id = '$mappingID'");
        $dbo->setQuery((string) $query);
        return $dbo->loadResult();
    }
}
