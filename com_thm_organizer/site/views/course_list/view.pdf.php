<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
define('K_PATH_IMAGES', JPATH_ROOT . '/media/com_thm_organizer/images/');
jimport('tcpdf.tcpdf');

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/courses.php';

/**
 * Class loads persistent information about a course into the display context.
 */
class THM_OrganizerViewCourse_List extends JViewLegacy
{
    /**
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $input = JFactory::getApplication()->input;

        $lessonID = $input->get("lessonID", 0);
        $type     = $input->get("type", 0);
        $validTypes = [0,1,2];

        if (empty($lessonID) OR !in_array($type, $validTypes)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_404'), 404);
        }

        if (THM_OrganizerHelperCourses::authorized($lessonID)) {
            switch ($type) {
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
                    throw new Exception(JText::_('COM_THM_ORGANIZER_400'), 400);
            }
        }

        throw new Exception(JText::_('COM_THM_ORGANIZER_401'), 401);
    }
}