<?php
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

		// Verifizierung der Joomla SessionID und abfragen von Usernamen und Gruppe
		$res = $this->JDA->query( "SELECT username FROM " . $this->cfg[ 'jdb_table_session' ] . " WHERE session_id='$token'" );
		if ( count( $res ) == 1 ) {
			$data = $res[ 0 ];

			$userroles = $this->JDA->getUserRoles();
			$userrole = "user";
			if(is_array($userroles))
				foreach($userroles as $k=>$v)
					$userrole = $k;

			// ALLES OK
			return array(
				'success' => true,
				'username' => $data->username,
				'role' => strtolower( $userrole ), // user, registered, author, editor, publisher
				'additional_rights' => $addRights // 'doz' => array( 'knei', 'igle' ), ...
			);
		}

		// FEHLER
		return array(
			 'success' => false,
			'clearToken' => true,
			'errors' => array(
				 'reason' => 'Authentifizierung fehlgeschlagen. Ihr Login konnte nicht von der Fachhochschulhomepage übernommern werden. Bitte melden Sie sich erneut an.'
			)
		);
	}

	// Prueft ob die uebergebene ID die aktuelle SessionID ist
	public function checkSession( $sid )
	{
		return session_id() == $sid;
	}
}
?>