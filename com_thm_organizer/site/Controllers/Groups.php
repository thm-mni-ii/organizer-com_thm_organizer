<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Controllers;

use Exception;
use Joomla\CMS\Router\Route;
use Organizer\Controller;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Routing;

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
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
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
		$success = $this->getModel('group')->publishPast();
		$url     = Routing::getRedirectBase() . '&view=groups';
		if ($success)
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_SUCCESS', 'success');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_SAVE_FAIL', 'error');
		}

		$this->setRedirect(Route::_($url, false));
	}
}