<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        DataAbstraction
 * @description DataAbstraction file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class DataAbstraction for component com_thm_organizer
 * Class provides methods to abstract the joomla methods
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerDataAbstraction
{
    /**
     * Database
     *
     * @var    object
     */
    private $_dbo;

    /**
     * User
     *
     * @var    object
     */
    private $_user;

    /**
     * Joomla mainframe
     *
     * @var    object
     */
    private $_mainframe;

    /**
     * Constructor with initial tasks
     */
    public function __construct()
    {
        $this->_mainframe = JFactory::getApplication();
        $this->_mainframe->initialise();
        $this->_dbo = JFactory::getDBO();
        $this->_user = JFactory::getUser();
    }

    /**
     * Method to get the username
     *
     * @return String The username
     */
    public function getUserName()
    {
        return $this->_user->username;
    }

    /**
     * Method to get the user session id
     *
     * @return String The session id
     */
    public function getUserSessionID()
    {
        return session_id();
    }

    /**
     * Method to get the user roles
     *
     * @return The user roles
     */
    public function getUserRoles()
    {
        return $this->_user->groups;
    }

    /**
     * Method to get the user id
     *
     * @return The user id
     */
    public function getUserID()
    {
        return $this->_user->id;
    }

    /**
     * Method to get the joomla temp folder path
     *
     * @return String The temp folder path
     */
    public function getDownloadFolder()
    {
        $confObject = JFactory::getApplication();
        $tmpPath = $confObject->getCfg('tmp_path') . DIRECTORY_SEPARATOR;
        return $tmpPath;
    }

    /**
     * Method to get the joomla temp folder path
     *
     * @param   String   $query  The SQL statement
     * @param   Boolean  $arr    A flag which indicates whether that the result should be a array or object
     *
     * @return String The temp folder path
     */
    public function query($query, $arr = false)
    {
        $this->_dbo->setQuery($query);
        if (strpos(strtolower($query), "select") === 0)
        {
            if ($arr == false)
            {
                try 
                {
                    $data = $this->_dbo->loadObjectList();
                }
                catch (runtimeException $e)
                {
                    throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_QUERY"), 500);
                }
            }
            else
            {
                try
                {
                    $data = $this->_dbo->loadResultArray();
                }
                catch (runtimeException $e)
                {
                    throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_QUERY"), 500);
                }
            }
        }
        else
        {
            try 
            {
                $this->_dbo->execute();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_EXECUTE"), 500);
            }
            
            $data = true;
        }
        if ($this->_dbo->getErrorNum())
        {
            $data = false;
        }

        return $data;
    }

    /**
     * Method to get database object
     *
     * @return Object The database object
     */
    public function getDBO()
    {
        return $this->_dbo;
    }

    /**
     * Method to get a selected request item
     *
     * @param   String  $var  The item to select
     *
     * @return The item which is selected via $var
     */
    public function getRequest($var)
    {
        return JREQUEST::getVar($var);
    }

    /**
     * Method to get the component settings
     *
     * @return The setting object
     */
    public function getSettings()
    {
        return (object) array(
                "eStudyPath" => "", "eStudywsapiPath" => "","eStudyCreateCoursePath" => "",
                "eStudySoapSchema" => "", "downFolder" => "","vacationcat" => ""
        );
        $query = $this->_dbo->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_settings');
        $query->where("id = '1'");
        
        try 
        {
            $settings = $this->execute((string) $query);
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_SETTINGS"), 500);
        }
        
        if ($settings)
        {
            $settings = $settings[ 0 ];
        }
        else
        {
            return (object) array(
                        "eStudyPath" => "", "eStudywsapiPath" => "","eStudyCreateCoursePath" => "",
                        "eStudySoapSchema" => "", "downFolder" => "","vacationcat" => ""
                    );
        }
        return $settings;
    }

    /**
     * Method to check if the component is available
     *
     * @param   String  $com  The component name
     *
     * @return Boolean True if the component is available unless false
     */
    public function isComponentavailable( $com )
    {
        $query    = $this->_dbo->getQuery(true);
        $query->select('extension_id AS "id", element AS "option", params, enabled');
        $query->from('#__extensions');
        $query->where('`type` = ' . $this->_dbo->quote('component'));
        $query->where('`element` = ' . $this->_dbo->quote($com));
        $this->_dbo->setQuery($query);
        if ($this->_dbo->getErrorMsg())
        {
            return false;
        }

        try 
        {
            $result = $this->_dbo->loadObject();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_COMPONENT_CHECK"), 500);
        }

        if ($result === null)
        {
            return false;
        }

        return true;
    }

    /**
     * Method to get the semester id
     *
     * @return The semester id
     */
    public function getSemID()
    {
        $semesterID = JRequest::getString('semesterID');
        return $semesterID;
    }

    /**
     * Method to get the document object
     *
     * @return The document object
     */
    public function getDoc()
    {
        $doc = JFactory::getDocument();
        return $doc;
    }
}
