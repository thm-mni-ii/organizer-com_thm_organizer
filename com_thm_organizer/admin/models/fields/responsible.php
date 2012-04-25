<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
 
jimport('joomla.form.formfield');
 
class JFormFieldResponsible extends JFormField {
 
	protected $type = 'Responsible';
 
	// getLabel() left out
 
	public function getInput() {
		$return = '<select id="'.$this->id.'" name="'.$this->name.'">';
		
		$responsibles = $this->getResponsibles();
				
		foreach($responsibles AS $k=>$v)
		{
			$return .= '<option value="'.$v->id.'" >'.$v->name.'</option>';
		}
				
		$return .= '</select>';

		return $return;
	}
	
	private function getResponsibles() {
		$mainframe = JFactory::getApplication("administrator");
		$dbo = JFactory::getDBO();
		$usergroups = array();
	
		$query = $dbo->getQuery(true);
		$query->select('id');
		$query->from('#__usergroups');
		$dbo->setQuery((string)$query);
		$groups = $dbo->loadObjectList();
	
		foreach($groups as $k=>$v)
		{
			if(JAccess::checkGroup($v->id, 'core.login.admin') || $v->id == 8)
			{
				$usergroups[] = $v->id;
			}
		}
	
		$query = "SELECT DISTINCT username as id, name as name
		FROM #__users INNER JOIN #__user_usergroup_map ON #__users.id = user_id INNER JOIN #__usergroups ON group_id = #__usergroups.id WHERE";
		$first = true;
		if(is_array($usergroups))
		{
			foreach($usergroups as $k=>$v)
			{
				if($first != true)
					$query .= " OR";
				$query .= " #__usergroups.id = ".(int)$v;
				$first = false;
			}
		}
		$query .= " ORDER BY name";
		$dbo->setQuery( $query );
		$resps = $dbo->loadObjectList();
	
		return $resps;
	}
}
?>