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
 * Class which manages stored building data.
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
        if (!THM_OrganizerHelperComponent::allowFMAccess()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $data  = JFactory::getApplication()->input->get('jform', [], 'array');
        $table = JTable::getInstance('buildings', 'thm_organizerTable');

        return $table->save($data);
    }

    /**
     * Removes building entries from the database.
     *
     * @return boolean true on success, otherwise false
     */
    public function delete()
    {
        if (!THM_OrganizerHelperComponent::allowFMAccess()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        return THM_OrganizerHelperComponent::delete('buildings');
    }
}
