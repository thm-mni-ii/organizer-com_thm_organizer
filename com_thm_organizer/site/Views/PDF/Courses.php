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

namespace Organizer\Views\PDF;

use Exception;
use Organizer\Helpers\Can;
use Organizer\Views\BaseView;
use THM_OrganizerTemplateBadges;
use THM_OrganizerTemplateDepartment_Participants;
use THM_OrganizerTemplateParticipants;

/**
 * Class loads persistent information about a course into the display context.
 */
class Courses extends BaseView
{
	const BADGES = 2;
	const DEPARTMENT_PARTICIPANTS = 1;
	const PARTICIPANTS = 0;

	/**
	 * Method to get display
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 * @throws Exception => invalid request / unauthorized access
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function display($tpl = null)
	{
		$input = OrganizerHelper::getInput();

		$courseID   = $input->get('lessonID', 0);
		$type       = $input->get('type', 0);
		$validTypes = [self::BADGES, self::DEPARTMENT_PARTICIPANTS, self::PARTICIPANTS];

		if (empty($courseID) or !in_array($type, $validTypes))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
		}

		if (!Can::manage('course', $courseID))
		{
			throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
		}

		switch ($type)
		{
			case self::BADGES:
				require_once __DIR__ . '/tmpl/badges.php';
				new THM_OrganizerTemplateBadges($courseID);
				break;
			case self::DEPARTMENT_PARTICIPANTS:
				require_once __DIR__ . '/tmpl/department_participants.php';
				new THM_OrganizerTemplateDepartment_Participants($courseID);
				break;
			case self::PARTICIPANTS:
				require_once __DIR__ . '/tmpl/participants.php';
				new THM_OrganizerTemplateParticipants($courseID);
				break;
			default:
				throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
		}
	}
}
