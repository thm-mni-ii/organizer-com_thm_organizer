<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.model
 * @name        THM_OrganizerModelVirtual_Schedule_Edit
 * @description Class to create and edit a virtual schedule
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');
/**
 * Class THM_OrganizerModelVirtual_Schedule_Edit for component com_thm_organizer
 * Class provides methods to create and edit a virtual schedule
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.model
 */
class THM_OrganizerModelVirtual_Schedule_Edit extends JModelAdmin
{
    /**
     * Method to get the form
     *
     * @param   Array    $data      Data         (default: Array)
     * @param   Boolean  $loadData  Load data  (default: true)
     *
     * @return  A Form object
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_thm_organizer.virtual_schedule_edit', 'virtual_schedule_edit', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form))
        {
            return false;
        }
        return $form;
    }
}
