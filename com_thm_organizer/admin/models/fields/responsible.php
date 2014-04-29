<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.field
 * @name        JFormFieldResponsible
 * @description creates a form field with responsible
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');
 
/**
 * Class JFormFieldResponsible for component com_thm_organizer
 * Class provides methods to creates a form field with responsible
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.field
 */
class JFormFieldResponsible extends JFormField
{
    /**
     * Form field type
     *
     * @var    String
     */
    protected $type = 'Responsible';
 
    /**
     * Method to get the form field
     *
     * @return  String  The Form field with responsibles
     */
    public function getInput()
    {
        $return = '<select id="' . $this->id . '" name="' . $this->name . '">';
 
        $responsibles = $this->getResponsibles();
 
        foreach ($responsibles AS $resp)
        {
            $return .= '<option value="' . $resp['id'] . '" >' . $resp['name'] . '</option>';
        }
 
        $return .= '</select>';

        return $return;
    }
 
    /**
     * Method to get the responsibles
     *
     * @return  Array  An Array with the responsibles
     */
    private function getResponsibles()
    {
        $dbo = JFactory::getDBO();
 
        $query = $dbo->getQuery(true);
        $query->select('id');
        $query->from('#__usergroups');
        $dbo->setQuery((string) $query);
        $groupIDs = $dbo->loadColumn();
 
        $usergroups = array();
        foreach ($groupIDs as $groupID)
        {
            if (JAccess::checkGroup($groupID, 'core.login.admin') || $groupID == 8)
            {
                $usergroups[] = $groupID;
            }
        }
 
        if (count($usergroups))
        {
            $query = $dbo->getQuery(true);
            $query->select("DISTINCT username as id, name as name");
            $query->from('#__users');
            $query->innerJoin('#__user_usergroup_map ON #__users.id = user_id INNER JOIN #__usergroups ON group_id = #__usergroups.id');
            $query->where("#__usergroups.id IN('" . implode("', '", $usergroups) . "')");
            $query->order('name');
            $dbo->setQuery((string) $query);
            $resps = $dbo->loadAssocList();
        }
        else
        {
            $resps = array();
        }

        return $resps;
    }
}
