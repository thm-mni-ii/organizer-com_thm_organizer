<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/programs.php';

/**
 * Class retrieves dynamic program options.
 */
class THM_OrganizerModelProgram_Ajax extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    /**
     * Gets the program options as a string
     *
     * @return string the concatenated plan program options
     */
    public function getPlanOptions()
    {
        $planOptions = THM_OrganizerHelperPrograms::getPlanPrograms();

        return json_encode($planOptions);
    }

    /**
     * Retrieves subject entries from the database
     *
     * @return string  the subjects which fit the selected resource
     */
    public function programsByTeacher()
    {
        $dbo          = \JFactory::getDbo();
        $defaultArray = explode('-', \JFactory::getLanguage()->getTag());
        $defaultTag   = $defaultArray[0];
        $language     = \OrganizerHelper::getInput()->get('languageTag', $defaultTag);
        $query        = $dbo->getQuery(true);
        $concateQuery = ["dp.name_$language", "', ('", 'd.abbreviation', "' '", ' dp.version', "')'"];
        $query->select('dp.id, ' . $query->concatenate($concateQuery, '') . ' AS name');
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');

        $teacherClauses = THM_OrganizerHelperMapping::getTeacherMappingClauses();
        if (!empty($teacherClauses)) {
            $query->where('( ( ' . implode(') OR (', $teacherClauses) . ') )');
        }

        $query->order('name');
        $dbo->setQuery($query);

        $programs = \OrganizerHelper::executeQuery('loadObjectList');

        return empty($programs) ? '[]' : json_encode($programs);
    }
}
