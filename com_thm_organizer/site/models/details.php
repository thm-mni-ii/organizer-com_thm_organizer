<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModeldetails
 * @description THM_OrganizerModeldetails component site model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
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
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModeldetails extends JModel
{
	/**
	 * Method to return a module instance of a given lsf module id
	 *
	 * @param   String  $moduleID  Module Id from lsf
	 *
	 * @return <Module> Instance of a Module
	 */
	public function getModuleByID($moduleID)
	{
		// Get the global component configuration
		$globParams = JComponentHelper::getParams('com_thm_organizer');

		// Perform a soap requst on lsf and save the response
		$client = new THM_OrganizerLSFClientAll(
												$globParams->get('webserviceUri'),
												$globParams->get('webserviceUsername'),
												$globParams->get('webservicePassword')
											   );
		$modulesXML = $client->getModuleByModulid($moduleID);

		// Mapping of the response to a module instance
		$modulobj = new ModuleAll($modulesXML, "", JRequest::getVar('lang'));

		return $modulobj;
	}

	/**
	 * Method to return a mdule instance of a given lsf course code (e.g. CS1001)
	 *
	 * @param   Integer  $moduleID  The module id
	 *
	 * @return <Module> Instance of a Module
	 */
	public function getModuleByNrMni($moduleID)
	{
		// Get the global component configuration
		$globParams = JComponentHelper::getParams('com_thm_organizer');
		$session = JFactory::getSession();

		// Set the default language
		if ($session->get('language') == null)
		{
			$session->set('language', 'de');
		}

		// Perform a soap requst on lsf and save the response
		$client = new THM_OrganizerLSFClient(
											 $globParams->get('webserviceUri'),
											 $globParams->get('webserviceUsername'),
											 $globParams->get('webservicePassword')
											);
		$modulesXML = $client->getModuleByNrMni($moduleID);

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
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);
		$query->select('DISTINCT dozentid');
		$query->from('#__thm_organizer_dozenten_module');
		$query->where("modulid = '$nrmni'");
		$dbo->setQuery((string) $query);
		return $dbo->loadResultArray();
	}

	/**
	 * Method to build the path for the navigation
	 *
	 * @return <Array>  Contains navigation attributes
	 */
	public function getNavigation()
	{
		// Get the global component configuration
		$globParams = JComponentHelper::getParams('com_thm_organizer');

		// Get the necessary instances
		$model = new THM_OrganizerModelGroups;
		$config = $model->getLsfConfiguration();

		// Perform a soap request, in order to get all courses based on the chosen component configuration
		$client = new THM_OrganizerLSFClient(
											 $globParams->get('webserviceUri'),
											 $globParams->get('webserviceUsername'),
											 $globParams->get('webservicePassword')
											);
		$modulesXML = $client->getModules(
				$config[0]->lsf_object, $config[0]->lsf_studiengang, $config[0]->lsf_abschluss, $config[0]->po
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
	 * @param   String   $ISBNText          The bibliography
	 * @param   Boolean  $ISBNPlgAvailable  True if the isbnlink plugin is available otherwise false
	 *
	 * @return  String  The bibliography with the transformed isbn numbers as link
	 */
	public function transformISBN($ISBNText, $ISBNPlgAvailable)
	{
		if ($ISBNPlgAvailable === false)
		{
			return $ISBNText;
		}
		else
		{
			$isbnlinkPlugin = JPluginHelper::getPlugin("content", "thm_isbnlink");
			$pluginParams = json_decode($isbnlinkPlugin->params);
				
			$pluginParams->keyword = "ISBN";
			$ISBNText .= "ISBN:0123456789blabla ISBN 0 12345-678 9";
	
			$pluginKeyword = $pluginParams->keyword;
				
			$matches = $this->getISBNMatches($ISBNText, $pluginKeyword);
	
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
	 * @param   String  $ISBNText       The text with isbn
	 * @param   String  $pluginKeyword  The keyword
	 * 
	 * @return Ambigous <>|multitype:
	 */
	public function getISBNMatches($ISBNText, $pluginKeyword)
	{
		$matches = array();
	
		// Result is stored in $matches
		preg_match_all("/" . $pluginKeyword . "((-13)?(:)?(\s)?(\d[-\s]?){12}|(-10)?(:)?(\s)?(\d[-\s]?){9})\d/",
		 $ISBNText, $matches, PREG_PATTERN_ORDER
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
