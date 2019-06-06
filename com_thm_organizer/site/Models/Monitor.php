<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Exception;
use Joomla\CMS\Factory;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class which manages stored monitor data.
 */
class Monitor extends BaseModel
{
    /**
     * save
     *
     * attempts to save the monitor form data
     *
     * @return bool true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function save()
    {
        $data = OrganizerHelper::getFormInput();

        if (empty($data['roomID'])) {
            unset($data['roomID']);
        }

        $data['content'] = $data['content'] == '-1' ? '' : $data['content'];

        return parent::save($data);
    }

    /**
     * Saves the default behaviour as chosen in the monitor manager
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function saveDefaultBehaviour()
    {
        if (!Access::isAdmin()) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $input       = OrganizerHelper::getInput();
        $monitorID   = $input->getInt('id', 0);
        $plausibleID = ($monitorID > 0);

        if ($plausibleID) {
            $table = $this->getTable();
            $table->load($monitorID);
            $table->set('useDefaults', $input->getInt('useDefaults', 0));

            return $table->store();
        }

        return false;
    }

    /**
     * Toggles the monitor's use of default settings
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function toggle()
    {
        if (!Access::allowFMAccess()) {
            throw new Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $input     = OrganizerHelper::getInput();
        $monitorID = $input->getInt('id', 0);
        if (empty($monitorID)) {
            return false;
        }

        $value = $input->getInt('value', 1) ? 0 : 1;

        $query = $this->_db->getQuery(true);
        $query->update('#__thm_organizer_monitors');
        $query->set("useDefaults = '$value'");
        $query->where("id = '$monitorID'");
        $this->_db->setQuery($query);

        return (bool)OrganizerHelper::executeQuery('execute');
    }
}
