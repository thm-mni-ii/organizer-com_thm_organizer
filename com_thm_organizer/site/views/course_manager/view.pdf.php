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

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/campuses.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/courses.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/planning_periods.php';

/**
 * Class loads persistent information about a course into the display context.
 */
class THM_OrganizerViewCourse_Manager extends \Joomla\CMS\MVC\View\HtmlView
{
    const BADGES = 2;
    const DEPARTMENT_PARTICIPANTS = 1;
    const PARTICIPANTS = 0;

    /**
     * Method to get display
     *
     * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return void
     * @throws Exception => invalid request / unauthorized access
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function display($tpl = null)
    {
        $input = THM_OrganizerHelperComponent::getInput();

        $lessonID   = $input->get('lessonID', 0);
        $type       = $input->get('type', 0);
        $validTypes = [self::BADGES, self::DEPARTMENT_PARTICIPANTS, self::PARTICIPANTS];

        if (empty($lessonID) or !in_array($type, $validTypes)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_400'), 400);
        }

        if (!THM_OrganizerHelperCourses::authorized($lessonID)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_401'), 401);
        }

        switch ($type) {
            case self::BADGES:
                require_once __DIR__ . '/tmpl/badges.php';
                new THM_OrganizerTemplateBadges($lessonID);
                break;
            case self::DEPARTMENT_PARTICIPANTS:
                require_once __DIR__ . '/tmpl/department_participants.php';
                new THM_OrganizerTemplateDepartment_Participants($lessonID);
                break;
            case self::PARTICIPANTS:
                require_once __DIR__ . '/tmpl/participants.php';
                new THM_OrganizerTemplateParticipants($lessonID);
                break;
            default:
                throw new Exception(JText::_('COM_THM_ORGANIZER_400'), 400);
        }
    }
}
