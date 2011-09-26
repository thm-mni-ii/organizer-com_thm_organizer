<?php

/**
 * @author m.homeniuk
 * @version 0.4
 */
class mySchedImport {
	private $cfg;
	private $clientCourse;
	private $clientCalendar;

	function __construct($username, $joomlaSID, $CFG) {
		try
		{
			require_once('Zend/Soap/Client.php');
			ini_set("soap.wsdl_cache_enabled", "0");
			$this->cfg = $CFG;
			$this->clientCourse = new Zend_Soap_Client($this->cfg->getestudyWsapiPath() . '/course.php?wsdl');
			$this->clientCourse->addSoapInputHeader(
				@new SoapHeader(
					$this->cfg->getSoapSchema(),
					"authenticateUser",
					array(
						$username,
						$joomlaSID
					)
				),
				true
			);

			$this->clientCalendar = new Zend_Soap_Client($this->cfg->getestudyWsapiPath() . '/calendar.php?wsdl');
			$this->clientCalendar->addSoapInputHeader(
				@new SoapHeader(
					$this->cfg->getSoapSchema(),
					"authenticateUser",
					array(
						$username,
						$joomlaSID)
				),
				true
			);
		}
		catch(Exception $e)
		{}
	}


	function getiCalender() {

		try {
			return $this->clientCalendar->iCalender();
		}
		catch(SoapFault $fault) {
			return false;
		}
	}

	/**
	 * @return liefert
	 */
	function getCalendar() {

		try {
			$calendar = $this->clientCalendar->calendar();
			if(isset($calendar->item))
				return $calendar->item;
		}
		catch(SoapFault $fault) {
			return false;
		}
	}
	function getJsonCalendar() {

		try {
			return $this->clientCalendar->jsonCalendar();
		}
		catch(SoapFault $fault) {
			return false;
		}
	}

	//boolean exists(string $username, string $modulShortCut, string $semester)
	/**
	 *
	 * @param $dozentUserNamen ein Assoziatives Array mit den Dozentennamen als Werte
	 * @param $modulShortCut Modulkuerzel
	 * @param $semester Semester z.B. 09WS fuer das Wintersemester im Jahr 2009
	 *
	 * @return true wenn der Kurs existiert sonst false
	 */
	function existsCourse($dozentUserNamen, $modulShortCut, $semester) {

		try {
			if(!is_array($dozentUserNamen)) {
				$existsCourse = $this->clientCourse->exists($dozentUserNamen, $modulShortCut, $semester);
    			if($existsCourse) {
    				return true;
    			}
    			return false;
			}
			foreach($dozentUserNamen as $key => $value) {
    			$existsCourse = $this->clientCourse->exists($value->id, $modulShortCut, $semester);
    			if($existsCourse) {
    				return true;
    			}
			}
			return false;
		}
		catch(SoapFault $fault) {
			return $fault;
		}
	}

