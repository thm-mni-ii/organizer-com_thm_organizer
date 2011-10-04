<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( dirname( __FILE__ ) . '/auth.php' );
// Dummywerte, nur zum testen
// *********************

class User
{
	private $username = null;
	private $token = null;
	private $passwd = null;
	private $cfg = null;
	private $JDA = null;

	public function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->username = $this->JDA->getRequest( "username" );
		$this->token    = $this->JDA->getRequest( "token" );
		$this->passwd   = $this->JDA->getRequest( "passwd" );
		$this->cfg = $CFG->getCFG();
	}

	public function auth()
	{
		if ( isset( $this->cfg[ 'AUTH_TEST_MODE' ] ) )
		    if ( $this->cfg[ 'AUTH_TEST_MODE' ] ) {
		        // hgNummer des Users - Ist die Id zum speichern des Stundenplans
		        // Jede weitere Verarbeitung wird abgebrochen
		        $_REQUEST  = array( );
		        // Rolle des Users - bestimmt mit welchen Rechten der User die Plaene sieht
		        $role = 'registered';
		        // Hier koennen doz, room, clas spezifische Rechte gesetzt werden - Alle angaben ergaenzen die RollenRechte
		        $addRights = array(
		             'doz' => array(
		                 'knei',
		                'igle'
		            )
		        );
		        // ALLES OK
		        return array("success"=>true, "data"=>array(
		            'username' => $this->username,
		            'role' => $role, // user, registered, author, editor, publisher
		            'additional_rights' => $addRights // 'doz' => array('knei', 'igle' ), ...
		        ) );
		        exit( );
		    }

		// Nur Anfragen ueber HTTPS werden zugelassen -
		if ( isset( $this->cfg[ 'REQUIRE_HTTPS' ] ) )
		    if ( $this->cfg[ 'REQUIRE_HTTPS' ] && !strstr( strtolower( $_SERVER['SERVER_PROTOCOL' ] ), 'https' ) ) {
		        return array("success"=>true, "data"=> array(
		             'error' => "Schwerer Fehler: Keine Verschl&Atilde;&frac14;sselte Verbindung vorhanden!"
		        ) );
		        exit( );
		    }

		// Nur Token Verifikation - Token ist die SessionId von Joomla und wird mit der DB verglichen
		if ( $this->token ) {
		    //***************************************
		    // Ueberpruefung ob Token korrekt sind
		    //***************************************
		    $auth = new Auth($this->JDA, $this->cfg);
		    return array("data"=>$auth->joomla( $this->token ) );

		    // Hier werden die Logindaten des Users gecheckt
		} elseif ( $this->username && $this->passwd ) {
		    //***************************************
		    // Ueberpruefung ob Angaben korrekt sind
		    //***************************************
		    $auth = new Auth($this->JDA, $this->cfg);
		    return array("data"=> $auth->ldap( $this->username, $this->passwd ) );
		}

	}
}

?>
