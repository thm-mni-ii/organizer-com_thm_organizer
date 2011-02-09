<?php
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
 
jimport( 'joomla.application.component.model' );
 
/**
 * Category List Model
 *
 * @package    Giessen Scheduler
 * @subpackage Components
 */
class thm_organizersModelCategory_List extends JModel
{
    var $data = null;

    function __construct()
    {
        parent::__construct();
        $this->data = new stdClass();
        $this->data->name = 'Category Manager';
        $this->getData();
    }


    /**
     * Configure the submenu links.
     *
     * @param	string	the extension being used for the categories
     *
     * @return	void
     * @since	1.6
     */
    public static function addSubmenu($extension)
    {

		$parts = explode('.',$extension);
		$component = $parts[0];

		if (count($parts) > 1) {
			$section = $parts[1];
		}

		// Try to find the component helper.
		$eName	= str_replace('com_', '', $component);
		$file	= JPath::clean(JPATH_ADMINISTRATOR.'/components/'.$component.'/helpers/'.$eName.'.php');

		if (file_exists($file)) {
			require_once $file;

			$prefix	= ucfirst(str_replace('com_', '', $component));
			$cName	= $prefix.'Helper';

			if (class_exists($cName)) {

				if (is_callable(array($cName, 'addSubmenu'))) {
					$lang = JFactory::getLanguage();
					// loading language file from the administrator/language directory then
					// loading language file from the administrator/components/*extension*/language directory
						$lang->load($component, JPATH_BASE, null, false, false)
					||	$lang->load($component, JPath::clean(JPATH_ADMINISTRATOR.'/components/'.$component), null, false, false)
					||	$lang->load($component, JPATH_BASE, $lang->getDefault(), false, false)
					||	$lang->load($component, JPath::clean(JPATH_ADMINISTRATOR.'/components/'.$component), $lang->getDefault(), false, false);
 					call_user_func(array($cName, 'addSubmenu'), 'categories'.(isset($section)?'.'.$section:''));
				}
			}
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	string	$extension	The extension.
	 * @param	int		$categoryId	The category ID.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions($extension, $categoryId = 0)
	{
		$user		= JFactory::getUser();
		$result		= new JObject;
		$parts		= explode('.',$extension);
		$component	= $parts[0];

		if (empty($categoryId)) {
			$assetName = $component;
		}
		else {
			$assetName = $component.'.category.'.(int) $categoryId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

    function getData()
    {
        $query = "SELECT * FROM #__giessen_scheduler_categories";
	$this->data->data = $this->_getList( $query );
        $this->getLinks();
    }

    function getLinks()
    {
        $dbo = $this->getDBO();
        $query = "SELECT name, admin_menu_link AS link
                  FROM #__components AS c
                  WHERE c.option = 'com_thm_organizer'
                    AND name != '".$this->data->name."'
                  ORDER BY name ASC;";
        $dbo->setQuery( $query );
        $result = $dbo->loadAssocList();
        if(count($result) >= 0) $this->data->links = $result;
        else $this->data->links = '';
    }
	
}