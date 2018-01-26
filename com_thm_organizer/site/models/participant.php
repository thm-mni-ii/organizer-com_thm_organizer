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
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/courses.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/participants.php';

/**
 * Class provides methods for handling course participants
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelParticipant extends JModelLegacy
{
    /**
     * (De-) Registers course participants
     *
     * @param int    $participantID the participantID
     * @param int    $courseID      id of lesson
     * @param string $state         the state requested by the user
     *
     * @return boolean true on success, false on error
     */
    public function register($participantID, $courseID, $state)
    {
        $canAccept = (int)THM_OrganizerHelperCourses::canAcceptParticipant($courseID);
        $state     = $state == 1 ? $canAccept : 2;

        return THM_OrganizerHelperParticipants::changeState($participantID, $courseID, $state);
    }

    /**
     * Saves user information to database
     *
     * @return boolean true on success, false on error
     */
    public function save()
    {
        $data = JFactory::getApplication()->input->get('jform', [], 'array');

        if (empty($data)) {
            return false;
        }

        // Standardize name casing
        $data['forename'] = ucfirst(strtolower($data['forename']));
        $data['surname']  = ucfirst(strtolower($data['surname']));

        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_thm_organizer/tables');
        $table = JTable::getInstance('participants', 'THM_OrganizerTable');

        if (empty($table)) {
            return false;
        }

        $table->load($data["id"]);

        if (empty($table->id)) {
            $initial = true;
            $values  = '';

            foreach ($data as $value) {
                if ($initial) {
                    $initial = false;
                } else {
                    $values .= ', ';
                }

                $values .= $this->_db->q($value);
            }

            $query = $this->_db->getQuery(true);
            $query->insert('#__thm_organizer_participants')
                ->columns($this->_db->qn(array_keys($data)))
                ->values($values);
            $this->_db->setQuery($query);

            try {
                return (bool)$this->_db->execute();
            } catch (Exception $exception) {
                JFactory::getApplication()->enqueueMessage($exception->getMessage(), 'error');

                return false;
            }
        } else {
            return (bool)$table->save($data);
        }

    }
}