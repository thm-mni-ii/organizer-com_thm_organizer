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
 * Class which manages stored campus data.
 */
class THM_OrganizerModelCampus extends JModelLegacy
{
    /**
     * save
     *
     * attempts to save the monitor form data
     *
     * @return bool true on success, otherwise false
     * @throws Exception
     */
    public function save()
    {
        $data      = JFactory::getApplication()->input->get('jform', [], 'array');
        $dataTable = JTable::getInstance('campuses', 'thm_organizerTable');

        // Ensure maximal depth of two
        if (!empty($data['parentID'])) {
            $parentTable = JTable::getInstance('campuses', 'thm_organizerTable');
            $parentTable->load($data['parentID']);
            if (!empty($parentTable->parentID)) {
                return false;
            }
        }

        return $dataTable->save($data);
    }

    /**
     * Removes campus entries from the database
     *
     * @return boolean true on success, otherwise false
     */
    public function delete()
    {
        require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';

        return THM_OrganizerHelperComponent::delete('campuses');
    }
}
