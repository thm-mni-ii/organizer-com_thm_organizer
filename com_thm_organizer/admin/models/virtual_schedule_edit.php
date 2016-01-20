<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.model
 * @name        THM_OrganizerModelVirtual_Schedule_Edit
 * @description Class to create and edit a virtual schedule
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.edit.model');
/**
 * Class THM_OrganizerModelVirtual_Schedule_Edit for component com_thm_organizer
 * Class provides methods to create and edit a virtual schedule
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.model
 */
class THM_OrganizerModelVirtual_Schedule_Edit extends THM_CoreModelEdit
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
    }
}
