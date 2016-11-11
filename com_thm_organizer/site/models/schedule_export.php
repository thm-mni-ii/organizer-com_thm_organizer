<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSchedule_Export
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/departments.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/programs.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/pools.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/schedule.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/teachers.php';
/** @noinspection PhpIncludeInspection */
//require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class provides methods for retrieving program data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSchedule_Export extends JModelLegacy
{
	public function __construct(array $config)
	{
		parent::__construct($config);
	}

	/**
	 * Retrieves program options
	 *
	 * @return array an array of program options
	 */
	public function getDepartmentOptions()
	{
		$departments = THM_OrganizerHelperDepartments::getPlanDepartments(false);
		$options = array();

		foreach ($departments as $departmentID => $departmentName)
		{
			$option['value'] = $departmentID;
			$option['text'] = $departmentName;
			$options[] = $option;
		}

		return $options;
	}

	public function getPoolOptions()
	{
		$pools = THM_OrganizerHelperPools::getPlanPools(false);
		$options = array();

		foreach ($pools as $poolID => $poolName)
		{
			$option['value'] = $poolID;
			$option['text'] = $poolName;
			$options[] = $option;
		}

		return $options;
	}

	/**
	 * Retrieves program options
	 *
	 * @return array an array of program options
	 */
	public function getProgramOptions()
	{
		$programs = THM_OrganizerHelperPrograms::getPlanPrograms();
		$options = array();

		foreach ($programs as $program)
		{
			$option['value'] = $program['id'];
			$option['text'] = empty($program['name'])? $program['ppName'] : $program['name'];
			$options[] = $option;
		}

		return $options;
	}

	/**
	 * Retrieves teacher options
	 *
	 * @return array an array of teacher options
	 */
	public function getRoomOptions()
	{
		$rooms = THM_OrganizerHelperRooms::getPlanRooms();
		asort($rooms);

		$options = array();

		foreach ($rooms as $roomID => $roomName)
		{
			$option['value'] = $roomID;
			$option['text'] = $roomName;
			$options[] = $option;
		}

		return $options;
	}

	/**
	 * Retrieves teacher options
	 *
	 * @return array an array of teacher options
	 */
	public function getTeacherOptions()
	{
		$teachers = THM_OrganizerHelperTeachers::getPlanTeachers(false);

		$options = array();

		foreach ($teachers as $teacherID => $teacherName)
		{
			$option['value'] = $teacherID;
			$option['text'] = $teacherName;
			$options[] = $option;
		}

		return $options;
	}
}
