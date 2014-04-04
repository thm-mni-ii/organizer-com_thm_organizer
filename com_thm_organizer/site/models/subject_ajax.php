<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSubject_Ajax
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
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
class THM_OrganizerModelSubject_Ajax extends JModel
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
        $programID = JRequest::getString('programID');
        $poolID = JRequest::getString('poolID');
        $teacherID = JRequest::getString('teacherID');
        if ($programID == '-1' AND $teacherID == '-1')
        {
            return '[]';
        }

        $boundaries = $this->getBoundaries();

        $lang = explode('-', JFactory::getLanguage()->getTag());
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $select = "DISTINCT s.id, s.name_{$lang[0]} AS name, s.externalID";
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
        $subjects = $dbo->loadObjectList();

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
        $poolID = JRequest::getString('poolID');

        if ($poolID != '-1' AND $poolID != 'null')
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
        if (empty($boundaries))
        {
            return array();
        }

        return $boundaries;
    }
}
