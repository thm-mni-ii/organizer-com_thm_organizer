<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		THM_CurriculumViewdetails
 * @description THM_CurriculumViewdetails component site view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @author	    Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . DS . 'helper/lsfapi.php';
require_once JPATH_COMPONENT_SITE . DS . 'models/groups.php';

/**
 * Class THM_CurriculumViewdetails for component com_thm_organizer
 *
 * Class provides methods to display the details view
 *
 * @category	Joomla.Component.Site
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerViewdetails extends JView
{
	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (default: null)
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		$document = & JFactory::getDocument();
		$modelGroups = new THM_OrganizerModelGroups;
		$model = & $this->getModel();
		$this->session = & JFactory::getSession();
		$verantw = "";
		$verantwLabel = "";

		$linkVerantwortlicher = null;
		$dozentenLinks = array();

		$document->addStyleSheet($this->baseurl . '/components/com_thm_organizer/css/curriculum.css');

		if (JRequest::getString('id'))
		{
			$this->modul = $model->getModuleByID(JRequest::getString('id'));
		}
		elseif (JRequest::getString('nrmni'))
		{
			$this->modul = $model->getModuleByNrMni(JRequest::getString('nrmni'));
		}
		else
		{

		}
		array_push($dozentenLinks, $verantw . $verantwLabel);
		$lecturer = $modelGroups->buildLecturerLink(JRequest::getString('id'), JRequest::getVar('lang'));

		// Comma seperated lecturer data */
		$this->dozenten = implode(', ', $lecturer);
		$this->mappingTurnus_de = array(1 => 'nur Wintersemester', 2 => 'nur Sommersemester', 3 => 'jedes Semester',
				4 => 'bei Bedarf', 5 => 'j&auml;hrlich');
		$this->mappingTurnus_en = array(1 => 'Winter', 2 => 'Summer', 3 => 'Every Semester', 4 => 'If necessary', 5 => 'Annual');
		$this->moduleNavigation = json_decode($this->session->get('navi_json'));
		$this->lang = JRequest::getVar('lang');
		$this->langLink = ($this->lang == 'de') ? 'en' : 'de';
		$this->langUrl = self::languageSwitcher($this->langLink);
		
		if(isset($this->modul))
		{
			$this->modultitel = $this->modul->getModultitel();
			$this->modulNrMni = $this->modul->getNrMni();
			$this->modulKurzname = $this->modul->getKurzname();
			$this->modulKurzbeschreibung = $this->modul->getKurzbeschreibung();
			$this->modulLernziel = $this->modul->getLernziel();
			$this->modulLerninhalt = $this->modul->getLerninhalt();
			$this->modulDauer = $this->modul->getDauer();
			$this->modulSprache = $this->modul->getSprache();
			$this->modulAufwand = $this->modul->getAufwand();
			$this->modulLernform = $this->modul->getLernform();
			$this->modulVorleistung = $this->modul->getVorleistung();
			$this->modulLeistungsnachweis = $this->modul->getLeistungsnachweis();
			$this->modulTurnus = $this->modul->getTurnus();
			$this->modulLiteraturVerzeichnis = $this->modul->getLiteraturVerzeichnis();
			$this->modulVorraussetzung = $this->modul->getVorraussetzung();
		}
		else
		{
			$this->modultitel = "";
			$this->modulNrMni = "";
			$this->modulKurzname = "";
			$this->modulKurzbeschreibung = "";
			$this->modulLernziel = "";
			$this->modulLerninhalt = "";
			$this->modulDauer = "";
			$this->modulSprache = "";
			$this->modulAufwand = "";
			$this->modulLernform = "";
			$this->modulVorleistung = "";
			$this->modulLeistungsnachweis = "";
			$this->modulTurnus = 0;
			$this->modulLiteraturVerzeichnis = "";
			$this->modulVorraussetzung = "";
		}
		
		

		parent::display($tpl);
	}

	/**
	 * Method to build the url for the language switcher butto
	 *
	 * @param   String  $langLink  Language link
	 *
	 * @return  String
	 */
	private function languageSwitcher($langLink)
	{
		$session = & JFactory::getSession();

		$uri = JUri::base();
		$itemid = JRequest::getVar('Itemid');
		$group = JRequest::getVar('view');
		$u = & JURI::getInstance('index.php');
		$tmpl = null;
		$mysched = null;

		if (JRequest::getVar('mysched'))
		{
			$id = JRequest::getVar('nrmni');
			$tmpl = "component";
			$mysched = "true";
		}
		else
		{
			$id = JRequest::getVar('id');
		}

		$params = array('option' => 'com_thm_organizer',
				'view' => $group,
				'Itemid' => $itemid,
				'id' => $id,
				'lang' => $langLink,
				'tmpl' => $tmpl,
				'mysched' => $mysched
		);

		$params = array_merge($u->getQuery(true), $params);
		$query = $u->buildQuery($params);
		$u->setQuery($query);

		return $u->toString();
	}

	/**
	 * Method to return the first and lastname of a given userid
	 *
	 * @param   String  $id  Id  (default: null)
	 *
	 * @return  mixed
	 */
	private function getLecturer($id = null)
	{
		if (isset($id))
		{
			$this->db = &JFactory::getDBO();

			// Build the sql statement
			$query = $this->db->getQuery(true);
			$query->select("*");
			$query->from('#__thm_organizer_lecturers as lecturers');
			$query->where("userid = '$id' ");
			$this->db->setQuery($query);
			$rows = $this->db->loadObjectList();

			if (isset($rows))
			{
				return $rows[0]->academic_title . " " . $rows[0]->forename . " " . $rows[0]->surname;
			}
		}
	}
}
