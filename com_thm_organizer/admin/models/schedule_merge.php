<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSchedule_Merge
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.edit.model');

/**
 * Loads room entry information to be merged
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSchedule_Merge extends THM_CoreModelEdit
{
    /**
     * Constructor.
     *
     * @param   array $config An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }


    /**
     * Method to get the form
     *
     * @param   Array $data Data         (default: Array)
     * @param   Boolean $loadData Load data
     *
     * @return  mixed  JForm object on success, False on error.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = array(), $loadData = true)
    {
        $option = $this->get('option');
        $name = $this->get('name');
        $form = $this->loadForm("$option.$name", $name, array('control' => 'jform', 'load_data' => false));

        if (empty($form)) {
            return false;
        }

        return $form;
    }
}
