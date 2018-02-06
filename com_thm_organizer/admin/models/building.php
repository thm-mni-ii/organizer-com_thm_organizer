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
/** @noinspection PhpIncludeInspection */
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class which modifies stored building data.
 */
class THM_OrganizerModelBuilding extends JModelLegacy
{
    /**
     * Saves building data from the request form to the database.
     *
     * @return bool true on success, otherwise false
     * @throws Exception
     */
    public function save()
    {
        $data  = JFactory::getApplication()->input->get('jform', [], 'array');
        $table = JTable::getInstance('buildings', 'thm_organizerTable');

        return $table->save($data);
    }

    /**
     * Removes building entries from the database.
     *
     * @return  boolean true on success, otherwise false
     */
    public function delete()
    {
        return THM_OrganizerHelper::delete('buildings');
    }
}
