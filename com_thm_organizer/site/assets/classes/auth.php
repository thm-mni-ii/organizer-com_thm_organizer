<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class Auth
{
	private $JDA = null;
	private $cfg = null;

	function __construct($JDA, $cfg)
	{
		$this->JDA = $JDA;
		$this->cfg = $cfg;
	}
	//*********************************************
	// LDAP Benutzername und Passwort ueberpruefen
	//*********************************************
	public function ldap( $username, $passwd, $addRights = null )
	{
		$ldap = new LdapAuth( $this->cfg[ 'ldap_server' ], $this->cfg[ 'ldap_base' ], $this->cfg[ 'ldap_filter' ], $this->cfg[ 'ldap_protocol' ], $this->cfg[ 'ldap_useSSH' ] );
		if ( $user = $ldap->authenticateUser( $username, $passwd ) ) {
			$role = self::mapLdapRole( $user[ 'role' ] );

			// ALLES OK
			return array(
				 'success' => true,
				'username' => $username,
				'role' => $role, // user, registered, author, editor, publisher
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

	//*********************************************
	// Mapping von LDAP-User-Role auf die Role von MySched
	//*********************************************
	public function mapLdapRole( $role )
	{
		// Mapping der LdapRole auf die Rollen von MySched
		switch ( $role ) {
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


	//*****************************************
	// Ueberpruefung ob Joomla SID korrekt ist
	//*****************************************
	public function joomla( $token )
	{
		$addRights               = array( );
		$_SESSION[ 'joomlaSid' ] = $token;

		$userrolearr = $this->JDA->getUserRoles();
		foreach($userrolearr as $k=>$v)
		{
			$userrole = $k;
			break;
		}

		// ALLES OK
		return array(
			'success' => true,
			'username' => $this->JDA->getUserName(),
			'role' => strtolower( $userrole ), // user, registered, author, editor, publisher
			'additional_rights' => $addRights // 'doz' => array( 'knei', 'igle' ), ...
		);
	}

	// Prueft ob die uebergebene ID die aktuelle SessionID ist
	public function checkSession( $sid )
	{
		return session_id() == $sid;
	}
}
?>