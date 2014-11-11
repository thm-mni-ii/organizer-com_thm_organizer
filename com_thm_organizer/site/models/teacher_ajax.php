<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelTeacher_Ajax
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
class THM_OrganizerModelTeacher_Ajax extends JModelLegacy
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
    public function teachersByProgramOrPool()
    {
        $programID = JRequest::getString('programID');
        $poolID = JRequest::getString('poolID');

        if (!empty($poolID) AND $poolID != '-1' AND $poolID != 'null')
        {
            $resourceType = 'pool';
            $resourceID = $poolID;
        }
        else
        {
            $resourceType = 'program';
            $resourceID = $programID;
        }

        $boundaries = THM_OrganizerHelperMapping::getBoundaries($resourceType, $resourceID);

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT t.id, t.forename, t.surname")->from('#__thm_organizer_teachers AS t');
        $query->innerJoin('#__thm_organizer_subject_teachers AS st ON st.teacherID = t.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.subjectID = st.subjectID');
        if (!empty($boundaries))
        {
            $query->where("m.lft >= '{$boundaries['lft']}'");
            $query->where("m.rgt <= '{$boundaries['rgt']}'");
        }
        $query->order('t.surname');
        $dbo->setQuery((string) $query);
        
        try 
        {
            $teachers = $dbo->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        if (empty($teachers))
        {
            return '';
        }

        foreach ($teachers AS $key => $value)
        {
            $teachers[$key]->name = empty($value->forename)?
                $value->surname : $value->surname . ', ' . $value->forename;
        }

        return json_encode($teachers);
    }
}
