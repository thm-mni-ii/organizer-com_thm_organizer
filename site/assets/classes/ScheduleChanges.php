<?php

/**
 * @author Wolf Rost
 * @version 1.0
 **/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( dirname( __FILE__ ) . '/auth.php' );

class ScheduleChanges
{
	private $jsid = null;
	private $sid = null;
	private $class_semester_id = null;
	private $id = null;
	private $cfg = null;
	private $JDA = null;
	private $json = null;
	private $auth = null;

	function __construct($JDA, $CFG)
	{
		$this->jsid              = $JDA->getRequest( "jsid" );
		$this->sid               = $JDA->getRequest( "sid" );
		$this->class_semester_id = $JDA->getRequest( "class_semester_id" );
		$this->id                = $JDA->getRequest( "id" );
		$this->cfg               = $CFG->getCFG();
		$this->JDA = $JDA;
		$this->json = file_get_contents( "php://input" );
		$this->auth = new Auth($this->JDA, $this->cfg);
	}

	public function save()
	{
		if ( $this->jsid && $this->auth->checkSession( $this->sid ) ) {
			$res = $this->JDA->query( "SELECT username FROM " . $this->cfg[ 'jdb_table_session' ] . " WHERE session_id ='" . $this->jsid . "'" );
			if ( count( $res ) == 1 ) {
				$data     = $res[ 0 ];
				$username = $data->username;

				$res               = $this->JDA->query( "SELECT username as author, organization, semesterDesc FROM #__thm_organizer_semesters INNER JOIN ON #__users manager = #__users.id WHERE #__thm_organizer_semesters.id = " . $this->class_semester_id );
				$ret               = $res[ 0 ];
				$author            = $ret->author;
				$this->class_semester_id = $ret->orgunit . "-" . $ret->semester;
				$counter           = 1;

				/**
				 * This loop works similar to CSMA
				 **/
				$ret = $this->updateChangeLog( $this->cfg[ 'db_table' ], $username, $author );
				while ( $ret[ "code" ] != 1 ) {
					if ( $ret[ "code" ] == 0 ) {
						break;
					}
					if ( $counter == 3 )
						break;
					else {
						sleep( rand( $counter, $counter * 2 ) );
						$counter++;
						$ret = $this->updateChangeLog( $this->cfg[ 'db_table' ], $username, $author );
					}
				}
				return array("success"=>true,"data"=>array(
					 'code' => $ret[ "code" ],
					'reason' => $ret[ "reason" ],
					'counter' => $counter
				) );
			} else {
				return array("success"=>true,"data"=>array(
					 'code' => 2,
					'reason' => "Username not found",
					'counter' => "0"
				) );
			}
		} else {
			// FEHLER
			return array("success"=>false,"data"=>array(
				 'code' => 3,
				'reason' => 'Ihre Sitzung ist abgelaufen oder ungültig. Bitte melden Sie sich neu an.'
			) );
		}
	}

	/**
	 * This Function try to save the given lessons.
	 * @param $db_table string The table name of the user schedules
	 * @param $db object A database object
	 * @param $this->json string A String representation of the personal lesson array
	 * @param $username string A String representing the username
	 * @param $this->class_semester_id string A String representing a combination of 'orgunit'-'semester'
	 * @param $this->id string The Id of the current saving schedule
	 * @param $author string The responsible of all plans
	 * @return array This array contains a code and reason element
	 **/

