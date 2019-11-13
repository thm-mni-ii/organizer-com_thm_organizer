<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Controllers;

use Exception;
use Organizer\Controller;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Routing;
use Organizer\Models\Organizer as Model;

/**
 * Class receives user actions and performs access checks and redirection.
 */
class Organizer extends Controller
{
	/**
	 * Makes call to migrate the data
	 *
	 * @return void
	 * @throws Exception
	 */
	public function migrateConfigurations()
	{
		$model = new Model;

		if ($model->migrateConfigurations())
		{
			OrganizerHelper::message('Configurations have been migrated');
		}
		else
		{
			OrganizerHelper::message('Configurations have not been migrated', 'error');
		}

		$url = Routing::getRedirectBase() . "&view=organizer";
		$this->setRedirect($url);
	}

	/**
	 * Makes call to migrate the data
	 *
	 * @return void
	 * @throws Exception
	 */
	public function migrateSchedules()
	{
		$model = new Model;

		if ($model->migrateSchedules())
		{
			OrganizerHelper::message('Schedules have been migrated');
		}
		else
		{
			OrganizerHelper::message('Schedules have not been migrated', 'error');
		}

		$url = Routing::getRedirectBase() . "&view=organizer";
		$this->setRedirect($url);
	}

	/**
	 * Makes call to migrate the data
	 *
	 * @return void
	 * @throws Exception
	 */
	public function migrateUserLessons()
	{
		$model = new Model;

		if ($model->migrateUserLessons())
		{
			OrganizerHelper::message('User lessons have been migrated');
		}
		else
		{
			OrganizerHelper::message('User lessons have not been migrated', 'error');
		}

		$url = Routing::getRedirectBase() . "&view=organizer";
		$this->setRedirect($url);
	}
}
