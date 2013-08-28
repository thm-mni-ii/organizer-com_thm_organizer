<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerLSFClient
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

/**
 * Class THM_OrganizerLSFClient for component com_thm_organizer
 * Class provides methods for lsf communication
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class THM_OrganizerLSFClient
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
     */
    public function __construct()
    {
        require_once 'lib/nusoap/nusoap.php';

        $this->_endpoint = JComponentHelper::getParams('com_thm_organizer')->get('wsURI');
        $this->_username = JComponentHelper::getParams('com_thm_organizer')->get('wsUsername');
        $this->_password = JComponentHelper::getParams('com_thm_organizer')->get('wsPassword');

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
        $queryXML = "<?xml version='1.0' encoding='UTF-8'?>";
        $queryXML .= '<SOAPDataService>';
        $queryXML .= '<general>';
        $queryXML .= "<object>$object</object>";
        $queryXML .= '</general>';
        $queryXML .= '<user-auth>';
        $queryXML .= "<username>$this->_username</username>";
        $queryXML .= "<password>$this->_password</password>";
        $queryXML .= '</user-auth>';
        $queryXML .= '<filter>';
        $queryXML .= "<pord.abschl>$abschluss</pord.abschl>";
        $queryXML .= "<pord.pversion>$pversion</pord.pversion>";

        // BI I MI
        $queryXML .= "<pord.stg>$studiengang</pord.stg>";
        $queryXML .= '</filter>';
        $queryXML .= '</SOAPDataService>';

        return self::getDataXML($queryXML);
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
        $queryXML = "<?xml version='1.0' encoding='UTF-8'?>";
        $queryXML .= '<SOAPDataService>';
        $queryXML .= '<general>';
        $queryXML .= '<object>ModuleMNI</object>';
        $queryXML .= '</general>';
        $queryXML .= ' <user-auth>';
        $queryXML .= "<username>$this->_username</username>";
        $queryXML .= "<password>$this->_password</password>";
        $queryXML .= '</user-auth>';
        $queryXML .= '<filter>';
        $queryXML .= "<pord.pfnrex>$moduleID</pord.pfnrex>";
        $queryXML .= '</filter>';
        $queryXML .= '</SOAPDataService>';
        return self::getDataXML($queryXML);
    }

    /**
     * Method to get the module by mni number
     *
     * @param   Integer  $moduleID  The module mni number
     *
     * @return Ambigous <void, string, unknown> Returns the xml strucutre of a given lsf module id
     */
    public function getModuleByModulid($moduleID)
    {
        $queryXML = "<?xml version='1.0' encoding='UTF-8'?>";
        $queryXML .= '<SOAPDataService>';
        $queryXML .= '<general>';
        $queryXML .= '<object>ModuleMNI</object>';
        $queryXML .= '</general>';
        $queryXML .= '<user-auth>';
        $queryXML .= "<username>$this->_username</username>";
        $queryXML .= "<password>$this->_password</password>";
        $queryXML .= '</user-auth>';
        $queryXML .= '<filter>';
        $queryXML .= "<pord.pordnr>$moduleID</pord.pordnr>";
        $queryXML .= '</filter>';
        $queryXML .= '</SOAPDataService>';
        return self::getDataXML($queryXML);
    }
}
