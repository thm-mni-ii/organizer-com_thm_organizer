<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		THM_CurriculumViewdetails
 * @description THM_CurriculumViewdetails component site view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT_SITE . DS . 'models/groups.php';

/**
 * Class THM_CurriculumViewdetails for component com_thm_organizer
 * Class provides methods to display the details view
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSubject_Details extends JView
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
		$document = JFactory::getDocument();
		$document->addStyleSheet($this->baseurl . '/components/com_thm_organizer/css/curriculum.css');

		$model = $this->getModel();
        $this->subject = $model->subject;
		$this->session = JFactory::getSession();
		$verantw = "";
		$verantwLabel = "";
		$dozentenLinks = array();


		// Comma seperated lecturer data */
		$this->moduleNavigation = json_decode($this->session->get('navi_json'));
		$this->lang = JRequest::getVar('lang');
		$this->otherLanguageTag = ($this->lang == 'de') ? 'en' : 'de';
		$this->langUrl = self::languageSwitcher($this->otherLanguageTag);
		
		
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
		$itemid = JRequest::getVar('Itemid');
		$group = JRequest::getVar('view');
		$URI = JURI::getInstance('index.php');
		$tmpl = null;
		$mysched = null;

		if (JRequest::getVar('mysched'))
		{
			$moduleID = JRequest::getVar('nrmni');
			$tmpl = "component";
			$mysched = "true";
		}
		else
		{
			$moduleID = JRequest::getVar('id');
		}

		$params = array('option' => 'com_thm_organizer',
				'view' => $group,
				'Itemid' => $itemid,
				'id' => $moduleID,
				'lang' => $langLink,
				'tmpl' => $tmpl,
				'mysched' => $mysched
		);

		$params = array_merge($URI->getQuery(true), $params);
		$query = $URI->buildQuery($params);
		$URI->setQuery($query);

		return $URI->toString();
	}

	/**
	 * Method to return the first and lastname of a given userid
	 *
	 * @param   String  $userID  Id  (default: null)
	 *
	 * @return  mixed
	 */
	private function getLecturer($userID = null)
	{
		if (isset($userID))
		{
			$this->db = JFactory::getDBO();

			// Build the sql statement
			$query = $this->db->getQuery(true);
			$query->select("*");
			$query->from('#__thm_organizer_lecturers as lecturers');
			$query->where("userid = '$userID' ");
			$this->db->setQuery($query);
			$rows = $this->db->loadObjectList();

			if (isset($rows))
			{
				return $rows[0]->academic_title . " " . $rows[0]->forename . " " . $rows[0]->surname;
			}
		}
	}
}
