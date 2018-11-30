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
 * Class which manages stored degree data.
 */
class THM_OrganizerModelDegree extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    /**
     * Saves degree information to the database
     *
     * @return boolean true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function save()
    {
        if (!THM_OrganizerHelperAccess::isAdmin()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $data  = THM_OrganizerHelperComponent::getInput()->get('jform', [], 'array');
        $table = JTable::getInstance('degrees', 'thm_organizerTable');

        return $table->save($data);
    }

    /**
     * Deletes the chosen degrees from the database
     *
     * @return boolean true on success, otherwise false
     * @throws Exception => unauthorized access
     */
    public function delete()
    {
        if (!THM_OrganizerHelperAccess::isAdmin()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        return THM_OrganizerHelperComponent::delete('degrees');
    }
}
