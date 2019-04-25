<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

require_once 'courses.php';
require_once 'languages.php';

use THM_OrganizerHelperLanguages as Languages;

/**
 * Provides general functions for participant access checks, data retrieval and display.
 */
class THM_OrganizerHelperParticipants
{
    const WAIT_LIST = 0;
    const REGISTERED = 1;
    const REMOVED = 2;

    /**
     * Changes a participants state.
     *
     * @param int $participantID the participant's id
     * @param int $courseID      the course's id
     * @param int $state         the requested state
     *
     * @return bool true on success, otherwise false
     */
    public static function changeState($participantID, $courseID, $state)
    {
        switch ($state) {
            case self::WAIT_LIST:
            case self::REGISTERED:
                \JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_thm_organizer/tables');
                $table = \JTable::getInstance('user_lessons', 'THM_OrganizerTable');

                $data = [
                    'lessonID' => $courseID,
                    'userID'   => $participantID
                ];

                $table->load($data);

                $now                   = date('Y-m-d H:i:s');
                $data['user_date']     = $now;
                $data['status_date']   = $now;
                $data['status']        = $state;
                $data['configuration'] = THM_OrganizerHelperCourses::getInstances($courseID);

                $success = $table->save($data);

                break;

            case self::REMOVED:
                $dbo   = \JFactory::getDbo();
                $query = $dbo->getQuery(true);
                $query->delete('#__thm_organizer_user_lessons');
                $query->where("userID = '$participantID'");
                $query->where("lessonID = '$courseID'");
                $dbo->setQuery($query);
                $success = (bool)THM_OrganizerHelperComponent::executeQuery('execute');
                if (!$success) {
                    return false;
                }

                break;
        }

        if (empty($success)) {
            return false;
        }

        self::notify($participantID, $courseID, $state);

        return true;
    }

    /**
     * Notify user if registration state was changed
     *
     * @param int $participantID the participant's id
     * @param int $courseID      the course's id
     * @param int $state         the requested state
     *
     * @return void
     */
    private static function notify($participantID, $courseID, $state)
    {
        $mailer = \JFactory::getMailer();
        $input  = THM_OrganizerHelperComponent::getInput();

        $user       = \JFactory::getUser($participantID);
        $userParams = json_decode($user->params, true);
        $mailer->addRecipient($user->email);

        if (!empty($userParams['language'])) {
            $input->set('languageTag', explode('-', $userParams['language'])[0]);
        } else {
            $officialAbbreviation = THM_OrganizerHelperCourses::getCourse($courseID)['instructionLanguage'];
            $tag                  = strtoupper($officialAbbreviation) === 'E' ? 'en' : 'de';
            $input->set('languageTag', $tag);
        }

        $params = THM_OrganizerHelperComponent::getParams();
        $sender = \JFactory::getUser($params->get('mailSender'));

        if (empty($sender->id)) {
            return;
        }

        $mailer->setSender([$sender->email, $sender->name]);

        $course   = THM_OrganizerHelperCourses::getCourse($courseID);
        $dateText = THM_OrganizerHelperCourses::getDateDisplay($courseID);

        if (empty($course) or empty($dateText)) {
            return;
        }

        $lang       = Languages::getLanguage();
        $campus     = THM_OrganizerHelperCourses::getCampus($courseID);
        $courseName = (empty($campus) or empty($campus['name'])) ? $course['name'] : "{$course['name']} ({$campus['name']})";
        $mailer->setSubject($courseName);
        $body = $lang->_('THM_ORGANIZER_GREETING') . ',\n\n';

        $dates = explode(' - ', $dateText);

        if (count($dates) == 1 or $dates[0] == $dates[1]) {
            $body .= sprintf($lang->_('THM_ORGANIZER_CIRCULAR_BODY_ONE_DATE') . ':\n\n', $courseName, $dates[0]);
        } else {
            $body .= sprintf(
                $lang->_('THM_ORGANIZER_CIRCULAR_BODY_TWO_DATES') . ':\n\n', $courseName, $dates[0],
                $dates[1]
            );
        }

        $statusText = '';

        switch ($state) {
            case 0:
                $statusText .= $lang->_('THM_ORGANIZER_COURSE_MAIL_STATUS_WAIT_LIST');
                break;
            case 1:
                $statusText .= $lang->_('THM_ORGANIZER_COURSE_MAIL_STATUS_REGISTERED');
                break;
            case 2:
                $statusText .= $lang->_('THM_ORGANIZER_COURSE_MAIL_STATUS_REMOVED');
                break;
            default:
                return;
        }

        $body .= ' => ' . $statusText . '\n\n';

        $body .= $lang->_('THM_ORGANIZER_CLOSING') . ',\n';
        $body .= $sender->name . '\n\n';
        $body .= $sender->email . '\n';

        $addressParts = explode(' – ', $params->get('address'));

        foreach ($addressParts as $aPart) {
            $body .= $aPart . '\n';
        }

        $contactParts = explode(' – ', $params->get('contact'));

        foreach ($contactParts as $cPart) {
            $body .= $cPart . '\n';
        }

        $mailer->setBody($body);
        $mailer->Send();
    }
}
