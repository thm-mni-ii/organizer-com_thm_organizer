<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Exception;
use Joomla\CMS\Factory;

/**
 * Provides general functions for schedule access checks, data retrieval and display.
 */
class Schedules
{
    const SEMESTER_MODE = 1;

    const PERIOD_MODE = 2;

    const INSTANCE_MODE = 3;

    /**
     * deletes lessons in the personal schedule of a logged in user
     *
     * @return string JSON coded and deleted ccmIDs
     * @throws Exception => invalid request / unauthorized access
     */
    public static function deleteUserLesson()
    {
        $ccmID = Input::getInt('ccmID');
        if (empty($ccmID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
        }

        $userID = Factory::getUser()->id;
        if (empty($userID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $mode     = Input::getInt('mode', self::PERIOD_MODE);
        $mappings = self::getMatchingLessons($mode, $ccmID);

        $deletedCcmIDs = [];
        foreach ($mappings as $lessonID => $ccmIDs) {
            $userLessonTable = OrganizerHelper::getTable('UserLessons');

            if (!$userLessonTable->load(['userID' => $userID, 'lessonID' => $lessonID])) {
                continue;
            }

            $deletedCcmIDs = array_merge($deletedCcmIDs, $ccmIDs);

            // Delete a lesson completely? delete whole row in database
            if ($mode == self::SEMESTER_MODE) {
                $userLessonTable->delete($userLessonTable->id);
            } else {
                $configurations = array_flip(json_decode($userLessonTable->configuration));
                foreach ($ccmIDs as $ccmID) {
                    unset($configurations[$ccmID]);
                }

                $configurations = array_flip($configurations);
                if (empty($configurations)) {
                    $userLessonTable->delete($userLessonTable->id);
                } else {
                    $conditions = [
                        'id'            => $userLessonTable->id,
                        'userID'        => $userID,
                        'lessonID'      => $userLessonTable->lessonID,
                        'configuration' => array_values($configurations),
                        'user_date'     => date('Y-m-d H:i:s')
                    ];
                    $userLessonTable->bind($conditions);
                }

                $userLessonTable->store();
            }
        }

        return $deletedCcmIDs;
    }

    /**
     * Resolves the given date to the start and end dates for the requested time period
     *
     * @param array $parameters the schedule configuration parameters
     *
     * @return array the corresponding start and end dates
     */
    public static function getDates($parameters)
    {
        $date     = $parameters['date'];
        $dateTime = strtotime($date);
        $reqDoW   = date('w', $dateTime);

        $startDayNo   = empty($parameters['startDay']) ? 1 : $parameters['startDay'];
        $endDayNo     = empty($parameters['endDay']) ? 6 : $parameters['endDay'];
        $displayedDay = ($reqDoW >= $startDayNo and $reqDoW <= $endDayNo);
        if (!$displayedDay) {
            if ($reqDoW === 6) {
                $dateTime = strtotime('-1 day', $dateTime);
            } else {
                $dateTime = strtotime('+1 day', $dateTime);
            }
            $date = date('Y-m-d', strtotime($dateTime));
        }

        $parameters['date'] = $date;

        switch ($parameters['interval']) {
            case 'day':
                $dates = ['startDate' => $date, 'endDate' => $date];
                break;

            case 'month':
                $dates = Dates::getMonth($date, $startDayNo, $endDayNo);
                break;

            case 'semester':
                $dates = Dates::getSemester($date);
                break;

            case 'ics':
                // ICS calendars get the next 6 months of data
                $dates = Dates::getICSDates($date, $startDayNo, $endDayNo);
                break;

            case 'week':
            default:
                $dates = Dates::getWeek($date, $startDayNo, $endDayNo);
                break;
        }

        return $dates;
    }

    /**
     * Saves lesson instance references in the personal schedule of the user
     *
     * @return array saved ccmIDs
     * @throws Exception => invalid request / unauthorized access
     */
    public static function saveUserLesson()
    {
        $ccmID = Input::getInt('ccmID');
        if (empty($ccmID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
        }

        $userID = Factory::getUser()->id;
        if (empty($userID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $savedCcmIDs = [];
        $mode        = Input::getInt('mode', self::PERIOD_MODE);
        $mappings    = self::getMatchingLessons($mode, $ccmID);

        foreach ($mappings as $lessonID => $ccmIDs) {
            try {
                $userLessonTable = OrganizerHelper::getTable('UserLessons');
                $hasUserLesson   = $userLessonTable->load(['userID' => $userID, 'lessonID' => $lessonID]);
            } catch (Exception $e) {
                return '[]';
            }

            $conditions = [
                'userID'      => $userID,
                'lessonID'    => $lessonID,
                'user_date'   => date('Y-m-d H:i:s'),
                'status'      => (int)Courses::canAcceptParticipant($lessonID),
                'status_date' => date('Y-m-d H:i:s'),
            ];

            if ($hasUserLesson) {
                $conditions['id'] = $userLessonTable->id;
                $oldCcmIds        = json_decode($userLessonTable->configuration);
                $ccmIDs           = array_merge($ccmIDs, array_diff($oldCcmIds, $ccmIDs));
            }

            $conditions['configuration'] = $ccmIDs;

            if ($userLessonTable->bind($conditions) and $userLessonTable->store()) {
                $savedCcmIDs = array_merge($savedCcmIDs, $ccmIDs);
            }
        }

        return $savedCcmIDs;
    }
}
