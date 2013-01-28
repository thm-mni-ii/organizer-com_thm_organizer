<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		THM_CurriculumViewIndex
 * @description THM_CurriculumViewIndex component site view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

jimport('joomla.application.component.view');
jimport('joomla.error.profiler');

/**
 * Class THM_CurriculumViewIndex for component com_thm_organizer
 *
 * Class provides methods to display the index view
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerViewIndex extends JView
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
		$model = & $this->getModel();
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();

		JHTML::script('joomla.javascript.js', 'includes/js/');
		$document->addStyleSheet($this->baseurl . '/components/com_thm_organizer/css/curriculum.css');

		// Set the default language to german
		$this->session = & JFactory::getSession();

		if ($this->session->get('language') == null)
		{
			$this->session->set('language', 'de');
		}

		// Assign the data to the template
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = $menu->params;
		$this->lang = JRequest::getVar('lang');

		$this->langLink = ($this->lang == 'de') ? 'en' : 'de';
		$this->langUrl = self::languageSwitcher($this->langLink);

		parent::display($tpl);
	}

	/**
	 * Method to switch the language
	 *
	 * @param   String  $langLink  language link
	 *
	 * @return  String
	 */
	private function languageSwitcher($langLink)
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
