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
class THM_OrganizerModelPool_Ajax extends JModelLegacy
{
    /**
     * Retrieves pool options for a given curriculum element
     *
     * @return  string
     */
    public function poolDegreeOptions()
    {
        $input = JFactory::getApplication()->input;
        $isSubject = $input->getBool('subject', false);
        $ownID = $input->getInt('ownID', 0);
        $programEntries = $this->getProgramEntries($isSubject);

        // Called from a new resource or the selected programs have not been mapped
        $callerInvalid = (empty($ownID) OR empty($programEntries));
        if ($callerInvalid)
        {
            return '';
        }

        $programMappings = THM_OrganizerHelperMapping::getProgramMappings($programEntries);
        $programIDs = $input->getString('programID', '');
        $programIDArray = explode(',', $programIDs);

        $offerOptions = $this->offerOptions($programMappings, $programIDArray, $isSubject);
        if (!$offerOptions)
        {
            return '';
        }

        $parentIDs = $ownIDs = $mappings = array();
        THM_OrganizerHelperMapping::getMappingData($ownID, $mappings, $parentIDs, $ownIDs);
        $unSelectableMappings = $this->getUnselectableMappings($isSubject, $mappings, $ownIDs);

        $options = array();
        $options[] = '<option value="-1">' . JText::_('COM_THM_ORGANIZER_POM_NO_PARENT') . '</option>';

        $language = $input->getString('languageTag', 'de');
        $this->fillOptions($options, $programMappings, $unSelectableMappings, $parentIDs, $isSubject, $language);
        return implode('', $options);
    }

    /**
     * Retrieves the mappings of superordinate programs
     *
     * @return  array  the superordinate program mappings
     */
    private function getProgramEntries()
    {
        $programIDs = "'" . str_replace(",", "', '", JFactory::getApplication()->input->getString('programID', '0')) . "'";

        $query = $this->_db->getQuery(true);
        $query->select('id, programID, lft, rgt');
        $query->from('#__thm_organizer_mappings');
        $query->where("programID IN ( $programIDs )");
        $query->order('lft ASC');
        $this->_db->setQuery((string) $query);
        
        try
        {
            $programEntries = $this->_db->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_PROGRAM_ENTRIES"), 500);
        }
        
        return $programEntries;
    }

    /**
     * Retrieves an array of mappings which should not be available for selection
     * as the parent of the resource
     * 
     * @param   boolean  $isSubject  whether or not the resource is a subject
     * @param   array    &$mappings  the existing mappings of the resource
     * @param   array    &$ownIDs    the mapping ids for the resource
     * 
     * @return  array  the ids which should be unselectable
     */
    private function getUnselectableMappings($isSubject, &$mappings, &$ownIDs)
    {
        if ($isSubject)
        {
            return array();
        }
        $children = THM_OrganizerHelperMapping::getChildren($mappings);
        return array_merge($ownIDs, $children);
    }

    /**
     * Determines whether association options should be offered
     * 
     * @param   array    &$programMappings  the program mappings retrieved
     * @param   array    &$programIDArray   the requested program ids
     * @param   boolean  $isSubject         whether or not the request was sent
     *                                      from the subject edit view
     * 
     * @return  boolean  true if association options should be offered, otherwise
     *                   false
     */
    private function offerOptions(&$programMappings, &$programIDArray, $isSubject)
    {
        // No valid mappings
        if (empty($programMappings))
        {
            return false;
        }

        // If there are only program mappings, subjects cannot be mapped
        if (count($programIDArray) == count($programMappings) AND $isSubject)
        {
            return false;
        }

        return true;
    }

    /**
     * Fills the options array with HTML pool options
     *
     * @param   array    &$options               an array to store the options in
     * @param   array    &$programMappings       mappings belonging to one of the
     *                                           requested programs
     * @param   array    &$unselectableMappings  mappings which would lead to data
     *                                           inconsistency
     * @param   array    &$parentIDs             previously mapped parents
     * @param   boolean  $isSubject              whether the calling element is a
     *                                           subject
     * @param   string   $language               the language tag of the active language
     *
     * @return  void
     */
    private function fillOptions(&$options, &$programMappings, &$unselectableMappings, &$parentIDs, $isSubject, $language = 'de')
    {
        foreach ($programMappings as $mapping)
        {
            if (!empty($mapping['subjectID'])
             OR (!empty($unselectableMappings) AND in_array($mapping['id'], $unselectableMappings)))
            {
                continue;
            }
            if (!empty($mapping['poolID']))
            {
                $options[] = THM_OrganizerHelperMapping::getPoolOption($mapping, $language, $parentIDs);
            }
            else
            {
                $options[] = THM_OrganizerHelperMapping::getProgramOption($mapping, $language, $parentIDs, $isSubject);
            }
        }
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
        $selectedProgram = $input->getInt('programID', 0);
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

        $lang = JFactory::getApplication()->input->getString('languageTag', 'de');
        $query = $this->_db->getQuery(true);
        $query->select("p.id, p.name_{$lang} AS name, m.level");
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
        try
        {
            $pools = $this->_db->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_POOLS"), 500);
        }

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
