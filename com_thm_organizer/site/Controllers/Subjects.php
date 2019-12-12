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
use Organizer\Controller;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Routing;
use Organizer\Models\SubjectLSF;

/**
 * Class receives user actions and performs access checks and redirection.
 */
class Subjects extends Controller
{
	protected $listView = 'subjects';

	protected $resource = 'subject';

	/**
	 * Makes call to the models's import batch function, and redirects to the manager view.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function import()
	{
		$model = new SubjectLSF;
		if ($model->import())
		{
			OrganizerHelper::message('THM_ORGANIZER_IMPORT_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_IMPORT_FAIL', 'error');
		}

		$url = Routing::getRedirectBase();
		$url .= "&view={$this->listView}";
		$this->setRedirect($url);
	}
}
