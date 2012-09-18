<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		LsfClientMNI
 * @description LsfClientMNI component site helper
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

require_once 'lib/nusoap.php';

/**
 * Class LsfClientMNI for component com_thm_organizer
 *
 * Class provides methods for lsf communication
 *
 * @category	Joomla.Component.Site
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class LsfClientMNI
{
	/**
	 * Web-Service URI
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_endpoint;

	/**
	 * Nusoap client
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_client;

	/**
	 * Username
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_username;

	/**
	 * Password
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_password;

	/**
	 * Constructor to set up the client
	 *
	 * @param   <String>  $endpoint  Web-Service URI
	 * @param   <String>  $username  The username
	 * @param   <String>  $password  The users password
	 */
	public function LsfClientMNI($endpoint, $username, $password)
	{
		$this->endpoint = $endpoint;
		$this->username = $username;
		$this->password = $password;

		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';

		$timeout = 120;
		$responseTimeout = 120;

		$this->client = new nusoap_client($this->endpoint, true, $proxyhost, $proxyport, $proxyusername, $proxypassword, $timeout, $responseTimeout);
		$err = $this->client->getError();
		if ($err)
		{
			echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
			echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
			exit();
		}
	}

	/**
	 * Method to perform a soap request based on a certain lsf query
	 *
	 * @param   String  $query  Query structure
	 *
	 * @return SimpleXMLElement
	 */
	private function getDataXML($query)
	{
		$para = array('xmlParams' => $query);
		$sres = $this->client->call('getDataXML', $para);
		if (!$sres)
		{
			echo "<span style='color:red;'>Web-Service Fehler: Ung&uuml;ltiger Funtkionsaufruf</span>";
			return;
		}
		else
		{
			if ($sres != "error in soap-request")
			{
				$xmlheader = "<?xml version='1.0' encoding='ISO-8859-15'?>";
				$final = $xmlheader . $sres;
				$xml = simplexml_load_string($final);
				return $xml;
			}
			else
			{
				echo "<span align='center' style='color:red;border:1px dotted black;'>" .
						"<big>Web-Service Fehler: Bitte SOAP-Query Parameter &uuml;berpr&uuml;fen</big></span>";
				return "";
			}
		}
	}

	/**
	 * Performs a soap request, in order to get the xml strucutre of the given configuration
	 *
	 * @param   String  $object       An object
	 * @param   String  $studiengang  Major
	 * @param   String  $semester     Semester
	 * @param   String  $abschluss    Graduation
	 * @param   String  $pversion     Version
	 *
	 * @return SimpleXMLElement
	 */
	public function getModules($object, $studiengang, $semester, $abschluss = null, $pversion = null)
	{
		$pxml = "<?xml version='1.0' encoding='UTF-8'?>";
		$pxml = $pxml . "<SOAPDataService>";
		$pxml = $pxml . " <general>";
		$pxml = $pxml . " 	<object>" . $object . "</object>";
		$pxml = $pxml . " </general>";
		$pxml = $pxml . " <user-auth>";
		$pxml = $pxml . " 	<username>" . $this->username . "</username>";
		$pxml = $pxml . " 	<password>" . $this->password . "</password>";
		$pxml = $pxml . " </user-auth>";
		$pxml = $pxml . " <filter>";
		$pxml = $pxml . " 	<pord.abschl>" . $abschluss . "</pord.abschl>";
		$pxml = $pxml . " 	<pord.pversion>" . $pversion . "</pord.pversion>";

		// BI I MI
		$pxml = $pxml . " 	<pord.stg>" . $studiengang . "</pord.stg>";
		$pxml = $pxml . " </filter>";
		$pxml = $pxml . "</SOAPDataService>";

		return self::getDataXML($pxml);
	}

	/**
	 * Method to get the module by mni number
	 *
	 * @param   String  $id  The module mni number
	 *
	 * @return Ambigous <void, string, unknown> Returns the xml strucutre of a given lsf lsf course code (CS1001, ...)
	 */
	public function getModuleByNrMni($id)
	{
		$pxml = "<?xml version='1.0' encoding='UTF-8'?>";
		$pxml = $pxml . "<SOAPDataService>";
		$pxml = $pxml . " <general>";
		$pxml = $pxml . " 	<object>ModuleMNI</object>";
		$pxml = $pxml . " </general>";
		$pxml = $pxml . " <user-auth>";
		$pxml = $pxml . " 	<username>" . $this->username . "</username>";
		$pxml = $pxml . " 	<password>" . $this->password . "</password>";
		$pxml = $pxml . " </user-auth>";
		$pxml = $pxml . " <filter>";
		$pxml = $pxml . "  <pord.pfnrex>" . $id . "</pord.pfnrex>";
		$pxml = $pxml . " </filter>";
		$pxml = $pxml . "</SOAPDataService>";
		return self::getDataXML($pxml);
	}

	/**
	 * Method to get the module by module id
	 *
	 * @param   Integer  $id  The module id
	 *
	 * @return Ambigous <void, string, unknown> Returns the xml strucutre of a given lsf module id
	 */
	public function getModuleByModulid($id)
	{
		$pxml = "<?xml version='1.0' encoding='UTF-8'?>";
		$pxml = $pxml . "<SOAPDataService>";
		$pxml = $pxml . " <general>";
		$pxml = $pxml . " 	<object>ModuleMNI</object>";
		$pxml = $pxml . " </general>";
		$pxml = $pxml . " <user-auth>";
		$pxml = $pxml . " 	<username>" . $this->username . "</username>";
		$pxml = $pxml . " 	<password>" . $this->password . "</password>";
		$pxml = $pxml . " </user-auth>";
		$pxml = $pxml . " <filter>";
		$pxml = $pxml . "  <pord.pordnr>" . $id . "</pord.pordnr>";
		$pxml = $pxml . " </filter>";
		$pxml = $pxml . "</SOAPDataService>";
		return self::getDataXML($pxml);
	}

}
