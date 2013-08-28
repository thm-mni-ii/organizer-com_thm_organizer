<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerLSFClientAll
 * @description THM_OrganizerLSFClientAll component site helper
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

require_once 'lib/nusoap/nusoap.php';

/**
 * Class THM_OrganizerLSFClientAll for component com_thm_organizer
 *
 * Class provides methods for lsf communication
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class THM_OrganizerLSFClientAll
{
    /**
     * Web-Service URI
     *
     * @var    String
     */
    private $_endpoint;

    /**
     * Nusoap client
     *
     * @var    Object
     */
    private $_client;

    /**
     * Username
     *
     * @var    String
     */
    private $_username;

    /**
     * Password
     *
     * @var    String
     */
    private $_password;

    /**
     * Constructor to set up the client
     *
     * @param   <String>  $endpoint  Web-Service URI
     * @param   <String>  $username  The username
     * @param   <String>  $password  The users password
     */
    public function __construct($endpoint, $username, $password)
    {
        $this->_endpoint = $endpoint;
        $this->_username = $username;
        $this->_password = $password;

        $proxyhost = '';
        $proxyport = '';
        $proxyusername = '';
        $proxypassword = '';

        $timeout = 120;
        $responseTimeout = 120;

        $this->_client = new nusoap_client($this->_endpoint, true, $proxyhost, $proxyport, $proxyusername, $proxypassword, $timeout, $responseTimeout);
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
        $sres = $this->_client->call('getDataXML', $para);

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
                echo "<span align='center' style='color:red;border:1px dotted black;'>";
                echo "<big>Web-Service Fehler: Bitte SOAP-Query Parameter &uuml;berpr&uuml;fen</big></span>";
                return "";
            }
        }
    }

    /**
     * Performs a soap request, in order to get the xml strucutre of the given configuration
     *
     * @param   String  $object       An object
     * @param   String  $studiengang  Major
     * @param   String  $abschluss    Graduation
     * @param   String  $pversion     Version
     *
     * @return SimpleXMLElement
     */
    public function getModules($object, $studiengang, $abschluss = null, $pversion = null)
    {
        $pxml = "<?xml version='1.0' encoding='UTF-8'?>";
        $pxml = $pxml . "<SOAPDataService>";
        $pxml = $pxml . "<general>";
        $pxml = $pxml . "<object>$object</object>";
        $pxml = $pxml . "</general>";
        $pxml = $pxml . "<user-auth>";
        $pxml = $pxml . "<username>$this->_username</username>";
        $pxml = $pxml . "<password>$this->_password</password>";
        $pxml = $pxml . "</user-auth>";
        $pxml = $pxml . "<filter>";
        $pxml = $pxml . "<pord.abschl>$abschluss</pord.abschl>";
        $pxml = $pxml . "<pord.pversion>$pversion</pord.pversion>";

        // BI I MI
        $pxml = $pxml . "<pord.stg>$studiengang</pord.stg>";
        $pxml = $pxml . "</filter>";
        $pxml = $pxml . "</SOAPDataService>";

        return self::getDataXML($pxml);
    }

    /**
     * Method to get the module by mni number
     *
     * @param   String  $moduleID  The module mni number
     *
     * @return Ambigous <void, string, unknown> Returns the xml strucutre of a given lsf lsf course code (CS1001, ...)
     */
    public function getModuleByNrMni($moduleID)
    {
        $pxml = "<?xml version='1.0' encoding='UTF-8'?>";
        $pxml = $pxml . "<SOAPDataService>";
        $pxml = $pxml . "<general>";
        $pxml = $pxml . "<object>ModuleAll</object>";
        $pxml = $pxml . "</general>";
        $pxml = $pxml . "<user-auth>";
        $pxml = $pxml . "<username>$this->_username</username>";
        $pxml = $pxml . "<password>$this->_password</password>";
        $pxml = $pxml . "</user-auth>";
        $pxml = $pxml . "<filter>";
        $pxml = $pxml . "<pord.pfnrex>$moduleID</pord.pfnrex>";
        $pxml = $pxml . " </filter>";
        $pxml = $pxml . "</SOAPDataService>";
        return self::getDataXML($pxml);
    }

    /**
     * Method to get the module by module id
     *
     * @param   Integer  $moduleID  The module id
     *
     * @return Ambigous <void, string, unknown> Returns the xml strucutre of a given lsf module id
     */
    public function getModuleByModulid($moduleID)
    {
        $pxml = "<?xml version='1.0' encoding='UTF-8'?>";
        $pxml = $pxml . "<SOAPDataService>";
        $pxml = $pxml . "<general>";
        $pxml = $pxml . "<object>ModuleAll</object>";
        $pxml = $pxml . "</general>";
        $pxml = $pxml . "<user-auth>";
        $pxml = $pxml . "<username>$this->_username</username>";
        $pxml = $pxml . "<password>$this->_password</password>";
        $pxml = $pxml . "</user-auth>";
        $pxml = $pxml . "<filter>";
        $pxml = $pxml . "<pord.pordnr>$moduleID</pord.pordnr>";
        $pxml = $pxml . " </filter>";
        $pxml = $pxml . "</SOAPDataService>";
        return self::getDataXML($pxml);
    }

}
