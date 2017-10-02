<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelProgram_Ajax
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/programs.php';

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
	 * @return  string  the subjects which fit the selected resource
	 */
	public function programsByTeacher()
	{
		$dbo          = JFactory::getDbo();
		$defaultArray = explode('-', JFactory::getLanguage()->getTag());
		$defaultTag   = $defaultArray[0];
		$language     = JFactory::getApplication()->input->get('languageTag', $defaultTag);
		$query        = $dbo->getQuery(true);
		$concateQuery = ["dp.name_$language", "', ('", "d.abbreviation", "' '", " dp.version", "')'"];
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
		$dbo->setQuery($query);

		try
		{
			$programs = $dbo->loadObjectList();
		}
		catch (RuntimeException $exc)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');

			return '[]';
		}

		return json_encode($programs);
	}
}
