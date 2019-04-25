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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/courses.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/participants.php';

use OrganizerHelper;

/**
 * Class which manages stored participant data.
 */
class THM_OrganizerModelParticipant extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    /**
     * (De-) Registers course participants
     *
     * @param int    $participantID the participantID
     * @param int    $courseID      id of lesson
     * @param string $state         the state requested by the user
     *
     * @return boolean true on success, false on error
     * @throws Exception => unauthorized access
     */
    public function register($participantID, $courseID, $state)
    {
        if (!\JFactory::getUser()->id === $participantID) {
            throw new \Exception(\JText::_('THM_ORGANIZER_403'), 403);
        }

        $canAccept = (int)THM_OrganizerHelperCourses::canAcceptParticipant($courseID);
        $state     = $state == 1 ? $canAccept : 2;

        return THM_OrganizerHelperParticipants::changeState($participantID, $courseID, $state);
    }

    /**
     * Saves user information to database
     *
     * @return boolean true on success, false on error
     * @throws Exception => invalid request / unauthorized access
     */
    public function save()
    {
        $data = OrganizerHelper::getInput()->get('jform', [], 'array');

        if (!isset($data['id'])) {
            throw new \Exception(\JText::_('THM_ORGANIZER_400'), 400);
        } elseif ($data['id'] !== \JFactory::getUser()->id) {
            throw new \Exception(\JText::_('THM_ORGANIZER_403'), 403);
        }

        $address   = trim($data['address']);
        $city      = trim($data['city']);
        $forename  = trim($data['forename']);
        $programID = trim($data['programID']);
        $surname   = trim($data['surname']);
        $zipCode   = trim($data['zip_code']);

        if (empty($address) or empty($city) or empty($forename) or empty($programID)
            or empty($surname) or empty($zipCode) or !is_numeric($zipCode)) {
            return false;
        }

        function normalize(&$item)
        {
            if (strpos($item, '-') !== false) {
                $compoundParts = explode('-', $item);
                array_walk($compoundParts, 'normalize');
                $item = implode('-', $compoundParts);

                return;
            }
            $item = ucfirst(strtolower($item));
        }

        // Standardize name formatting/casing

        $forenames = explode(' ', $forename);
        array_filter($forenames);
        array_walk($forenames, 'normalize');
        $forename = implode(' ', $forenames);

        $surname  = str_replace('-', ' ', $surname);
        $surnames = explode(' ', $surname);
        $surnames = array_filter($surnames);
        array_walk($surnames, 'normalize');
        $surname = implode('-', $surnames);

        $data['address']   = $address;
        $data['city']      = $city;
        $data['forename']  = $forename;
        $data['programID'] = $programID;
        $data['surname']   = $surname;
        $data['zip_code']  = $zipCode;

        \JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_thm_organizer/tables');
        $table = \JTable::getInstance('participants', 'THM_OrganizerTable');

        if (empty($table)) {
            return false;
        }

        $table->load($data['id']);

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

            return (bool)OrganizerHelper::executeQuery('execute');
        } else {
            return (bool)$table->save($data);
        }

    }
}
