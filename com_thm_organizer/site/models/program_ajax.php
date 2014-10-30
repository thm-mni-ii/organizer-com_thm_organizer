<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelProgram_Ajax
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Class provides methods for retrieving program data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelProgram_Ajax extends JModelLegacy
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
    public function programsByTeacher()
    {
        $dbo = JFactory::getDbo();
        $language = explode('-', JFactory::getLanguage()->getTag());
        $query = $dbo->getQuery(true);        
        $concateQuery = array("dp.subject_{$language[0]}","', ('", "d.abbreviation", "' '", " dp.version", "')'");
        $query->select("dp.id, " . $query->concatenate($concateQuery, "") . " AS name");
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');

        $teacherClauses = THM_OrganizerHelperMapping::getTeacherMappingClauses();
        if (!empty($teacherClauses))
        {
            $query->where("( ( " . implode(') OR (', $teacherClauses) . ") )");
        }

        $query->order('name');
        $dbo->setQuery((string) $query);
        
        try
        {
            $programs = $dbo->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        return json_encode($programs);
    }
}
