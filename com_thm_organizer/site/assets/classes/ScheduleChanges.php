<?php
/**
 * @version	    v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		ScheduleChanges
 * @description ScheduleChanges file from com_thm_organizer
 * @author	    Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

require_once dirname(__FILE__) . '/auth.php';

/**
 * Class ScheduleChanges for component com_thm_organizer
 *
 * Class provides methods to deal with schedule changes
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class ScheduleChanges
{
	/**
	 * Joomla session id
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_jsid = null;

	/**
	 * Semester id
	 *
	 * @var    String
	 * @since  1.0
	 */
	private $_sid = null;

	/**
	 * Semester id
	 *
	 * @var    Integer
	 * @since  1.0
	 */
	private $_semesterID = null;

	/**
	 * Id
	 *
	 * @var    Integer
	 * @since  1.0
	 */
	private $_id = null;

	/**
	 * Config
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_cfg = null;

	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 * @since  1.0
	 */
	private $_JDA = null;

	/**
	 * JSON
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_json = null;

	/**
	 * Auth Object
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_auth = null;

	/**
	 * Constructor with the joomla data abstraction object and configuration object
	 *
	 * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
	 * @param   MySchedConfig    $CFG  A object which has configurations including
	 *
	 * @since  1.5
	 *
	 */
	public function __construct($JDA, $CFG)
	{
		$this->_jsid              = $JDA->getRequest("jsid");
		$this->_sid               = $JDA->getRequest("sid");
		$this->_semesterID 		 = $JDA->getRequest("semesterID");
		$this->_id                = $JDA->getRequest("id");
		$this->_cfg               = $CFG->getCFG();
		$this->_JDA = $JDA;
		$this->_json = file_get_contents("php://input");
		$this->_auth = new Auth($this->_JDA, $this->_cfg);
	}

	/**
	 * Method to save the schedules changes
	 *
	 * @return Array An array with information about the status of the save process
	 */
	public function save()
	{
		if ($this->_jsid && $this->_auth->checkSession($this->_sid))
		{
			$res = $this->_JDA->query("SELECT username FROM " . $this->_cfg['jdb_table_session'] . " WHERE session_id ='" . $this->_jsid . "'");
			if (count($res) == 1)
			{
				$data     = $res[0];
				$username = $data->username;

				$res               = $this->_JDA->query("SELECT username as author, organization, semesterDesc FROM #__thm_organizer_semesters " .
									 	"INNER JOIN ON #__users manager = #__users.id WHERE #__thm_organizer_semesters.id = " . $this->_semesterID
									 );
				$ret               = $res[0];
				$author            = $ret->_author;
				$this->_semesterID = $ret->orgunit . "-" . $ret->semester;
				$counter           = 1;

				/**
				 * This loop works similar to CSMA
				 **/
				$ret = $this->updateChangeLog($this->_cfg['db_table'], $username, $author);
				while ($ret["code"] != 1)
				{
					if ($ret["code"] == 0)
					{
						break;
					}
					if ($counter == 3)
					{
						break;
					}
					else
					{
						sleep(rand($counter, $counter * 2));
						$counter++;
						$ret = $this->updateChangeLog($this->_cfg['db_table'], $username, $author);
					}
				}
				return array("success" => true,"data" => array(
					 'code' => $ret["code"],
					 'reason' => $ret["reason"],
					 'counter' => $counter
				));
			}
			else
			{
				return array("success" => true,"data" => array(
					 'code' => 2,
					 'reason' => "Username not found",
					 'counter' => "0"
				));
			}
		}
		else
		{
			// FEHLER
			return array("success" => false,"data" => array(
				 'code' => 3,
				 'reason' => 'Ihre Sitzung ist abgelaufen oder ungültig. Bitte melden Sie sich neu an.'
			));
		}
	}

	/**
	 * This Function try to save the given lessons.
	 *
	 * @param   String  $db_table  The table name of the user schedules
	 * @param   String  $username  A String representing the username
	 * @param   String  $author    The responsible of all plans
	 *
	 * @return Array This array contains a code and reason element
	 */
	private function updateChangeLog($db_table, $username, $author)
	{
		$timestamp = time();
		$res = $this->_JDA->query("UPDATE " . $db_table . " SET checked_out = '" . date("Y-m-d H:i:s", $timestamp) .
					"' WHERE username = '$this->_semesterID' AND checked_out IS NULL"
			   );

		if ($this->_JDA->getDBO()->getAffectedRows() == 1)
		{
			// Datenspalte gesperrt und bereit zum mergen
			$changearr = json_decode($this->_json);

			$res      = $this->_JDA->query("SELECT data FROM " . $db_table . " WHERE username='$this->_semesterID'");
			$dbarr    = json_decode($res[0]->data);
			$newdbarr = $dbarr;

			/**
			 * Ersetzt Veranstaltungen derren Keys gleich sind und entfernt Veranstaltungen von diesem
			 * Benutzer welche nicht mehr da sind.
			 **/
			if (is_array($dbarr))
			{
				foreach ($dbarr as $index => $dbitem)
				{
					if ((($dbitem->owner == $username || $author == $username) && $dbitem->responsible == $this->_id) || $this->_id == "respChanges")
					{
						$found = false;
						foreach ($changearr as $changeitem)
						{
							if ($dbitem->key == $changeitem->key)
							{
								$newdbarr[$index] = $changeitem;
								$found              = true;
							}
						}
						if (!$found)
						{
							unset($newdbarr[$index]);
						}
					}
				}
			}
			else
			{

			}

			if (is_array($newdbarr))
			{
				$newdbarr = array_values($newdbarr);
			}
			else
			{

			}

			/**
			 * F�gt neue Veranstaltungen hinzu
			 **/
			foreach ($changearr as $changeitem)
			{
				$found = false;
				foreach ($dbarr as $index => $dbitem)
				{
					if ($dbitem->key == $changeitem->key)
					{
						$found = true;
					}
					else
					{

					}
				}
				if (!$found)
				{
					$newdbarr[] = $changeitem;
				}
				else
				{

				}
			}

			$this->_json = $this->array_encode_json($newdbarr);
			$this->_json = $this->_JDA->getDBO()->getEscaped($this->_json);
			$res  = $this->_JDA->query("UPDATE " . $db_table . " SET data = '$this->_json', checked_out = NULL, created = '$timestamp' " .
						"WHERE username = '$this->_semesterID' AND checked_out IS NOT NULL"
					);
			return array(
				 'code' => 1,
					'reason' => 'Successful Update'
			);
		}
		else
		{
			$this->_json = $this->_JDA->getDBO()->getEscaped($this->_json);
			$res  = $this->_JDA->query("INSERT INTO " . $db_table . " (username, data, created, checked_out) " .
						"VALUES ('$this->_semesterID', '$this->_json', '$timestamp', NULL)"
					);
			if ($this->_JDA->getDBO()->getAffectedRows() == -1)
			{
				// Spalte gerade gesperrt
				return array(
					 'code' => 2,
						'reason' => 'Locked'
				);
			}
			else
			{
				return array(
					 'code' => 1,
						'reason' => "Successful Insert"
				);

			}
		}
	}

	/**
	 * The function transform a array into a string like json_encode but this function can handle special characters.
	 *
	 * @param   Object  $arr  An array
	 *
	 * @return  String  Return a string representation of the $arr
	 */
	private function array_encode_json($arr)
	{
		$retstring = "[";
		if (is_array($arr))
		{
			if (count($arr) > 0)
			{
				foreach ($arr as $arritem)
				{
					if ($retstring != "[")
					{
						$retstring = $retstring . ",";
					}
					$tempstring = "{";
					$retstring = $retstring . $tempstring . "}";
				}
				$retstring = $retstring . "]";
				return $retstring;
			}
			else
			{
				return "[]";
			}
		}
		else
		{
			return "[]";
		}
	}
}
