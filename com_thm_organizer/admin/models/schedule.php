<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/xml/schedule.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/json_schedule.php';

/**
 * Class which manages stored schedule data.
 */
class THM_OrganizerModelSchedule extends JModelLegacy
{
    /**
     * JSON Object modeling the schedule
     *
     * @var object
     */
    public $schedule = null;

    /**
     * Activates the selected schedule
     *
     * @return true on success, otherwise false
     * @throws Exception
     */
    public function activate()
    {
        $active = $this->getScheduleRow();

        if (empty($active)) {
            return true;
        }

        if (!THM_OrganizerHelperComponent::allowSchedulingAccess($active->id)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        if (!empty($active->active)) {
            return true;
        }

        $jsonModel = new THM_OrganizerModelJSONSchedule;

        // No access checks for the reference schedule, because access rights are inherited through the department.
        $reference = $this->getScheduleRow($active->departmentID, $active->planningPeriodID);

        if (empty($reference) or empty($reference->id)) {
            $jsonModel->save($active->schedule);
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
     * @throws Exception
     */
    public function checkIfActive()
    {
        $scheduleIDs = JFactory::getApplication()->input->get('cid', [], 'array');
        if (!empty($scheduleIDs)) {
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
     * @throws Exception
     */
    public function delete()
    {
        if (!THM_OrganizerHelperComponent::allowSchedulingAccess()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $this->_db->transactionStart();
        $scheduleIDs = JFactory::getApplication()->input->get('cid', [], 'array');
        foreach ($scheduleIDs as $scheduleID) {

            if (!THM_OrganizerHelperComponent::allowSchedulingAccess($scheduleID)) {
                $this->_db->transactionRollback();
                throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
            }

            try {
                $success = $this->deleteSingle($scheduleID);
            } catch (Exception $exception) {
                JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"),
                    'error');
                $this->_db->transactionRollback();

                return false;
            }

            if (!$success) {
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
    private function deleteSingle($scheduleID)
    {
        $schedule = JTable::getInstance('schedules', 'thm_organizerTable');
        $schedule->load($scheduleID);

        return $schedule->delete();
    }

    /**
     * Gets a schedule row for referencing.
     *
     * @param int $departmentID     the department id of the reference row
     * @param int $planningPeriodID the planning period id of the reference row
     *
     * @return mixed  object if successful, otherwise null
     * @throws Exception
     */
    private function getScheduleRow($departmentID = null, $planningPeriodID = null)
    {
        if (empty($departmentID) or empty($planningPeriodID)) {
            $input = JFactory::getApplication()->input;

            // called from activate or set reference => table id in request
            $listIDs = $input->get('cid', [], 'array');

            // implicitly called by the toggle function
            $toggleID = $input->getInt('id', 0);

            $pullID = empty($listIDs) ? $toggleID : $listIDs[0];

            if (empty($pullID)) {
                return null;
            }

            $pullData = $pullID;
        } else {
            $pullData = [
                'departmentID'     => $departmentID,
                'planningPeriodID' => $planningPeriodID,
                'active'           => 1
            ];
        }

        $scheduleRow = JTable::getInstance('schedules', 'thm_organizerTable');
        $scheduleRow->load($pullData);

        return !empty($scheduleRow->id) ? $scheduleRow : null;
    }

    /**
     * Creates the delta to the chosen reference schedule
     *
     * @return boolean true on successful delta creation, otherwise false
     * @throws Exception
     */
    public function setReference()
    {
        $reference = $this->getScheduleRow();

        if (empty($reference)) {
            return true;
        }

        if (!THM_OrganizerHelperComponent::allowSchedulingAccess($reference->id)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $active = $this->getScheduleRow($reference->departmentID, $reference->planningPeriodID);

        if (empty($active)) {
            return true;
        }

        // No access checks for the active schedule, they share the same department from which they inherit access.

        $jsonModel  = new THM_OrganizerModelJSONSchedule;
        $refSuccess = $jsonModel->setReference($reference, $active);

        return $refSuccess;
    }

    /**
     * Toggles the schedule's active status. Access checks performed in called functions.
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception
     */
    public function toggle()
    {
        $input      = JFactory::getApplication()->input;
        $scheduleID = $input->getInt('id', 0);

        if (empty($scheduleID)) {
            return false;
        }

        $active = $input->getBool('value', 1);

        if ($active) {
            return true;
        }

        return $this->activate();
    }

    /**
     * Saves a schedule in the database for later use
     *
     * @return  boolean true on success, otherwise false
     * @throws Exception
     */
    public function upload()
    {
        $form        = JFactory::getApplication()->input->get('jform', [], 'array');
        $invalidForm = (empty($form) or empty($form['departmentID']) or !is_numeric($form['departmentID']));

        if ($invalidForm) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_400'), 400);
        }

        if (!THM_OrganizerHelperComponent::allowSchedulingAccess(null, $form['departmentID'])) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $xmlModel = new THM_OrganizerModelXMLSchedule;
        $valid    = $xmlModel->validate();

        if (!$valid) {
            return false;
        }

        $this->schedule = $xmlModel->schedule;

        $new = JTable::getInstance('schedules', 'thm_organizerTable');
        $new->set('departmentID', $this->schedule->departmentID);
        $new->set('planningPeriodID', $this->schedule->planningPeriodID);
        $new->set('creationDate', $this->schedule->creationDate);
        $new->set('creationTime', $this->schedule->creationTime);
        $new->set('schedule', json_encode($this->schedule));

        $reference = $this->getScheduleRow($new->departmentID, $new->planningPeriodID);
        $jsonModel = new THM_OrganizerModelJSONSchedule;

        if (empty($reference) or empty($reference->id)) {
            $new->set('active', 1);
            $new->store();

            return $jsonModel->save($this->schedule);
        }

        return $jsonModel->setReference($reference, $new);
    }
}
