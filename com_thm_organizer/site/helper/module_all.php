<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		ModuleAll
 * @description ModuleAll component site helper
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

require_once 'lsfapi.php';
header('Content-Type: text/html; charset=utf-8');

/**
 * Class ModuleAll for component com_thm_organizer
 *
 * Class provides methods to Mapping: LSF-XML Struktur -> Objekt
 *
 * @category	Joomla.Component.Site
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class ModuleAll
{
	/**
	 * XML structure
	 *
	 * @var    Object
	 * @since  1.0
	 */
	public $xmlStructure = null;

	/**
	 * Language
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $language = null;

	/* Atribute der XML-Struktur */
	/**
	 * Module id
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $modulid = null;

	/**
	 * His number
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $nrHis = null;

	/**
	 * Mni number
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $nrMni = null;

	/**
	 * Abbreviation
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $kuerzel = null;

	/**
	 * Short name
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $kurzname = null;

	/**
	 * Short name (german)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $kurzname_de = null;

	/**
	 * Short name (english)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $kurzname_en = null;

	/**
	 * Module title (german)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $modultitelDe = null;

	/**
	 * Module title (english)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $modultitelEn = null;

	/**
	 * Major
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $studiengang = null;

	/**
	 * Version
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $pversion = null;

	/**
	 * $ktxtppflicht
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $ktxtppflicht = null;

	/**
	 * $ktxtpform
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $ktxtpform = null;

	/**
	 * Language
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $sprache = null;

	/**
	 * Creditpoints
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $lp = null;

	/**
	 * Semester week hours
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $sws = null;

	/**
	 * Pflichtsemester
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $pfsem = null;

	/**
	 * Effort (german)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $aufwand_de = null;

	/**
	 * Effort (english)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $aufwand_en = null;

	/**
	 * Group size
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $gruppengr = null;

	/**
	 * Rotation
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $turnus = null;

	/**
	 * Duration
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $dauer = null;

	/**
	 * $versemester
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $versemester = null;

	/**
	 * $verstandbearb
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $verstandbearb = null;

	/**
	 * Title (english)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $titelen = null;

	/**
	 * $fgid
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $fgid = null;

	/**
	 * Parent modul
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $parentmodul = null;

	/**
	 * $stgktxt
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $stgktxt = null;

	/**
	 * Graduation
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $abschl = null;

	/**
	 * Responsible
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $verantworlicher = null;

	/**
	 * Responsible firstname
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $verantworlicher_vorname = null;

	/**
	 * Responsible lastname
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $verantworlicher_nachname = null;

	/**
	 * Responsible ldap
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $verantworlicher_ldap = null;

	/**
	 * Teacher
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $dozent = null;

	/**
	 * Teacher ldap
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $dozent_ldap = null;

	/**
	 * Short Description (german)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $kurzbeschreibung_de = null;

	/**
	 * Short Description semester (german)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $kurzbeschreibung_de_semester = null;

	/**
	 * Short Description (english)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $kurzbeschreibung_en = null;

	/**
	 * Short Description semester (english)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $kurzbeschreibung_en_semester = null;

	/**
	 * Teaching type (german)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $lernform_de = null;

	/**
	 * Teaching type (english)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $lernform_en = null;

	/**
	 * Conditions
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $zwvoraussetzungen = null;

	/**
	 * Learning objectives (german)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $lernziel_de = null;

	/**
	 * Learning objectives (english)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $lernziel_en = null;

	/**
	 * Learning content (german)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $lerninhalt_de = null;

	/**
	 * Learning content (english)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $lerninhalt_en = null;

	/**
	 * Preliminary work (german)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $vorleistung_de = null;

	/**
	 * Preliminary work (english)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $vorleistung_en = null;

	/**
	 * Grading (german)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $leistungsnachweis_de = null;

	/**
	 * Grading (english)
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $leistungsnachweis_en = null;

	/**
	 * Bibliography
	 *
	 * @var    String
	 * @since  1.0
	 */
	public $litverz = null;

	/**
	 * Constructor to set up the client
	 *
	 * @param   <Object>  $xml   XML structure
	 * @param   <String>  $type  Type
	 * @param   <String>  $lang  Language
	 */
	public function ModuleAll($xml, $type = null, $lang = null)
	{
		$this->xmlStructure = $xml;

		if ($type == null)
		{
			$this->parseXmlForDetails();
		}
		else
		{
			$this->parseXmlForList();
		}
		$this->language = $lang;
	}

	/**
	 * Method to set the Language
	 *
	 * @param   String  $lang  Language
	 *
	 * @return void
	 */
	public function setLanguage($lang)
	{
		$this->language = $lang;
	}

	/**
	 * Method to parse the xml for the list
	 *
	 * @return void
	 */
	private function parseXmlForList()
	{
		if (isset($this->xmlStructure->modul))
		{
			$this->xmlStructure = $this->xmlStructure->modul;
		}

		if (isset($this->xmlStructure->modulid))
		{
			$this->modulid = (String) $this->xmlStructure->modulid;
		}
		if (isset($this->xmlStructure->nrhis))
		{
			$this->nrHis = (String) $this->xmlStructure->nrhis;
		}
		if (isset($this->xmlStructure->nrmni))
		{
			$this->nrMni = (String) $this->xmlStructure->nrmni;
		}
		if (isset($this->xmlStructure->kuerzel))
		{
			$this->kuerzel = (String) $this->xmlStructure->kuerzel;
		}
		if (isset($this->xmlStructure->kurzname))
		{
			$this->kurzname = (String) $this->xmlStructure->kurzname;
		}
		if (isset($this->xmlStructure->titelde))
		{
			$this->modultitelDe = (String) $this->xmlStructure->titelde;
		}
		if (isset($this->xmlStructure->titelen))
		{
			$this->modultitelEn = $this->xmlStructure->titelen;
		}
		if (isset($this->xmlStructure->pfsem))
		{
			$this->pfsem = (String) $this->xmlStructure->pfsem;
		}
		if (isset($this->xmlStructure->lp))
		{
			$this->lp = (String) $this->xmlStructure->lp;
		}
		if (isset($this->xmlStructure->ktxtppflicht))
		{
			$this->ktxtppflicht = (String) $this->xmlStructure->ktxtppflicht;
		}

		// Verantwortliche
		if (isset($this->xmlStructure->verantwortliche))
		{
			if (isset($this->xmlStructure->verantwortliche->personinfo))
			{
				$this->verantworlicher_vorname = $this->xmlStructure->verantwortliche->personinfo->vorname;
				$this->verantworlicher_nachname = $this->xmlStructure->verantwortliche->personinfo->nachname;
				$this->verantworlicher_ldap = $this->xmlStructure->verantwortliche->hgnr;
			}
		}

		// Dozenten
		$this->dozent = array();
		if (isset($this->xmlStructure->dozent))
		{
			$i = 0;
			foreach ($this->xmlStructure->dozent as $dozent)
			{

				$dozentInfo = array();
				$dozentString = isset($dozent->hgnr) ? (String) $dozent->hgnr : (String) $dozent->redmokid;
				$arrPersonInfo = array();
				$arrPersonInfo['id'] = $dozentString;
				$arrPersonInfo['name'] = (String) $dozent->personinfo->{'personal.nachname'};
				$arrPersonInfo['vorname'] = (String) $dozent->personinfo->{'personal.vorname'};
				array_push($this->dozent, $arrPersonInfo);
				$i++;
			}
		}

		$this->xmlStructure = "";
	}

	/**
	 * Method to parse xml for details
	 *
	 * @return void
	 */
	private function parseXmlForDetails()
	{
		if (isset($this->xmlStructure->modul))
		{
			$this->xmlStructure = $this->xmlStructure->modul;
		}

		if (isset($this->xmlStructure->modulid))
		{
			$this->modulid = (String) $this->xmlStructure->modulid;
		}
		if (isset($this->xmlStructure->nrhis))
		{
			$this->nrHis = $this->xmlStructure->nrhis;
		}
		if (isset($this->xmlStructure->modulecode))
		{
			$this->nrMni = $this->xmlStructure->modulecode;
		}
		if (isset($this->xmlStructure->kuerzel))
		{
			// De en
			$this->kuerzel = $this->xmlStructure->kuerzel;
		}
		if (isset($this->xmlStructure->kurzname))
		{
			// De en
			$this->kurzname_de = $this->xmlStructure->kurzname;
		}
		if (isset($this->xmlStructure->kurznameen))
		{
			// De en
			$this->kurzname_en = $this->xmlStructure->kurznameen;
		}
		if (isset($this->xmlStructure->titelde))
		{
			$this->modultitelDe = $this->xmlStructure->titelde;
		}
		if (isset($this->xmlStructure->titelen))
		{
			$this->modultitelEn = $this->xmlStructure->titelen;
		}
		if (isset($this->xmlStructure->sprache))
		{
			$this->sprache = $this->xmlStructure->sprache;
		}
		if (isset($this->xmlStructure->lp))
		{
			$this->lp = $this->xmlStructure->lp;
		}
		if (isset($this->xmlStructure->sws))
		{
			$this->sws = $this->xmlStructure->sws;
		}

		for ($i = 0; $i <= count($this->xmlStructure->beschreibungen); $i++)
		{
			if(!isset($this->xmlStructure->beschreibungen[$i]))
			{
				continue;
			}
			
			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Voraussetzungen" && $this->xmlStructure->beschreibungen[$i]->sprache == "de")
			{
				$this->zwvoraussetzungen_de = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Voraussetzungen" && $this->xmlStructure->beschreibungen[$i]->sprache == "en")
			{
				$this->zwvoraussetzungen_en = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Kurzbeschreibung" && $this->xmlStructure->beschreibungen[$i]->sprache == "de")
			{
				$this->kurzbeschreibung_de = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Kurzbeschreibung" && $this->xmlStructure->beschreibungen[$i]->sprache == "en")
			{
				$this->kurzbeschreibung_en = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Qualifikations und Lernziele"
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "de")
			{
				$this->lernziel_de = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Qualifikations und Lernziele"
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "en")
			{
				$this->lernziel_en = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Inhalt"
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "de")
			{
				$this->lerninhalt_de = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Inhalt"
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "en")
			{
				$this->lerninhalt_en = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Creditpoints/Arbeitsaufwand"
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "de")
			{
				$this->aufwand_de = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Creditpoints/Arbeitsaufwand"
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "en")
			{
				$this->aufwand_en = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Lehrformen"
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "de")
			{
				$this->lernform_de = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Lehrformen"
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "en")
			{
				$this->lernform_en = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if (strcmp($this->xmlStructure->beschreibungen[$i]->kategorie, "Prüfungsvorleistungen") == 0
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "de")
			{
				$this->vorleistung_de = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Prüfungsvorleistungen"
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "en")
			{
				$this->vorleistung_en = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Voraussetzungen für die Vergabe von Creditpoints"
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "de")
			{
				$this->leistungsnachweis_de = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Voraussetzungen für die Vergabe von Creditpoints"
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "en")
			{
				$this->leistungsnachweis_en = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Literatur"
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "de")
			{
				$this->litverz_de = $this->xmlStructure->beschreibungen[$i]->txt;
			}

			if ($this->xmlStructure->beschreibungen[$i]->kategorie == "Literatur"
			 && $this->xmlStructure->beschreibungen[$i]->sprache == "en")
			{
				$this->litverz_en = $this->xmlStructure->beschreibungen[$i]->txt;
			}
		}

		if (isset($this->xmlStructure->turnus))
		{
			$this->turnus = $this->xmlStructure->turnus;
		}

		if (isset($this->xmlStructure->dauer))
		{
			$this->dauer = $this->xmlStructure->dauer;
		}

		// Verantwortliche
		if (isset($this->xmlStructure->verantwortliche))
		{
			if (isset($this->xmlStructure->verantwortliche->personinfo))
			{
				$this->verantworlicher_vorname = $this->xmlStructure->verantwortliche->personinfo->vorname;
				$this->verantworlicher_nachname = $this->xmlStructure->verantwortliche->personinfo->nachname;
				$this->verantworlicher_ldap = $this->xmlStructure->verantwortliche->hgnr;
			}
		}

		// Dozenten
		$this->dozent = array();
		if (isset($this->xmlStructure->dozent))
		{
			$i = 0;
			foreach ($this->xmlStructure->dozent as $dozent)
			{
				$dozentInfo = array();
				$dozentString = isset($dozent->hgnr) ? (String) $dozent->hgnr : (String) $dozent->redmokid;
				$arrPersonInfo = array();
				$arrPersonInfo['id'] = $dozentString;
				$arrPersonInfo['name'] = (String) $dozent->personinfo->{'personal.nachname'};
				$arrPersonInfo['vorname'] = (String) $dozent->personinfo->{'personal.vorname'};
				array_push($this->dozent, $arrPersonInfo);
				$i++;
			}
		}
	}

	// --- Getter-Start ---

	/**
	 * Method to get the HIS number
	 *
	 * @return String
	 */
	public function getNrHis()
	{
		return $this->nrHis;
	}

	/**
	 * Method to get the MNI number
	 *
	 * @return String
	 */
	public function getNrMni()
	{
		return $this->nrMni;
	}

	/**
	 * Method to get the abbreviation
	 *
	 * @return String
	 */
	public function getKuerzel()
	{
		if ($this->language == "de")
		{
			return $this->kurzel;
		}
		else
		{
			return $this->kuerzel;
		}
	}

	/**
	 * Method to get the short name
	 *
	 * @return String
	 */
	public function getKurzname()
	{
		if ($this->language == "de")
		{
			return $this->kurzname_de;
		}
		else
		{
			return $this->kurzname_en;
		}
	}

	/**
	 * Method to get the short name (german)
	 *
	 * @return String
	 */
	public function getKurznameDe()
	{
		return $this->kurzname_de;
	}

	/**
	 * Method to get the short name (english)
	 *
	 * @return String
	 */
	public function getKurznameEn()
	{
		return $this->kurzname_en;
	}

	/**
	 * Method to get the module title
	 *
	 * @return String
	 */
	public function getModultitel()
	{
		if ($this->language == "de")
		{
			return $this->modultitelDe;
		}
		else
		{
			return $this->modultitelEn;
		}
	}

	/**
	 * Method to get the module title (german)
	 *
	 * @return String
	 */
	public function getModultitelDe()
	{
		return $this->modultitelDe;
	}

	/**
	 * Method to get the module title (english)
	 *
	 * @return String
	 */
	public function getModultitelEn()
	{
		return $this->modultitelEn;
	}

	/**
	 * Method to get the major
	 *
	 * @return String
	 */
	public function getStudiengang()
	{
		return $this->studiengang;
	}

	/**
	 * Method to get the language
	 *
	 * @return String
	 */
	public function getSprache()
	{
		return $this->sprache;
	}

	/**
	 * Method to get the credit points
	 *
	 * @return String
	 */
	public function getCreditpoints()
	{
		return $this->lp;
	}

	/**
	 * Method to get the effort
	 *
	 * @return String
	 */
	public function getAufwand()
	{
		if ($this->language == "de")
		{
			return $this->aufwand_de;
		}
		else
		{
			return $this->aufwand_en;
		}
	}

	/**
	 * Method to get the rotation
	 *
	 * @return String
	 */
	public function getTurnus()
	{
		if ($this->language == "de")
		{
			return $this->turnus;
		}
		else
		{
			return $this->turnus;
		}
	}

	/**
	 * Method to get the duration
	 *
	 * @return String
	 */
	public function getDauer()
	{
		return $this->dauer;
	}

	/**
	 * Method to get the graduation
	 *
	 * @return String
	 */
	public function getAbschluss()
	{
		return $this->$abschl;
	}

	/**
	 * Method to get the responsibles firstname
	 *
	 * @return String
	 */
	public function getModulVerantwortlicherVorname()
	{
		return $this->verantworlicher_vorname;
	}

	/**
	 * Method to get the responsibles lastname
	 *
	 * @return String
	 */
	public function getModulVerantwortlicherNachname()
	{
		return $this->verantworlicher_nachname;
	}

	/**
	 * Method to get the responsibles ldap
	 *
	 * @return String
	 */
	public function getModulVerantwortlicherLdap()
	{
		return $this->verantworlicher_ldap;
	}

	/**
	 * Method to get the short description
	 *
	 * @return String
	 */
	public function getKurzbeschreibung()
	{
		if ($this->language == "de")
		{
			return $this->kurzbeschreibung_de;
		}
		else
		{
			return $this->kurzbeschreibung_en;
		}
	}

	/**
	 * Method to get the teaching type
	 *
	 * @return String
	 */
	public function getLernform()
	{
		if ($this->language == "de")
		{
			return $this->lernform_de;
		}
		else
		{
			return $this->lernform_en;
		}
	}

	/**
	 * Method to get the conditions
	 *
	 * @return String
	 */
	public function getVorraussetzung()
	{
		if ($this->language == "de")
		{
			return $this->zwvoraussetzungen_de;
		}
		else
		{
			return $this->zwvoraussetzungen_en;
		}
	}

	/**
	 * Method to get the learning objects
	 *
	 * @return String
	 */
	public function getLernziel()
	{
		if ($this->language == "de")
		{
			return $this->lernziel_de;
		}
		else
		{
			return $this->lernziel_en;
		}
	}

	/**
	 * Method to get the learning content
	 *
	 * @return String
	 */
	public function getLerninhalt()
	{
		if ($this->language == "de")
		{
			return $this->lerninhalt_de;
		}
		else
		{
			return $this->lerninhalt_en;
		}
	}

	/**
	 * Method to get the preliminary work
	 *
	 * @return String
	 */
	public function getVorleistung()
	{
		if ($this->language == "de")
		{
			return $this->vorleistung_de;
		}
		else
		{
			return $this->vorleistung_en;
		}
	}

	/**
	 * Method to get the grading
	 *
	 * @return String
	 */
	public function getLeistungsnachweis()
	{
		if ($this->language == "de")
		{
			return $this->leistungsnachweis_de;
		}
		else
		{
			return $this->leistungsnachweis_en;
		}
	}

	/**
	 * Method to get the bibliography
	 *
	 * @return String
	 */
	public function getLiteraturVerzeichnis()
	{
		if ($this->language == "de")
		{
			return $this->litverz_de;
		}
		else
		{
			return $this->litverz_en;
		}
	}

	/**
	 * Method to get the semester weeks hours
	 *
	 * @return String
	 */
	public function getSWS()
	{
		return $this->sws;
	}

	/**
	 * Method to get the modul id
	 *
	 * @return String
	 */
	public function getModulId()
	{
		return $this->modulid;
	}

	/**
	 * Method to get the teacher
	 *
	 * @return String
	 */
	public function getDozenten()
	{
		return $this->dozent;
	}

	// --- Getter-Ende ---
}
