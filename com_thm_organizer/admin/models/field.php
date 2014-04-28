<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelField
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class THM_OrganizerModelField for component com_thm_organizer
 * Class provides methods to deal with color
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelField extends JModel
{
    /**
     * save
     *
     * attempts to save the monitor form data
     *
     * @return bool true on success, otherwise false
     */
    public function save()
    {
        $data = JRequest::getVar('jform', null, null, null, 4);
        if (strpos($data['gpuntisID'], 'DS_') === false)
        {
            $data['gpuntisID'] = 'DS_' . $data['gpuntisID'];
        }
        $table = JTable::getInstance('fields', 'thm_organizerTable');
        return $table->save($data);
    }
    /**
     * Removes color entries from the database
     *
     * @return  boolean true on success, otherwise false
     */
    public function delete()
    {
        return THM_OrganizerHelper::delete('fields');
    }
}
