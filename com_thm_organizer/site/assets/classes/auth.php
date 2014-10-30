<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        Auth
 * @description Auth file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class Auth for component com_thm_organizer
 *
 * Class provides methods for authenticate the user
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THMAuth
{
    /**
     * Joomla data abstraction
     *
     * @var    DataAbstraction
     */
    private $_JDA = null;

    /**
     * Config
     *
     * @var    Object
     */
    private $_cfg = null;

    /**
     * Constructor with the joomla data abstraction object and configuration object
     *
     * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
     * @param   MySchedConfig    $cfg  A object which has configurations including
     */
    public function __construct($JDA, $cfg)
    {
        $this->_JDA = $JDA;
        $this->_cfg = $cfg;
    }

    /**
     * Method to check the username and password
     *
     * @param   String  $username   The username to check
     * @param   String  $passwd     The password to check
     * @param   Array   $addRights  Additional rights that this user have
     *
     * @return An array which has the result including
     */
    public function ldap( $username, $passwd, $addRights = null )
    {
        $ldap = new LdapAuth(
                    $this->_cfg['ldap_server'], $this->_cfg['ldap_base'],
                    $this->_cfg['ldap_filter'], $this->_cfg['ldap_protocol'], $this->_cfg['ldap_useSSH']
                );
        $user = $ldap->authenticateUser($username, $passwd);
        if (!empty($user))
        {
            $role = self::mapLdapRole($user[ 'role' ]);

            // ALLES OK
            return array(
                 'success' => true,
                    'username' => $username,
                    'role' => $role, // User, registered, author, editor, publisher
                    'additional_rights' => $addRights // 'doz' => array( 'knei', 'igle' ), ...
            );
        }

        // FEHLER
        return array(
             'success' => false,
             'errors' => array(
                     'reason' => 'Authentifizierung fehlgeschlagen. Username oder Passwort falsch.'
             )
        );
    }

    /**
     * Method to map the LDAPP user roles to the MySched roles
     *
     * @param   String  $role  The user role
     *
     * @return The mapped role
     */
    public function mapLdapRole( $role )
    {
        // Mapping der LdapRole auf die Rollen von MySched
        switch ($role)
        {
            case "P":
                // Professor
            case "L":
                // Lehrbeauftragter
                $role = 'author';
                break;
            case "M":
                // Mitarbeiter
                $role = 'registered';
                break;
            case "S":
                // Student
            case "A":
                // Azubi
            case "E":
                // Externer Mitarbeiter
            case "R":
                // Praktikant
            case "U":
                // Undefiniert
            default:
                $role = 'registered';
                break;
        }
        return $role;
    }

    /**
     * Method to check the joomla sid is correct
     *
     * @param   String  $token  The joomla sid
     *
     * @return An array with the result
     */
    public function joomla( $token )
    {
        $addRights = array( );
        JFactory::getSession()->set('joomlaSid', $token);

        $userRoles = $this->_JDA->getUserRoles();
        $userRole = reset($userRoles);

        return array(
                'success' => true,
                'username' => $this->_JDA->getUserName(),
                'role' => strtolower($userRole), // User, registered, author, editor, publisher
                'additional_rights' => $addRights // 'doz' => array( 'knei', 'igle' ), ...
        );
    }

    /**
     * Method to check the session id
     *
     * @param   String  $sid  The session id
     *
     * @return Boolean If $sid is the same as the current session id it returns true otherwise false
     */
    public function checkSession( $sid )
    {
        return session_id() == $sid;
    }
}
