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
    public function poolDegreeOptions()
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

        $noProgramsMappings = empty($programMappings);
        $noValidMappings = (count($programIDArray) == count($programMappings)) AND $isSubject;
        $dontOfferOptions = $noProgramsMappings OR $noValidMappings;
        if ($dontOfferOptions)
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

        $query = $this->_db->getQuery(true);
        $query->select('id, programID, lft, rgt');
        $query->from('#__thm_organizer_mappings');
        $query->where("programID IN ( $programIDs )");
        $query->order('lft ASC');
        $this->_db->setQuery((string) $query);
        return $this->_db->loadAssocList();
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
        $query = $this->_db->getQuery(true);
        $query->select($isSubject? 'subjectID' : 'poolID');
        $query->from('#__thm_organizer_mappings');
        $query->where("id = '$mappingID'");
        $this->_db->setQuery((string) $query);
        return $this->_db->loadResult();
    }

    /**
     * Retrieves pool entries from the database based upon selected program and
     * teacher
     *
     * @return  string  the subjects which fit the selected resource
     */
    public function poolsByProgramOrTeacher()
    {
        $input = JFactory::getApplication()->input;
        $selectedProgram = $input->getInt('programID');
        if (empty($selectedProgram) OR $selectedProgram == '-1')
        {
            return '[]';
        }

        $programBounds = THM_OrganizerHelperMapping::getBoundaries('program', $selectedProgram);
        $teacherClauses = THM_OrganizerHelperMapping::getTeacherMappingClauses();

        if (empty($programBounds))
        {
            return '[]';
        }

        $lang = explode('-', JFactory::getLanguage()->getTag());
        $query = $this->_db->getQuery(true);
        $query->select("p.id, p.name_{$lang[0]} AS name, m.level");
        $query->from('#__thm_organizer_pools AS p');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');
        if (!empty($programBounds))
        {
            $query->where("m.lft >= '{$programBounds['lft']}'");
            $query->where("m.rgt <= '{$programBounds['rgt']}'");
        }
        if (!empty($teacherClauses))
        {
            $query->where("( ( " . implode(') OR (', $teacherClauses) . ") )");
        }
        $query->order('lft');
        $this->_db->setQuery((string) $query);
        $pools = $this->_db->loadObjectList();

        if (empty($pools))
        {
            return '[]';
        }

        foreach ($pools AS $key => $value)
        {
            $pools[$key]->name  = THM_OrganizerHelperMapping::getIndentedPoolName($value->name, $value->level, false);
        }
        return json_encode($pools);
    }
}
