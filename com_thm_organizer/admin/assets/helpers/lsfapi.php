<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerLSFClient
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

/**
 * Class provides methods for lsf communication
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerLSFClient
{
    public $clientSet = false;

    private $_client;

    private $_username;

    private $_password;

    /**
     * Constructor to set up the client
     */
    public function __construct()
    {
        $this->_username = JComponentHelper::getParams('com_thm_organizer')->get('wsUsername');
        $this->_password = JComponentHelper::getParams('com_thm_organizer')->get('wsPassword');

        $options = array();
        $options['uri'] = JComponentHelper::getParams('com_thm_organizer')->get('wsURI');
        $options['location'] = JComponentHelper::getParams('com_thm_organizer')->get('wsURI');
        $this->_client = new SoapClient(null, $options);
    }

    /**
     * Method to perform a soap request based on a certain lsf query
     *
     * @param   String  $query  Query structure
     *
     * @return  mixed  SimpleXMLElement if the query was successful, otherwise false
     */
    private function getDataXML($query)
    {
        $app = JFactory::getApplication();
        $result = $this->_client->__soapCall('getDataXML', array('xmlParams' => $query));
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
        $XML = $this->header('ModuleAll');
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
        $XML = $this->header('ModuleAll');
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
