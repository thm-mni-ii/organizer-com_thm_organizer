<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
define('K_PATH_IMAGES', JPATH_ROOT . '/media/com_thm_organizer/images/');
jimport('tcpdf.tcpdf');

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/prep_course.php';

/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewCourse_List extends JViewLegacy
{
	/**
	 * Method to get display
	 *
	 * @param Object $tpl template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$input = JFactory::getApplication()->input;

		$lessonID  = $input->get("lessonID", 0);
		$type      = $input->get("type", 0);
		$subjectID = THM_OrganizerHelperPrep_Course::getCourse($lessonID)["subjectID"];

		$user    = JFactory::getUser();
		$isAdmin = $user->authorise('core.admin');

		if ($isAdmin OR THM_OrganizerHelperPrep_Course::authSubjectTeacher($subjectID))
		{
			switch ($type)
			{
				case 0:
					require_once __DIR__ . "/tmpl/prep_course_participant_list.php";
					new THM_OrganizerTemplatePC_Participant_Export($lessonID);
					break;
				case 1:
					require_once __DIR__ . "/tmpl/prep_course_by_department.php";
					new THMOrganizerTemplatePC_By_Department_Export($lessonID);
					break;
				case 2:
					require_once __DIR__ . "/tmpl/prep_course_badges.php";
					new THMOrganizerTemplatePC_Badges_Export($lessonID);
					break;
				default:
					JError::raiseError(404, 'Type not found');
			}
		}

		JError::raiseError(401, 'Unauthorized');
	}
}