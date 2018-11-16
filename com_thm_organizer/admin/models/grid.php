<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class which manages stored grid data.
 */
class THM_OrganizerModelGrid extends JModelLegacy
{
    /**
     * Save the form data for a new grid
     *
     * @return bool true on success, otherwise false
     * @throws Exception
     */
    public function save()
    {
        if (!THM_OrganizerHelperAccess::isAdmin()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        $data  = JFactory::getApplication()->input->get('jform', [], 'array');
        $table = JTable::getInstance('grids', 'thm_organizerTable');

        // Save grids in json by foreach because the index is not numeric
        $periods = [];
        $index   = 1;
        foreach ($data['grid'] as $row) {
            $periods[$index] = $row;
            ++$index;
        }

        $grid         = ['periods' => $periods, 'startDay' => $data['startDay'], 'endDay' => $data['endDay']];
        $data['grid'] = json_encode($grid);

        return $table->save($data);
    }

    /**
     * Removes grid entries from the database
     *
     * @return boolean true on success, otherwise false
     * @throws Exception
     */
    public function delete()
    {
        if (!THM_OrganizerHelperAccess::isAdmin()) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_403'), 403);
        }

        return THM_OrganizerHelperComponent::delete('grids');
    }
}
