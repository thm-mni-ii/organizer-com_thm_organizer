<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\PDF;

use Exception;
use Organizer\Helpers;
use Organizer\Layouts\PDF\Attendance as Layout;
use Organizer\Views\BaseView;

/**
 * Class loads persistent information about a course into the display context.
 */
class Attendance extends BaseView
{
	use CourseParticipants;

	protected $_layout = 'Attendance';

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
		if (!$courseID = Helpers\Input::getInt('courseID'))
		{
			throw new Exception(Helpers\Languages::_('THM_ORGANIZER_400'), 400);
		}
		elseif (!Helpers\Can::manage('course', $courseID))
		{
			throw new Exception(Helpers\Languages::_('THM_ORGANIZER_401'), 401);
		}

		if (!$participants = $this->getParticipants($courseID))
		{
			throw new Exception(Helpers\Languages::_('THM_ORGANIZER_400'), 400);
		}

		$attendance = new Layout($courseID);
		$attendance->fill($participants);
		$attendance->render();
	}
}