	/**
	 * Die Methode liefert einen Kurslink. Dabei wird werden folgende Unterscheidungen gemacht:
	 * Falls der Kurs vorhanden ist wird unterschieden ob der User im Kurs bereitsangemeldet ist
	 * oder nicht. Falls ja, wird der dazugehoerige Kurslink zurueck gegeben. Falls nein, wird der
	 * dazugehoerige Link zum Anmelden zurueckgegeben
	 * Falls der Kurs nicht vorhanden ist und der, mit dem Parameter "$userName" uebergebene User
	 * berechtigt ist in eStudy einen Kurs anzulegen, wird ein Link zum Kursanlegen zurueckgegeben.
	 * Ist der Kurs nicht vorhanden und der User besitzt keinen Berechtigung einen Kurs in eStudy
	 * anzulegen wird ein leerer String zurueckgegeben.
	 *
	 * @param $dozentUserNamen ein Assoziative Array mit den Dozentennamen als Werte
	 * @param $modulShortCut Modulkuerzel
	 * @param $semester Semester z.B. 09WS fuer das Wintersemester im Jahr 2009
	 *
	 * @return liefert einen Kurslink
	 */
	function getCourseLink($dozentUserNamen, $modulShortCut, $semester) {

		try {
			//Pruefen ob der Kurs existiert

			$existsCourse = false;
			$dozentUserName = "";
			if(!is_array($dozentUserNamen)) {
				$existsCourse = $this->clientCourse->exists($dozentUserNamen, $modulShortCut, $semester);
    			if($existsCourse) {
    				$dozentUserName = $dozentUserNamen;
    			}
			}
			else {
			foreach($dozentUserNamen as $key => $value) {
    			$existsCourse = $this->existsCourse($value->id, $modulShortCut, $semester);
    			if($existsCourse) {
    				$dozentUserName = $value->id;
    				break;
    			}
			}
			}

			if($existsCourse) {
				//Pruefen ob der User im Kurs eingetragen ist
				$courseId = $this->getCourseId($dozentUserName, $modulShortCut, $semester);
				$userInCourse = $this->inCourse($courseId);
				if($userInCourse) {
					//ist der User im Kurs wird Kurslink zurueckgegeben
					$link = $this->cfg->getestudyPath() . '/news/news.php?changeToCourse=' . $courseId;
					return $link;
				}
				else {
					//ist der User nicht im Kurs wird der Link zum Anmelden ausgegeben
					$link = $this->cfg->getestudyPath(). '/courses/register.php?courseID=' . $courseId;
					return $link;
				}
			}
			else {
				//Falls der Kurs nicht existiert, wird die Rolle des Users im eStudy geprueft und danach der entsprechende Link gesetzt
				$canCreateCourse = $this->canCreateCourse();
				if($canCreateCourse) {
					return $this->cfg->getestudyCreateCoursePath();
				}
				else {
					return "";
				}
			}
		}
		catch(SoapFault $fault) {
			return false;
		}
	}

	//int id(string $username, string $modulShortCut, string $semester)
	/**
	 * @return liefert die Kurs-ID, wenn der Kurs nicht existiert false
	 */
	function getCourseId($dozentUserName, $modulShortCut, $semester) {

		try {
			$courseId = $this->clientCourse->id($dozentUserName, $modulShortCut, $semester);
			if($courseId != 0) {
				return $courseId;
			}
			else {
				return false;
			}
		}
		catch(SoapFault $fault) {
			return $fault;
		}
	}

	//int role(string $username, int $courseID)
	/**
	 * Liefert die Rolle des Users im Kurs besitzt
	 *
	 * @param $courseID die Kurs-ID wie sie in eStudy hinterlegt ist
	 *
	 * @return liefert 0 keine spezielle Rolle, normale Berechtigung
	 * 					1 fuer Admin
	 * 					2 fuer Student
	 * 					3 fuer Dozent
	 * 					4 fuer Sekretariat
	 * 					5 fuer Alumnus
	 * 					6 fuer Schueler
	 * 					7 fuer Gast
	 */
	function getUserRole($courseID) {

		try {
			return $this->clientCourse->role($courseID);
		}
		catch(SoapFault $fault) {
			return false;
		}
	}

	//boolean inCourse(string $username, int $courseID)
	/**
	 *
	 * @param $courseID die Kurs-ID wie sie in eStudy hinterlegt ist
	 *
	 * @return liefert true wenn der User im Course ist, sonst false
	 */
	function inCourse($courseID) {

		try {
			return $this->clientCourse->inCourse($courseID);
		}
		catch(SoapFault $fault) {
			return $fault;
		}
	}

	//boolean canCreateCourse(string $username, string $token)
	/**
	 * @return liefert true wenn der User einen Kurs anlegen kann, sonst false
	 */
	function canCreateCourse() {

		try {
			return $this->clientCourse->canCreateCourse();
		}
		catch(SoapFault $fault) {
			return $fault;
		}
	}
}

?>