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

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/programs.php';

/**
 * Class retrieves dynamic program options.
 */
class THM_OrganizerModelDepartment_Ajax extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    /**
     * Gets the program options as a string
     *
     * @return string the concatenated plan program options
     */
    public function getOptions()
    {
        $options = THM_OrganizerHelperDepartments::getOptions();

        return json_encode($options);
    }
}
