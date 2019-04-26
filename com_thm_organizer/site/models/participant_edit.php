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

use OrganizerHelper as OrganizerHelper;

/**
 * Class loads a form for editing participant data.
 */
class THM_OrganizerModelParticipant_Edit extends \Joomla\CMS\MVC\Model\FormModel
{
    /**
     * Loads user registration information from the database
     *
     * @return object  filled with user registration data on success, otherwise empty
     */
    public function getItem()
    {
        $query  = $this->_db->getQuery(true);
        $userID = \JFactory::getUser()->id;

        $query->select('u.id, p.address, p.zip_code, p.city, p.programID, p.forename, p.surname');
        $query->from('#__users AS u');
        $query->leftJoin('#__thm_organizer_participants AS p ON p.id = u.id');
        $query->where("u.id = '$userID'");

        $this->_db->setQuery($query);

        $item = OrganizerHelper::executeQuery('loadObject');

        return empty($item->id) ? new \stdClass : $item;
    }

    /**
     * Method to get the form
     *
     * @param array $data     Data         (default: array)
     * @param bool  $loadData Load data  (default: true)
     *
     * @return mixed  \JForm object on success, False on error.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_thm_organizer.participant_edit',
            'participant_edit',
            ['control' => 'jform', 'load_data' => $loadData]
        );

        return !empty($form) ? $form : false;
    }

    protected function loadFormData()
    {
        return $this->getItem();
    }
}
