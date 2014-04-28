<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelDegree
 * @description THM_OrganizerModelDegree component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');
require_once JPATH_COMPONENT . '/assets/helpers/thm_organizerHelper.php';

/**
 * Class THM_OrganizerModelDegree for component com_thm_organizer
 *
 * Class provides methods to deal with degree
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelDegree extends JModel
{
    /**
     * Saves degree information to the database
     *
     * @return  boolean true on success, otherwise false
     */
    public function save()
    {
        $data = JRequest::getVar('jform', null, null, null, 4);
        $table = JTable::getInstance('degrees', 'thm_organizerTable');
        return $table->save($data);
    }

    /**
     * Deletes the chosen degrees from the database
     *
     * @return boolean true on success, otherwise false
     */
    public function delete()
    {
        return THM_OrganizerHelper::delete('degrees');
    }
}
