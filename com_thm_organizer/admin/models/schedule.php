<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSchedule
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
JTable::addIncludePath(JPATH_BASE . '/administrator/components/com_thm_organizer/tables');
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/xml/schedule.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/json_schedule.php';

/**
 * Class enapsulating data abstraction and business logic for schedules.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSchedule extends JModelLegacy
{
	/**
	 * JSON Object modeling the schedule (old format)
	 *
	 * @var object
	 */
	public $schedule = null;

	/**
	 * JSON Object modeling the schedule (old format)
	 *
	 * @var object
	 */
	public $newSchedule = null;

	/**
	 * Activates the selected schedule
	 *
	 * @return  true on success, otherwise false
	 */
	public function activate()
	{
		$active        = $this->getScheduleRow();
		$activeInvalid = (empty($active) OR empty($active->id) OR !empty($active->active));

		if ($activeInvalid)
		{
			return true;
		}

		$jsonModel = new THM_OrganizerModelJSONSchedule;
		$reference = $this->getScheduleRow($active->departmentID, $active->planningPeriodID);

		if (empty($reference) OR empty($reference->id))
		{
			$jsonModel->save($active->newSchedule);
			$active->set('active', 1);
			$active->store();

			return true;
		}

		return $jsonModel->setReference($reference, $active);
	}

	/**
	 * Checks if the first selected schedule is active
	 *
	 * @return boolean true if the schedule is active otherwise false
	 */
	public function checkIfActive()
	{
		$scheduleIDs = JFactory::getApplication()->input->get('cid', [], 'array');
		if (!empty($scheduleIDs))
		{
			$scheduleID = $scheduleIDs[0];
			$schedule   = JTable::getInstance('schedules', 'thm_organizerTable');
			$schedule->load($scheduleID);

			return $schedule->active;
		}

		return false;
	}

	/**
	 * Deletes the selected schedules
	 *
	 * @return boolean true on successful deletion of all selected schedules
	 *                 otherwise false
	 */
	public function delete()
	{
		$this->_db->transactionStart();
		$scheduleIDs = JFactory::getApplication()->input->get('cid', [], 'array');
		foreach ($scheduleIDs as $scheduleID)
		{
			try
			{
				$success = $this->deleteSingle($scheduleID);
			}
			catch (Exception $exception)
			{
				JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
				$this->_db->transactionRollback();

				return false;
			}

			if (!$success)
			{
				$this->_db->transactionRollback();

				return false;
			}
		}
		$this->_db->transactionCommit();

		return true;
	}

	/**
	 * Deletes a single schedule
	 *
	 * @param int $scheduleID the id of the schedule to be deleted
	 *
	 * @return boolean true on success otherwise false
	 */
	public function deleteSingle($scheduleID)
	{
		$schedule = JTable::getInstance('schedules', 'thm_organizerTable');
		$schedule->load($scheduleID);

		return $schedule->delete();
	}

	/**
	 * Gets a schedule row for referencing. Implicitly migrating as necessary
	 *
	 * @param int $departmentID     the department id of the reference row
	 * @param int $planningPeriodID the planning period id of the reference row
	 *
	 * @return  mixed  object if successful, otherwise null
	 */
	private function getScheduleRow($departmentID = null, $planningPeriodID = null)
	{
		$scheduleRow = JTable::getInstance('schedules', 'thm_organizerTable');

		if (empty($departmentID) OR empty($planningPeriodID))
		{
			$input = JFactory::getApplication()->input;

			// called from activate or set reference => table id in request
			$listIDs = $input->get('cid', [], 'array');

			// implicitly called by the toggle function
			$toggleID = $input->getInt('id', 0);

			$pullID = empty($listIDs) ? $toggleID : $listIDs[0];

			if (empty($pullID))
			{
				return null;
			}

			$pullData = $pullID;
		}
		else
		{
			$pullData = [
				'departmentID'     => $departmentID,
				'planningPeriodID' => $planningPeriodID,
				'active'           => 1
			];
		}

		$exists = $scheduleRow->load($pullData);

		return $exists ? $scheduleRow : null;
	}

	/**
	 * Creates the delta to the chosen reference schedule
	 *
	 * @return boolean true on successful delta creation, otherwise false
	 */
	public function setReference()
	{
		$reference = $this->getScheduleRow();

		if (empty($reference) OR empty($reference->id))
		{
			return true;
		}

		$active = $this->getScheduleRow($reference->departmentID, $reference->planningPeriodID);

		if (empty($active) OR empty($active->id))
		{
			return true;
		}

		$jsonModel  = new THM_OrganizerModelJSONSchedule;
		$refSuccess = $jsonModel->setReference($reference, $active);

		return $refSuccess;
	}

	/**
	 * Toggles the schedule's active status
	 *
	 * @return  boolean  true on success, otherwise false
	 */
	public function toggle()
	{
		$input      = JFactory::getApplication()->input;
		$scheduleID = $input->getInt('id', 0);

		if (empty($scheduleID))
		{
			return false;
		}

		$active = $input->getBool('value', 1);

		if ($active)
		{
			return true;
		}

		return $this->activate();
	}

	/**
	 * saves a schedule in the database for later use
	 *
	 * @return   boolean true on success, otherwise false
	 */
	public function upload()
	{
		$xmlModel = new THM_OrganizerModelXMLSchedule;
		$valid    = $xmlModel->validate();

		if (!$valid)
		{
			return false;
		}

		$this->newSchedule = $xmlModel->newSchedule;

		$new = JTable::getInstance('schedules', 'thm_organizerTable');
		$new->set('departmentID', $this->newSchedule->departmentID);
		$new->set('departmentname', $this->newSchedule->departmentname);
		$new->set('planningPeriodID', $this->newSchedule->planningPeriodID);
		$new->set('semestername', $this->newSchedule->semestername);
		$new->set('creationDate', $this->newSchedule->creationDate);
		$new->set('creationTime', $this->newSchedule->creationTime);
		$new->set('startDate', $this->newSchedule->startDate);
		$new->set('endDate', $this->newSchedule->endDate);
		$jsonSchedule = json_encode($this->newSchedule);
		$new->set('schedule', $jsonSchedule);
		$new->set('newSchedule', $jsonSchedule);

		$reference = $this->getScheduleRow($new->departmentID, $new->planningPeriodID);
		$jsonModel = new THM_OrganizerModelJSONSchedule;

		if (empty($reference) OR empty($reference->id))
		{
			$new->set('active', 1);
			$new->store();

			return $jsonModel->save($this->newSchedule);
		}

		return $jsonModel->setReference($reference, $new);
	}
}
