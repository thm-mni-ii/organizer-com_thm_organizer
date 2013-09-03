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
    private $_client;

    private $_username;

    private $_password;

    /**
     * Constructor to set up the client
     */
    public function __construct()
    {
        require_once 'nusoap.php';

        $this->_username = JComponentHelper::getParams('com_thm_organizer')->get('wsUsername');
        $this->_password = JComponentHelper::getParams('com_thm_organizer')->get('wsPassword');

        $params = array();
        $params['endpoint'] = JComponentHelper::getParams('com_thm_organizer')->get('wsURI');
        $params['wsdl'] = true;
        $params['proxyhost'] = '';
        $params['proxyport'] = '';
        $params['proxyusername'] = '';
        $params['proxypassword'] = '';
        $params['timeout'] = 120;
        $params['responseTimeout'] = 120;

        $this->_client = new nusoap_client($params);
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
     * Performs a soap request, in order to get the xml strucutre of the given
     * configuration
     *
     * @param   String  $program  degree program code
     * @param   String  $degree   associated degree
     * @param   String  $year     year of accreditation
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
     * Method to get the module by mni number
     *
     * @param   String  $moduleID  The module mni number
     *
     * @return  Mixed <void, string, unknown> Returns the xml strucutre of a given lsf lsf course code (CS1001, ...)
     */
    public function getModuleByNrMni($moduleID)
    {
        $XML = $this->header('ModuleMNI');
        $XML .= "<pord.pfnrex>$moduleID</pord.pfnrex>";
        $XML .= $this->footer();
        return self::getDataXML($XML);
    }

    /**
     * Method to get the module by mni number
     *
     * @param   Integer  $moduleID  The module mni number
     *
     * @return  Mixed <void, string, unknown> Returns the xml strucutre of a given lsf module id
     */
    public function getModuleByModulid($moduleID)
    {
        $XML = $this->header('ModuleMNI');
        $XML .= "<pord.pordnr>$moduleID</pord.pordnr>";
        $XML .= $this->footer();
        return self::getDataXML($XML);
    }

    /**
     * Creates the header used by all XML queries
     * 
     * @param   String  $objectType  the LSF object type
     * 
     * @return  string  the header of the XML query
     */
    private function header($objectType)
    {
        $header = "<?xml version='1.0' encoding='UTF-8'?><SOAPDataService>";
        $header .= "<general><object>$objectType</object></general><user-auth>";
        $header .= "<username>$this->_username</username>";
        $header .= "<password>$this->_password</password>";
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
}
