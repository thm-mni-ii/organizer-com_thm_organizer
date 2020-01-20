<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Controllers;

use Exception;
use Joomla\CMS\Router\Route;
use Organizer\Controller;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Routing;
use Organizer\Models\Group;

/**
 * Class receives user actions and performs access checks and redirection.
 */
class Groups extends Controller
{
	protected $listView = 'groups';

	protected $resource = 'group';

	/**
	 * Makes call to the models's batch function, and redirects to the manager view.
	 *
	 * @return void
	 */
	public function batch()
	{
		$modelName = "Organizer\\Models\\" . OrganizerHelper::getClass($this->resource);
		$model     = new $modelName;

		if ($model->batch())
		{
			OrganizerHelper::message('ORGANIZER_SAVE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('ORGANIZER_SAVE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase() . "&view={$this->listView}";
		$this->setRedirect($url);
	}

	/**
	 * Sets the publication status for any group / complete term pairing to true
	 *
	 * @return void
	 * @throws Exception
	 */
	public function publishPast()
	{
		$group = new Group;

		if ($group->publishPast())
		{
			OrganizerHelper::message('ORGANIZER_SAVE_SUCCESS', 'success');
		}
		else
		{
			OrganizerHelper::message('ORGANIZER_SAVE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase() . '&view=groups';
		$this->setRedirect(Route::_($url, false));
	}
}
