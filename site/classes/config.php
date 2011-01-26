<?php
/*
 * Datenbanktabelle
CREATE TABLE `schedules` (
`username` VARCHAR( 100 ) NOT NULL ,
`data` TEXT NOT NULL ,
`created` INT NOT NULL
) ENGINE = MYISAM ;
*/

// Definiert Basepath
define ('B', dirname(__FILE__));

// Daten fuer WebService API von eStudy
class mySchedConfig {

    var $cfg = Array();

    function __construct($JDA) {

        $settings = $JDA->getSettings();

        // Daten fuer WebService API von eStudy
        $this->cfg['estudyPath'] = $settings->eStudyPath;
        $this->cfg['estudyWsapiPath'] = $this->cfg['estudyPath'] .$settings->eStudywsapiPath;
        $this->cfg['estudyCreateCoursePath'] = $this->cfg['estudyPath'] . $settings->eStudyCreateCoursePath;
        $this->cfg['soapSchema'] = $settings->eStudySoapSchema;
        // Schaltet die Authentifizierung in einen Testmodus
        // der alle Kombinationen akzeptiert
        $this->cfg['AUTH_TEST_MODE'] = false;

        // HTTPS bei Authentifizierung erzwingen?
        $this->cfg['REQUIRE_HTTPS'] = false;

        // die XML Datei mit den Stundenplandaten
        $this->cfg['xml_scheduleFile'] = B.'/mySched/schedule_full.xml';
        // Hier werden die PDF Dateien gespeichert (Muss nicht im Webroot liegen!)
        $this->cfg['pdf_downloadFolder'] = $settings->downFolder;
        // Die ID der Ferien/Feiertage Kategorie
        $this->cfg['vacation_id'] = $settings->vacationcat;        //LSF adress
        $this->cfg['lsf_adress'] = "http://www-test.mni.fh-giessen.de:8080/axis2/services/dbinterface?wsdl";
        //Wenn $cfg['sync_files'] = 1 dann wird beim ICal Download dem Benutzer
        //ein Link zu der ICal Datei ausgegeben.
        //Die ICal Datei wird auf dem Server gespeichert in einem Ordner
        //Ordnername: (Benutzer hgNummer) + (Benutzer Anmeldedatum)
        $this->cfg['sync_files'] = 0;

        /**
         * AUTH/LDAP - CONFIG
         */
        // Ldap einstellungen
        $this->cfg['ldap_server'] = 'ldap.fh-giessen.de';
        $this->cfg['ldap_base'] = 'DC=fh-giessen-friedberg,DC=de';
        $this->cfg['ldap_filter'] = '(& (uid=%s)(objectclass=posixaccount)  (| (ou=MNI)(ou=KMUB) ) )';
        $this->cfg['ldap_protocol'] = 3;
        $this->cfg['ldap_useSSH'] = true;

        /**
         * DATABASE - CONFIG
         */
        // Daten fuer MySched Datenbank
        $this->cfg['db_host'] = 'localhost';            //Adresse des Servers auf dem die Datenbankl&iuml;&iquest;&frac12;uft
        $this->cfg['db_port'] = '3306';        //Port unter dem die Datenbank erreichbar ist
        $this->cfg['db_user'] = 'root';        //Benutzername der Zugriff auf die Datenbank hat
        $this->cfg['db_pass'] = '';        //Passwort f&iuml;&iquest;&frac12;r den Benutzer
        $this->cfg['db_database'] = 'joomla';            //Datenbank die von Joomla benutzt wird
        $this->cfg['db_table'] = 'jos_giessen_scheduler_user_schedules';        //Tabelle in der die pers&iuml;&iquest;&frac12;nlichen Stundenpl&iuml;&iquest;&frac12;ne der Benutzer gespeichert werden
        $this->cfg['db_scheduletable'] = 'jos_giessen_scheduler_schedules'; //Tabelle in der die hochgeladenen Stundenpl&iuml;&iquest;&frac12;ne (XML Datei) gespeichert werden

        // Daten fuer Joomla Datenbank
        $this->cfg['jdb_host'] = 'localhost';    //Adresse des Servers auf dem die Datenbank l&iuml;&iquest;&frac12;uft
        $this->cfg['jdb_port'] = '3306';        //Port unter dem die Datenbank erreichbar ist
        $this->cfg['jdb_user'] = 'root';        //Benutzername der Zugriff auf die Datenban hat
        $this->cfg['jdb_pass'] = '';        //Passwort f&iuml;&iquest;&frac12;r den Benutzer
        $this->cfg['jdb_database'] = 'joomla';    //Datenbank die von Joomla benutzt wird
        $this->cfg['jdb_table_session'] = 'jos_session';        //Tabelle in der Joomla die einzelnen Sessions abspeichert
        $this->cfg['jdb_table_user'] = 'jos_users';    //Tabelle in der alle angemeldeten Joomla Benutzer gespeichert sind
        $this->cfg['jdb_table_events'] = 'jos_giessen_scheduler_events';    //Tabelle in der die Einzel Termine gespeichert sind
        $this->cfg['jdb_table_event_cat'] = 'jos_eventlist_categories'; //Tabelel in der die Kategorien gespeichert sind.
        $this->cfg['jdb_table_event_objects'] = 'jos_giessen_scheduler_eventobjects';
        $this->cfg['jdb_table_objects'] = 'jos_giessen_scheduler_objects';
        $this->cfg['jdb_table_categories'] = 'jos_giessen_scheduler_categories';
        $this->cfg['jdb_table_semester'] = 'jos_giessen_scheduler_semester';

    }

    function getCFGElement($s)
    {
        return $this->cfg[$s];
    }

    function getCFG()
    {
        return $this->cfg;
    }

    function getestudyPath() {

        return $this->cfg['estudyPath'];
    }

    function getestudyWsapiPath() {
        return $this->cfg['estudyWsapiPath'];
    }

    function getestudyCreateCoursePath() {
        return $this->cfg['estudyCreateCoursePath'];
    }

    function getSoapSchema() {
        return $this->cfg['soapSchema'];
    }
}
?>
