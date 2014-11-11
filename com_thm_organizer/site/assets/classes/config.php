<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        MySchedConfig
 * @description MySchedConfig file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

// Definiert Basepath
define('B', dirname(__FILE__));

/**
 * Class MySchedConfig for component com_thm_organizer
 *
 * Class provides information about the database and estudy
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class MySchedConfig
{
    /**
     * The mysched configuration array
     *
     * @var    Array
     */
    private $_cfg = Array();

    /**
     * Constructor with the joomla data abstraction object
     *
     * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
     */
    public function __construct($JDA)
    {
        $settings = $JDA->getSettings();

        // Daten fuer WebService API von eStudy
        $this->_cfg['estudyPath'] = $settings->eStudyPath;
        $this->_cfg['estudyWsapiPath'] = $this->_cfg['estudyPath'] . $settings->eStudywsapiPath;
        $this->_cfg['estudyCreateCoursePath'] = $this->_cfg['estudyPath'] . $settings->eStudyCreateCoursePath;
        $this->_cfg['soapSchema'] = $settings->eStudySoapSchema;

        // HTTPS bei Authentifizierung erzwingen?
        $this->_cfg['REQUIRE_HTTPS'] = false;

        // Die XML Datei mit den Stundenplandaten
        $this->_cfg['xml_scheduleFile'] = B . '/mySched/schedule_full.xml';

        // Hier werden die PDF Dateien gespeichert (Muss nicht im Webroot liegen!)
        $this->_cfg['pdf_downloadFolder'] = $JDA->getDownloadFolder();

        // Die ID der Ferien/Feiertage Kategorie
        $this->_cfg['vacation_id'] = $settings->vacationcat;

        // LSF adress
        $this->_cfg['lsf_adress'] = "http://www-test.mni.fh-giessen.de:8080/axis2/services/dbinterface?wsdl";

        /* Wenn $cfg['sync_files'] = 1 dann wird beim ICal Download dem Benutzer
         * in Link zu der ICal Datei ausgegeben.
        * Die ICal Datei wird auf dem Server gespeichert in einem Ordner
        * Ordnername: (Benutzer hgNummer) + (Benutzer Anmeldedatum)
        */
        $this->_cfg['sync_files'] = 0;

        /*
         * AUTH/LDAP - CONFIG
        */
        $this->_cfg['ldap_server'] = 'ldap.fh-giessen.de';
        $this->_cfg['ldap_base'] = 'DC=fh-giessen-friedberg,DC=de';
        $this->_cfg['ldap_filter'] = '(& (uid=%s)(objectclass=posixaccount)  (| (ou=MNI)(ou=KMUB) ) )';
        $this->_cfg['ldap_protocol'] = 3;
        $this->_cfg['ldap_useSSH'] = true;

        /*
         * DATABASE - CONFIG
        */
        // Adresse des Servers auf dem die Datenbankl&iuml;&iquest;&frac12;uft
        $this->_cfg['db_host'] = 'localhost';

        // Port unter dem die Datenbank erreichbar ist
        $this->_cfg['db_port'] = '3306';

        // Benutzername der Zugriff auf die Datenbank hat
        $this->_cfg['db_user'] = 'root';

        // Passwort f&iuml;&iquest;&frac12;r den Benutzer
        $this->_cfg['db_pass'] = '';

        // Datenbank die von Joomla benutzt wird
        $this->_cfg['db_database'] = 'joomla';

        // Tabelle in der die pers&iuml;&iquest;&frac12;nlichen Stundenpl&iuml;&iquest;&frac12;ne der Benutzer gespeichert werden
        $this->_cfg['db_table'] = '#__thm_organizer_user_schedules';

        // Tabelle in der die hochgeladenen Stundenpl&iuml;&iquest;&frac12;ne (XML Datei) gespeichert werden
        $this->_cfg['db_scheduletable'] = '#__thm_organizer_schedules';

        /*
         * JOOMLA DATABASE - CONFIG
        */
        // Adresse des Servers auf dem die Datenbank l&iuml;&iquest;&frac12;uft
        $this->_cfg['jdb_host'] = 'localhost';

        // Port unter dem die Datenbank erreichbar ist
        $this->_cfg['jdb_port'] = '3306';

        // Benutzername der Zugriff auf die Datenban hat
        $this->_cfg['jdb_user'] = 'root';

        // Passwort f&iuml;&iquest;&frac12;r den Benutzer
        $this->_cfg['jdb_pass'] = '';

        // Datenbank die von Joomla benutzt wird
        $this->_cfg['jdb_database'] = 'joomla';

        // Tabelle in der Joomla die einzelnen Sessions abspeichert
        $this->_cfg['jdb_table_session'] = '#__session';

        // Tabelle in der alle angemeldeten Joomla Benutzer gespeichert sind
        $this->_cfg['jdb_table_user'] = '#__users';

        // Tabelle in der die Einzel Termine gespeichert sind
        $this->_cfg['jdb_table_events'] = '#__thm_organizer_events';

        // Tabelel in der die Kategorien gespeichert sind.
        $this->_cfg['jdb_table_event_cat'] = '#__eventlist_categories';
        $this->_cfg['jdb_table_event_objects'] = '#__thm_organizer_eventobjects';
        $this->_cfg['jdb_table_objects'] = '#__thm_organizer_objects';
        $this->_cfg['jdb_table_categories'] = '#__thm_organizer_categories';
        $this->_cfg['jdb_table_semester'] = '#__thm_organizer_semesters';

    }

    /**
     * Method to get a property
     *
     * @param   String  $propertyName  The property to get
     *
     * @return  The property value
     */
    public function getCFGElement($propertyName)
    {
        return $this->_cfg[$propertyName];
    }

    /**
     * Method to get the configuration object
     *
     * @return The configuration object
     */
    public function getCFG()
    {
        return $this->_cfg;
    }

    /**
     * Method to get the estudy path
     *
     * @return The estudy path
     */
    public function getEstudyPath()
    {

        return $this->_cfg['estudyPath'];
    }

    /**
     * Method to get the estudy wsapi path
     *
     * @return The estudy wsapi path
     */
    public function getEstudyWsapiPath()
    {
        return $this->_cfg['estudyWsapiPath'];
    }

    /**
     * Method to get the estudy create course path
     *
     * @return The estudy create course path
     */
    public function getEstudyCreateCoursePath()
    {
        return $this->_cfg['estudyCreateCoursePath'];
    }

    /**
     * Method to get the soeap schema
     *
     * @return The soeap schema
     */
    public function getSoapSchema()
    {
        return $this->_cfg['soapSchema'];
    }
}
