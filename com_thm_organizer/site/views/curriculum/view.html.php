<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		THM_CurriculumViewCurriculum
 * @description THM_CurriculumViewCurriculum component site view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

jimport('joomla.application.component.view');
jimport('joomla.error.profiler');

/**
 * Class THM_CurriculumViewCurriculum for component com_thm_organizer
 *
 * Class provides methods to display the curriculum view
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v1.5.0
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
		$document = & JFactory::getDocument();
		$app = JFactory::getApplication();

		// Get the parameters of the current view
		$params = &$state->params;
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		$this->params = $menu->params;

		$this->lang = JRequest::getVar('lang');
		$this->langLink = ($this->lang == 'de') ? 'en' : 'de';
		$this->langUrl = self::languageSwitcher($this->langLink);
		$this->pagetitle = $menu->params->get('page_title');
		$this->doc = $document;

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
		$uri = JUri::base();
		$itemid = JRequest::getVar('Itemid');
		$group = JRequest::getVar('view');
		$u = & JURI::getInstance('index.php');
		$params = array('option' => 'com_thm_organizer',
				'view' => $group,
				'Itemid' => $itemid,
				'lang' => $langLink
		);
		$params = array_merge($u->getQuery(true), $params);
		$query = $u->buildQuery($params);
		$u->setQuery($query);

		return $u->toString();
	}
}
