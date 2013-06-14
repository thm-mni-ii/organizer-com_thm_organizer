<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		THM_CurriculumViewCurriculum
 * @description THM_CurriculumViewCurriculum component site view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
jimport('joomla.application.component.view');
jimport('joomla.error.profiler');

/**
 * Class THM_CurriculumViewCurriculum for component com_thm_organizer
 * Class provides methods to display the curriculum view
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewCurriculum extends JView
{
	/**
	 * Method to get display
	 *
	 * @param   Object  $tpl  template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{

		$app = JFactory::getApplication();

		// Get the parameters of the current view
		$this->params = JFactory::getApplication()->getMenu()->getActive()->params;

		$this->lang = JRequest::getVar('lang');
		$this->langLink = ($this->lang == 'de') ? 'en' : 'de';
		$this->langUrl = self::languageSwitcher($this->langLink);
		$this->pagetitle = $this->params->get('page_title');
		$this->doc = JFactory::getDocument();

		parent::display($tpl);
	}

	/**
	 * Method to switch the language
	 *
	 * @param   String  $langLink  language link
	 *
	 * @return  String
	 */
	public function languageSwitcher($langLink)
	{
		$itemid = JRequest::getVar('Itemid');
		$group = JRequest::getVar('view');
		$URI = JURI::getInstance('index.php');
		$params = array('option' => 'com_thm_organizer',
				'view' => $group,
				'Itemid' => $itemid,
				'lang' => $langLink
		);
		$params = array_merge($URI->getQuery(true), $params);
		$query = $URI->buildQuery($params);
		$URI->setQuery($query);

		return $URI->toString();
	}
}
