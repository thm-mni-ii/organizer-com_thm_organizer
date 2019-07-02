<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Organizer\Helpers\Access;
use Organizer\Helpers\Courses;
use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class which manages stored course data.
 */
class Course extends BaseModel
{
    /**
     * Saves data for participants when administrator changes state in manager
     *
     * @return bool true on success, false on error
     * @throws Exception => unauthorized access
     */
    public function changeParticipantState()
    {
        $input    = Input::getInput();
        $data     = $input->getArray();
        $formData = Input::getForm();

        if (!Access::allowCourseAccess($formData['id'])) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $participantIDs = $data['checked'];
        $state          = (int)$data['participantState'];
        $invalidState   = ($state < 0 or $state > 2);

        if (empty($participantIDs) or empty($formData['id']) or $invalidState) {
            return false;
        }

        $return = true;

        foreach ($data['checked'] as $participantID) {
            $success = Participants::changeState($participantID, $formData['id'], $state);

            if (empty($success)) {
                return false;
            }

            if ($state === 0) {
                Courses::refreshWaitList($formData['id']);
            }

            $return = ($return and $success);
        }

        return $return;
    }

    /**
     * Sends a circular mail to all course participants
     *
     * @return bool true on success, false on error
     * @throws Exception => not found / unauthorized access
     */
    public function circular()
    {
        $courseID = Input::getInt('lessonID');

        if (empty($courseID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_404'), 404);
        }

        if (empty(Access::allowCourseAccess($courseID))) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $data = Input::getForm();

        if (empty($data['text'])) {
            return false;
        }

        $sender = Factory::getUser(Input::getParams()->get('mailSender'));

        if (empty($sender->id)) {
            return false;
        }

        $recipients = Courses::getFullParticipantData($courseID, (bool)$data['includeWaitList']);

        if (empty($recipients)) {
            return false;
        }

        $mailer = Factory::getMailer();
        $mailer->setSender([$sender->email, $sender->name]);
        $mailer->setSubject($data['subject']);

        foreach ($recipients as $recipient) {
            $mailer->addRecipient($recipient['email']);
        }

        $mailer->setBody($data['text']);
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
     * @return Table A Table object
     */
    public function getTable($name = '', $prefix = '', $options = [])
    {
        // ToDo: standardize naming for courses and lessons
        return OrganizerHelper::getTable('Lessons');
    }

    /**
     * Saves changes to courses. Adjusting the course wait list as appropriate.
     *
     * @return bool true on success, otherwise false
     * @throws Exception invalid request / unauthorized access
     */
    public function save()
    {
        $data = Input::getForm();

        if (!isset($data['id'])) {
            throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
        } elseif (!Access::allowCourseAccess($data['id'])) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $table = $this->getTable();
        $table->load($data['id']);
        $table->campusID         = $data['campusID'];
        $table->max_participants = $data['max_participants'];
        $table->deadline         = $data['deadline'];
        $table->fee              = $data['fee'];

        $success = $table->store();

        if (empty($success)) {
            return false;
        }

        Courses::refreshWaitList($data['id']);

        return true;
    }
}
