<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerLSFClient
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Class provides methods for lsf communication
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerLSFClient
{
	public $clientSet = false;

	private $client;

	private $username;

	private $password;

	/**
	 * Constructor to set up the client
	 */
	public function __construct()
	{
		$this->username  = JComponentHelper::getParams('com_thm_organizer')->get('wsUsername');
		$this->password = JComponentHelper::getParams('com_thm_organizer')->get('wsPassword');

		$options             = array();
		$options['uri']      = JComponentHelper::getParams('com_thm_organizer')->get('wsURI');
		$options['location'] = JComponentHelper::getParams('com_thm_organizer')->get('wsURI');
		$this->client       = new SoapClient(null, $options);
	}

	/**
	 * Determines the resource type pool|subject|invalid
	 *
	 * @param object &$resource
	 *
	 * @return string pool|subject|invalid
	 */
	public static function determineType(&$resource)
	{
		$type = (string) $resource->pordtyp;

		if ($type == 'M')
		{
			return 'subject';
		}

		return (isset($resource->modulliste->modul) AND $type == 'K')? 'pool' : 'invalid';
	}

	/**
	 * Method to perform a soap request based on a certain lsf query
	 *
	 * @param string $query Query structure
	 *
	 * @return  mixed  SimpleXMLElement if the query was successful, otherwise false
	 */
	private function getDataXML($query)
	{
		$app    = JFactory::getApplication();
		$result = $this->client->__soapCall('getDataXML', array('xmlParams' => $query));
		if (!$result)
		{
			$app->enqueueMessage(JText::_('COM_THM_ORGANIZER_ERROR_INVALID_SOAP'), 'error');

			return false;
		}

		if ($result == "error in soap-request")
		{
			$app->enqueueMessage(JText::_('COM_THM_ORGANIZER_ERROR_INVALID_SOAP'), 'error');

			return false;
		}

		$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?>" . $result);

		return $xml;
	}

	/**
	 * Method to get the module by mni number
	 *
	 * @param int $moduleID The module mni number
	 *
	 * @return  Mixed <void, string, unknown> Returns the xml strucutre of a given lsf module id
	 */
	public function getModuleByModulid($moduleID)
	{
		$XML = $this->header('ModuleAll');
		$XML .= "<pord.pordnr>$moduleID</pord.pordnr>";
		$XML .= $this->footer();

		return self::getDataXML($XML);
	}

	/**
	 * Method to get the module by mni number
	 *
	 * @param string $moduleID The module mni number
	 *
	 * @return  Mixed <void, string, unknown> Returns the xml strucutre of a given lsf lsf course code (CS1001, ...)
	 */
	public function getModuleByNrMni($moduleID)
	{
		$XML = $this->header('ModuleAll');
		$XML .= "<pord.pfnrex>$moduleID</pord.pfnrex>";
		$XML .= $this->footer();

		return self::getDataXML($XML);
	}

	/**
	 * Performs a soap request, in order to get the xml strucutre of the given
	 * configuration
	 *
	 * @param string $program degree program code
	 * @param string $degree  associated degree
	 * @param string $year    year of accreditation
	 *
	 * @return SimpleXMLElement
	 */
	public function getModules($program, $degree = null, $year = null)
	{
		$XML = $this->header('studiengang');
		$XML .= "<pord.abschl>$degree</pord.abschl>";
		$XML .= "<pord.pversion>$year</pord.pversion>";
		$XML .= "<pord.stg>$program</pord.stg>";
		$XML .= $this->footer();

		return self::getDataXML($XML);
	}

	/**
	 * Creates the header used by all XML queries
	 *
	 * @param string $objectType the LSF object type
	 *
	 * @return  string  the header of the XML query
	 */
	private function header($objectType)
	{
		$header = "<?xml version='1.0' encoding='UTF-8'?><SOAPDataService>";
		$header .= "<general><object>$objectType</object></general><user-auth>";
		$header .= "<username>$this->username</username>";
		$header .= "<password>$this->password</password>";
		$header .= "</user-auth><filter>";

		return $header;
	}

	/**
	 * Creates the footer used by all XML queries
	 *
	 * @return string
	 */
	private function footer()
	{
		return '</filter></SOAPDataService>';
	}

	/**
	 * Ensures that the title(s) are set and do not contain 'dummy'. This function favors the German title.
	 *
	 * @param object &$resource the resource being checked
	 * @param bool   $isSubject whether or not the formatting is that of the program or subject soap response
	 *
	 * @return bool true if one of the titles has the possibility of being valid, otherwise false
	 */
	public static function invalidTitle(&$resource, $isSubject = false)
	{
		$titleDE = $isSubject? trim((string) $resource->modul->titelde) : trim((string) $resource->titelde);
		$titleEN = $isSubject? trim((string) $resource->modul->titelen) : trim((string) $resource->titelen);
		$title = empty($titleDE)? $titleEN : $titleDE;

		if (empty($title))
		{
			return true;
		}

		$dummyPos = stripos($title, 'dummy');

		return $dummyPos !== false;
	}
}
