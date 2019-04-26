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

/**
 * Class which manages stored color data.
 */
class THM_OrganizerModelColor extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    /**
     * save
     *
     * attempts to save the monitor form data
     *
     * @return bool true on success, otherwise false
     * @throws \Exception => unauthorized access
     */
    public function save()
    {
        if (!THM_OrganizerHelperAccess::isAdmin()) {
            throw new \Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        $data  = OrganizerHelper::getInput()->get('jform', [], 'array');
        $table = \JTable::getInstance('colors', 'thm_organizerTable');

        return $table->save($data);
    }

    /**
     * Removes color entries from the database
     *
     * @return boolean true on success, otherwise false
     * @throws \Exception => unauthorized access
     */
    public function delete()
    {
        if (!THM_OrganizerHelperAccess::isAdmin()) {
            throw new \Exception(Languages::_('THM_ORGANIZER_403'), 403);
        }

        return OrganizerHelper::delete('colors');
    }
}