	private function updateChangeLog( $db_table, $username, $author )
	{
		$timestamp = time();
		$res = $this->JDA->query( "UPDATE " . $db_table . " SET checked_out = '" . date( "Y-m-d H:i:s", $timestamp ) . "' WHERE username = '$this->class_semester_id' AND checked_out IS NULL" );

		if ( $this->JDA->getDBO()->getAffectedRows() == 1 ) {
			//Datenspalte gesperrt und bereit zum mergen
			$changearr = json_decode( $this->json );

			$res      = $this->JDA->query( "SELECT data FROM " . $db_table . " WHERE username='$this->class_semester_id'" );
			$dbarr    = json_decode( $res[ 0 ]->data );
			$newdbarr = $dbarr;

			/**
			 * Ersetzt Veranstaltungen derren Keys gleich sind und entfernt Veranstaltungen von diesem
			 * Benutzer welche nicht mehr da sind.
			 **/
			if ( is_array( $dbarr ) );
			foreach ( $dbarr as $index => $dbitem ) {
				if ( ( ( $dbitem->owner == $username || $author == $username ) && $dbitem->responsible == $this->id ) || $this->id == "respChanges" ) {
					$found = false;
					foreach ( $changearr as $changeitem ) {
						if ( $dbitem->key == $changeitem->key ) {
							$newdbarr[ $index ] = $changeitem;
							$found              = true;
						}
					}
					if ( !$found ) {
						unset( $newdbarr[ $index ] );
					}
				}
			}
			if ( is_array( $newdbarr ) )
				$newdbarr = array_values( $newdbarr );
			/**
			 * F�gt neue Veranstaltungen hinzu
			 **/
			foreach ( $changearr as $changeitem ) {
				$found = false;
				foreach ( $dbarr as $index => $dbitem ) {
					if ( $dbitem->key == $changeitem->key ) {
						$found = true;
					}
				}
				if ( !$found ) {
					$newdbarr[ ] = $changeitem;
				}
			}

			$this->json = $this->array_encode_json( $newdbarr );
			$this->json = $this->JDA->getDBO()->getEscaped( $this->json );
			$res  = $this->JDA->query( "UPDATE " . $db_table . " SET data = '$this->json', checked_out = NULL, created = '$timestamp' WHERE username = '$this->class_semester_id' AND checked_out IS NOT NULL" );
			return array(
				 'code' => 1,
				'reason' => 'Successful Update'
			);
		} else {
			$this->json = $this->JDA->getDBO()->getEscaped( $this->json );
			$res  = $this->JDA->query( "INSERT INTO " . $db_table . " (username, data, created, checked_out) VALUES ('$this->class_semester_id', '$this->json', '$timestamp', NULL)" );
			if ( $this->JDA->getDBO()->getAffectedRows() == -1 ) {
				//Spalte gerade gesperrt
				return array(
					 'code' => 2,
					'reason' => 'Locked'
				);
			} else {
				return array(
					 'code' => 1,
					'reason' => "Successful Insert"
				);

			}
		}
	}

	/**
	 * The function transform a array into a string like json_encode but this function can handle special characters.
	 * @param {object} $arr An array.
	 * @return {string} Return a string representation of the $arr.
	 */
	private function array_encode_json( $arr )
	{
		$retstring = "[";
		if ( is_array( $arr ) ) {
			if ( count( $arr ) > 0 ) {
				foreach ( $arr as $arritem ) {
					if ( $retstring != "[" )
						$retstring = $retstring . ",";
					$tempstring = "{";
					foreach ( $arritem as $k => $v ) {
						if ( $tempstring == "{" )
							if ( is_string( $v ) )
								$tempstring = $tempstring . "\"" . $k . "\":\"" . str_replace( "\"", "\\\"", $v ) . "\"";
							else
								$tempstring = $tempstring . "\"" . $k . "\":" . str_replace( "\"", "\\\"", $v ) . "";
						else if ( is_string( $v ) )
							$tempstring = $tempstring . ",\"" . $k . "\":\"" . str_replace( "\"", "\\\"", $v ) . "\"";
						else
							$tempstring = $tempstring . ",\"" . $k . "\":" . str_replace( "\"", "\\\"", $v ) . "";
					}
					$retstring = $retstring . $tempstring . "}";
				}
				$retstring = $retstring . "]";
				return $retstring;
			} else {
				return "[]";
			}
		} else {
			return "[]";
		}
	}
}
?>