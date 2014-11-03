<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSubject_Ajax
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_ADMINISTRATOR . '/components/com_thm_organizer/assets/helpers/mapping.php';

/**
 * Class provides methods for building a model of the curriculum in JSON format
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSubject_Ajax extends JModelLegacy
{
    /**
     * Constructor to set up the class variables and call the parent constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieves subject entries from the database
     * 
     * @return  string  the subjects which fit the selected resource
     */
    public function getSubjects()
    {
        $input = JFactory::getApplication()->input;
        $programID = $input->getString('programID', '-1');
        $teacherID = $input->getString('teacherID', '-1');
        $lang = $input->getString('languageTag', 'de');
        if ($programID == '-1' AND $teacherID == '-1')
        {
            return '[]';
        }

        $boundaries = $this->getBoundaries();

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $select = "DISTINCT s.id, s.name_{$lang} AS name, s.externalID";
        $query->select($select)->from('#__thm_organizer_subjects AS s');
        if (!empty($boundaries))
        {
            $query->innerJoin('#__thm_organizer_mappings AS m ON m.subjectID = s.id');
            $query->where("m.lft >= '{$boundaries['lft']}'");
            $query->where("m.rgt <= '{$boundaries['rgt']}'");
        }
        if ($teacherID != '-1')
        {
            $query->innerJoin('#__thm_organizer_subject_teachers AS st ON st.subjectID = s.id');
            $query->where("st.teacherID = '$teacherID'");
        }
        $query->order('name');
        $dbo->setQuery((string) $query);
        
        try 
        {
            $subjects = $dbo->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        return empty($subjects)? '[]' : json_encode($subjects);
    }

    /**
     * Retrieves the left and right boundaries of the nested program or pool
     * 
     * @return  array
     */
    private function getBoundaries()
    {
        $programID = JRequest::getString('programID');
        $programBoundaries = THM_OrganizerHelperMapping::getBoundaries('program', $programID);

        if (empty($programBoundaries))
        {
            return array();
        }

        $poolID = JRequest::getString('poolID');
        if ($poolID != '-1' AND $poolID != 'null')
        {
            $poolBoundaries = THM_OrganizerHelperMapping::getBoundaries('pool', $poolID);
        }

        if (!empty($poolBoundaries))
        {
            if ($this->poolInProgram($poolBoundaries, $programBoundaries))
            {
                return $poolBoundaries;
            }
        }

        return $programBoundaries;
    }

    /**
     * Checks whether the pool is subordinate to the selected program
     * 
     * @param   array  $poolBoundaries     the pool's left and right values
     * @param   array  $programBoundaries  the program's left and right values
     * 
     * @return  boolean  true if the pool is subordinate to the program,
     *                   otherwise false
     */
    private function poolInProgram($poolBoundaries, $programBoundaries)
    {
        $leftValid = $poolBoundaries['lft'] > $programBoundaries['lft'];
        $rightValid = $poolBoundaries['rgt'] < $programBoundaries['rgt'];
        if ($leftValid AND $rightValid)
        {
            return true;
        }
        return false;
    }
}
