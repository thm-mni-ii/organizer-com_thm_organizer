<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		curriculum view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
jimport('joomla.application.component.view');
jimport('joomla.error.profiler');

/**
 * Class loasd curriculum information into the view context
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
            JHtml::_('behavior.tooltip');
            jimport('extjs4.extjs4');

            $document = JFactory::getDocument();
            $document->addStyleSheet($this->baseurl . '/components/com_thm_organizer/views/curriculum/tmpl/curriculum-minify.css');
            $document->addScript($this->baseurl . '/components/com_thm_organizer/views/curriculum/tmpl/app.js');

            // Get the parameters of the current view
            $this->params = JFactory::getApplication()->getMenu()->getActive()->params;
            $this->languageTag = JRequest::getVar('languageTag', $this->params->get('language'));
            $this->langLink = ($this->languageTag == 'de') ? 'en' : 'de';
            $this->langUrl = self::languageSwitcher($this->langLink);
            $this->pagetitle = $this->params->get('page_title');

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
				'languageTag' => $langLink
		);
		$params = array_merge($URI->getQuery(true), $params);
		$query = $URI->buildQuery($params);
		$URI->setQuery($query);

		return $URI->toString();
	}
}
