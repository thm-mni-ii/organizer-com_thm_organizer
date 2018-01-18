<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/course.php';

/**
 * Class provides methods for editing the subject table in frontend
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelCourse extends JModelLegacy
{
    /**
     *    Saves data for participants when administrator changes state in manager
     *
     * @return bool true on success, false on error
     */
    public function changeParticipantState()
    {
        $input          = JFactory::getApplication()->input;
        $data           = $input->getArray();
        $formData       = $data['jform'];
        $participantIDs = $data["checked"];
        $state          = (int)$data["participantState"];
        $invalidState   = ($state < 0 OR $state > 2);

        if (empty($participantIDs) OR empty($formData['id']) OR $invalidState) {
            return false;
        }

        $return = true;

        foreach ($data["checked"] as $participantID) {
            $success = THM_OrganizerHelperParticipant::changeState($participantID, $formData['id'], $state);

            if (empty($success)) {
                return false;
            }

            if ($state === 0) {
                THM_OrganizerHelperCourse::refreshWaitList($formData['id']);
            }

            $return = ($return AND $success);
        }

        return $return;
    }

    /**
     * Sends a circular mail to all course participants
     *
     * @return bool true on success, false on error
     */
    public function circular()
    {
        $input = JFactory::getApplication()->input;

        $courseID  = $input->get("lessonID", 0);
        $subjectID = $input->get("subjectID", 0);

        if (empty($courseID) OR empty(THM_OrganizerHelperCourse::isCourseAdmin($subjectID, 'subject'))) {
            JError::raiseError(401, 'Unauthorized');
        }

        $data = $input->get('jform', [], 'array');

        if (empty($data["text"])) {
            return false;
        }

        $sender = JFactory::getUser(JComponentHelper::getParams('com_thm_organizer')->get('mailSender'));

        if (empty($sender->id)) {
            return false;
        }

        $recipients = THM_OrganizerHelperCourse::getFullParticipantData($courseID, (bool)$data["includeWaitList"]);

        if (empty($recipients)) {
            return false;
        }

        $mailer = JFactory::getMailer();
        $mailer->setSender([$sender->email, $sender->name]);
        $mailer->setSubject($data["subject"]);

        foreach ($recipients as $recipient) {
            $mailer->addRecipient($recipient["email"]);
        }

        $mailer->setBody($data["text"]);
        $sent = $mailer->Send();

        if (!$sent) {
            return false;
        }

        return true;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param string $name    The table name. Optional.
     * @param string $prefix  The class prefix. Optional.
     * @param array  $options Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     */
    public function getTable($name = 'lessons', $prefix = 'THM_OrganizerTable', $options = [])
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_thm_organizer/tables');

        return JTable::getInstance($name, $prefix, $options);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param string $name    The table name. Optional.
     * @param string $prefix  The class prefix. Optional.
     * @param array  $options Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     */
    public function save()
    {
        $input    = JFactory::getApplication()->input;
        $formData = $input->get('jform', [], 'array');
        $courseID = $formData['id'];

        if (empty($formData) OR empty($courseID)) {
            return false;
        }

        $table = $this->getTable();
        $table->load($courseID);
        $table->campusID         = $formData['campusID'];
        $table->max_participants = $formData['max_participants'];

        $success = $table->store();

        if (empty($success)) {
            return false;
        }

        THM_OrganizerHelperCourse::refreshWaitList($courseID);

        return true;
    }

}
