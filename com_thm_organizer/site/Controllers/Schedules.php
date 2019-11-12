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

/**
 * Class receives user actions and performs access checks and redirection.
 */
class Schedules extends Controller
{
	protected $listView = 'schedules';

	protected $resource = 'schedule';

	/**
	 * Performs access checks. Checks if the schedule is already active. If the
	 * schedule is not already active, calls the activate function of the
	 * schedule model.
	 *
	 * @return void
	 */
	public function activate()
	{
		$model = $this->getModel($this->resource);

		$success = $model->activate();
		if ($success)
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_ACTIVATE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_ACTIVATE_FAIL', 'error');
		}

		$this->setRedirect("index.php?option=com_thm_organizer&view={$this->listView}");
	}

	/**
	 * performs access checks, activates/deactivates the chosen schedule in the
	 * context of its term, and redirects to the schedule manager view
	 *
	 * @return void
	 */
	public function setReference()
	{
		if ($this->resource != 'schedule')
		{
			return;
		}

		$model   = $this->getModel('schedule');
		$success = $model->setReference();
		if ($success)
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_REFERENCE_SUCCESS');
		}
		else
		{
			OrganizerHelper::message('THM_ORGANIZER_MESSAGE_REFERENCE_FAIL', 'error');
		}

		$url = Routing::getRedirectBase();
		$url .= "&view=schedules";
		$this->setRedirect($url);
	}

	/**
	 * Performs access checks and uses the model's upload function to validate
	 * and save the file to the database should validation be successful
	 *
	 * @param   boolean  $shouldNotify  true if Upload and Notify button is pressed
	 *
	 * @return void
	 * @throws Exception
	 */
	public function upload($shouldNotify = false)
	{
		$url = Routing::getRedirectBase();
		if (JDEBUG)
		{
			OrganizerHelper::message('THM_ORGANIZER_DEBUG_ON', 'error');
			$url .= "&view=Schedules";
			$this->setRedirect($url);

			return;
		}

		$model = $this->getModel($this->resource);

		$form      = $this->input->files->get('jform', [], '[]');
		$file      = $form['file'];
		$validType = (!empty($file['type']) and $file['type'] == 'text/xml');

		if ($validType)
		{
			if (mb_detect_encoding($file['tmp_name'], 'UTF-8', true) === 'UTF-8')
			{
				$success = $model->upload($shouldNotify);
				$view    = $success ? 'Schedules' : 'Schedule_Edit';
			}
			else
			{
				$view = 'Schedule_Edit';
				OrganizerHelper::message('THM_ORGANIZER_FILE_ENCODING_INVALID', 'error');
			}
		}
		else
		{
			$view = 'Schedule_Edit';
			OrganizerHelper::message('THM_ORGANIZER_FILE_TYPE_NOT_ALLOWED', 'error');
		}

		$url .= "&view={$view}";
		$this->setRedirect($url);
	}
}
