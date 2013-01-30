<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		THM_OrganizerModeldetails
 * @description THM_OrganizerModeldetails component site model
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.model');
jimport('joomla.filesystem.path');

require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper/lsfapi.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper/lsfapi_all.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper/lsfapi_mni.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper/module_mni.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'helper/module_all.php';
require_once JPATH_SITE . DS . 'components' . DS . 'com_thm_organizer' . DS . 'models/groups.php';

/**
 * Class THM_OrganizerModeldetails for component com_thm_organizer
 *
 * Class provides methods to get details about modules
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerModeldetails extends JModel
{
	/**
	 * Method to return a module instance of a given lsf module id
	 *
	 * @param   String  $id  Module Id from lsf
	 *
	 * @return <Module> Instance of a Module
	 */
	public function getModuleByID($id)
	{
		// Get the global component configuration
		$globParams = &JComponentHelper::getParams('com_thm_organizer');
		$session = & JFactory::getSession();

		// Perform a soap requst on lsf and save the response
		$client = new LsfClientAll($globParams->get('webserviceUri'), $globParams->get('webserviceUsername'), $globParams->get('webservicePassword'));
		$modulesXML = $client->getModuleByModulid($id);

		// Mapping of the response to a module instance
		$modulobj = new ModuleAll($modulesXML, "", JRequest::getVar('lang'));

		return $modulobj;
	}

	/**
	 * Method to return a mdule instance of a given lsf course code (e.g. CS1001)
	 *
	 * @param   Integer  $id  The module id
	 *
	 * @return <Module> Instance of a Module
	 */
	public function getModuleByNrMni($id)
	{
		// Get the global component configuration
		$globParams = &JComponentHelper::getParams('com_thm_organizer');
		$session = & JFactory::getSession();

		// Set the default language
		if ($session->get('language') == null)
		{
			$session->set('language', 'de');
		}

		// Perform a soap requst on lsf and save the response
		$client = new LsfClient($globParams->get('webserviceUri'), $globParams->get('webserviceUsername'), $globParams->get('webservicePassword'));
		$modulesXML = $client->getModuleByNrMni($id);

		// Mapping of the response to a module instance
		$modulobj = new Module($modulesXML, "", JRequest::getVar('lang'));
		return $modulobj;
	}

	/**
	 * Method to get the teacher
	 *
	 * @param   String  $nrmni  The module id
	 *
	 * @return <Array>
	 */
	public function getDozenten($nrmni)
	{
		$db = &JFactory::getDBO();
		$query = "SELECT DISTINCT dozentid FROM #__thm_organizer_dozenten_module WHERE modulid = '$nrmni';";
		$db->setQuery($query);
		return $db->loadResultArray();
	}

	/**
	 * Method to build the path for the navigation
	 *
	 * @return <Array>  Contains navigation attributes
	 */
	public function getNavigation()
	{
		// Get the global component configuration
		$globParams = &JComponentHelper::getParams('com_thm_organizer');

		// Get the necessary instances
		$model = new THM_OrganizerModelGroups;
		$config = $model->getLsfConfiguration();

		// Perform a soap request, in order to get all courses based on the chosen component configuration
		$client = new LsfClient($globParams->get('webserviceUri'), $globParams->get('webserviceUsername'), $globParams->get('webservicePassword'));
		$modulesXML = $client->getModules(
				$config[0]->lsf_object, $config[0]->lsf_studiengang, "", $config[0]->lsf_abschluss, $config[0]->po
		);

		$navi = array();

		if (isset($modulesXML))
		{
			// Iterates over each group
			foreach ($modulesXML->gruppe as $gruppe)
			{
				// Iterates over each course
				foreach ($gruppe->modulliste->modul as $modul)
				{
					// Build the navigation array
					$arr = array();
					$arr['id'] = $modul->modulalphaid;
					$arr['link'] = JRoute::_("index.php?option=com_thm_organizer&view=details&nrmni=" . $modul->modulalphaid);
					array_push($navi, $arr);
				}
			}
		}
		return $navi;
	}
	


	/**
	 * Method to parse a ISBN in the correct syntax for the isbnlink plugin
	 *
	 * @param   String   $isbns						 The bibliography
	 * @param   Boolean  $isIsbnlinkPluginAvailable  True if the isbnlink plugin is available otherwise false
	 *
	 * @return  String  The bibliography with the transformed isbn numbers as link
	 */
	public function transformISBN($isbns, $isIsbnlinkPluginAvailable)
	{
		if ($isIsbnlinkPluginAvailable === false)
		{
			return $modulLiteraturVerzeichnis;
		}
		else
		{
			$isbnlinkPlugin = JPluginHelper::getPlugin("content", "thm_isbnlink");
			$pluginParams = json_decode($isbnlinkPlugin->params);
				
			$pluginParams->keyword = "ISBN";
			$modulLiteraturVerzeichnis .= "ISBN:0123456789blabla ISBN 0 12345-678 9";
	
			$pluginKeyword = $pluginParams->keyword;
				
			$matches = $this->getISBNMatches($modulLiteraturVerzeichnis, $pluginKeyword);
	
			var_dump($matches);
			echo "<br/><br/><br/><br/>";
		}
	}
	
	/**
	 * Method to get the keyword from the ISBNLinkPlugin
	 * 
	 * @return  String  The keyword
	 */
	public function getKeywordFromISBNLinkPlugin()
	{
	
	}
	
	/**
	 * Method to get all ISBN matches
	 * 
	 * @param   String  $modulLiteraturVerzeichnis  The text with isbn
	 * @param   String  $pluginKeyword				The keyword
	 * 
	 * @return Ambigous <>|multitype:
	 */
	public function getISBNMatches($modulLiteraturVerzeichnis, $pluginKeyword)
	{
		$matches = array();
	
		// Result is stored in $matches
		preg_match_all("/" . $pluginKeyword . "((-13)?(:)?(\s)?(\d[-\s]?){12}|(-10)?(:)?(\s)?(\d[-\s]?){9})\d/",
		 $modulLiteraturVerzeichnis, $matches, PREG_PATTERN_ORDER
		);
	
		if ($matches[0])
		{
			return $matches[0];
		}
		else
		{
			return $matches;
		}
	}
}
