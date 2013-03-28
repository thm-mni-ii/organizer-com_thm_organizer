<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		THM_OrganizerViewTeachers
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
jimport('joomla.application.component.view');
jimport('joomla.error.profiler');

/**
 * Class THM_OrganizerViewTeachers diplays modules grouped by responsible teachers.
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewTeachers extends JView
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
		JHTML::_('behavior.tooltip');
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		$model = $this->getModel();

		JHTML::script('joomla.javascript.js', 'includes/js/');
		$document->addStyleSheet($this->baseurl . '/components/com_thm_organizer/css/curriculum.css');

		// Set the default language to german
		$this->session = JFactory::getSession();

		if ($this->session->get('language') == null)
		{
			$this->session->set('language', 'de');
		}

		// Assign the data to the template
		$this->params = $menu->params;
		$this->pagetitle = $menu->params->get('pagetitle');
		$this->data = $model->getData();

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
