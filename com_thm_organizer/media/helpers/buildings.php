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
 * Class provides general functions for retrieving building data.
 */
class THM_OrganizerHelperBuildings
{
    /**
     * Checks for the building entry in the database, creating it as necessary. Adds the id to the building entry in the
     * schedule.
     *
     * @param string $name the building name
     *
     * @return mixed  int the id if the room could be resolved/added, otherwise null
     * @throws Exception
     */
    public static function getID($name)
    {
        $table    = JTable::getInstance('buildings', 'thm_organizerTable');
        $data = ['name' => $name];

        try {
            $success = $table->load($data);
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return null;
        }

        if ($success) {
            return $table->id;
        }

        // Entry not found
        $success = $table->save($data);

        return $success ? $table->id : null;
    }
}
