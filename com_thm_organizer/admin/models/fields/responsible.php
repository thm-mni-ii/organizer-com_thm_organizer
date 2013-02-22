<?php
/**
 * @version     v0.0.1
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
jimport('joomla.form.formfield');
 
/**
 * Class JFormFieldResponsible for component com_thm_organizer
 *
 * Class provides methods to creates a form field with responsible
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.field
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class JFormFieldResponsible extends JFormField
{
	/**
	 * Form field type
	 *
	 * @var    String
	 * @since  v0.0.1
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
				
		foreach ($responsibles AS $k => $v)
		{
			$return .= '<option value="' . $v->id . '" >' . $v->name . '</option>';
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
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$usergroups = array();
	
		$query = $dbo->getQuery(true);
		$query->select('id');
		$query->from('#__usergroups');
		$dbo->setQuery((string) $query);
		$groups = $dbo->loadObjectList();
	
		foreach ($groups as $k => $v)
		{
			if (JAccess::checkGroup($v->id, 'core.login.admin') || $v->id == 8)
			{
				$usergroups[] = $v->id;
			}
		}
	
		if (is_array($usergroups))
		{
			$query = $dbo->getQuery(true);
			
			$query->select("DISTINCT username as id, name as name");
			$query->from('#__users');
			$query->join('inner', '#__user_usergroup_map ON #__users.id = user_id INNER JOIN #__usergroups ON group_id = #__usergroups.id');
			$query->where("#__usergroups.id IN('" . implode("', '", $usergroups) . "')");
			$query->order('name');
			$dbo->setQuery($query);
			$resps = $dbo->loadObjectList();
		}
		else
		{
			$resps = array();
		}

	
		return $resps;
	}
}
