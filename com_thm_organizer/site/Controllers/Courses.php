<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Controllers;

use Exception;
use Joomla\CMS\Router\Route;
use Organizer\Controller;
use Organizer\Helpers as Helpers;
use Organizer\Models\Course;

/**
 * Class receives user actions and performs access checks and redirection.
 */
class Courses extends Controller
{
	const UNREGISTERED = null;

	use CourseParticipants;

	protected $listView = 'courses';

	protected $resource = 'course';

	/**
	 * Saves course information and redirects.
	 *
	 * @return void modifies saved course data
	 * @throws Exception => unauthorized access
	 */
	public function save()
	{
		$backend = $this->clientContext === self::BACKEND;
		$model   = new Course();
		$url     = Helpers\Routing::getRedirectBase();

		if ($courseID = $model->save())
		{
			Helpers\OrganizerHelper::message('THM_ORGANIZER_SAVE_SUCCESS', 'success');
		}
		else
		{
			Helpers\OrganizerHelper::message('THM_ORGANIZER_SAVE_FAIL', 'error');
		}

		if ($backend or empty($courseID))
		{
			$url .= "&view=courses";
		}
		else
		{
			$url .= "&view=courses&id=$courseID";
		}

		$this->setRedirect(Route::_($url, false));
	}
}
