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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class loads a form for editing participant data.
 */
class Participant_Edit extends FormModel
{
    /**
     * Loads user registration information from the database
     *
     * @return object  filled with user registration data on success, otherwise empty
     */
    public function getItem()
    {
        $query  = $this->_db->getQuery(true);
        $userID = Factory::getUser()->id;

        $query->select('u.id, p.address, p.zip_code, p.city, p.programID, p.forename, p.surname');
        $query->from('#__users AS u');
        $query->leftJoin('#__thm_organizer_participants AS p ON p.id = u.id');
        $query->where("u.id = '$userID'");

        $this->_db->setQuery($query);

        $item = OrganizerHelper::executeQuery('loadObject');

        return empty($item->id) ? new \stdClass : $item;
    }
}
