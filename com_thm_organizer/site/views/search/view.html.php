<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewSearch
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/planning_periods.php';

define('ALL', 0);
define('DEPARTMENTS', 1);
define('POOLS', 2);
define('PROGRAMS', 3);
define('ROOMS', 4);
define('SUBJECTS', 5);
define('TEACHERS', 6);

/**
 * Class loading persistent data into the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSearch extends JViewLegacy
{
	public $languageSwitches = [];

	public $languageTag;

	public $query;

	public $results;

	/**
	 * loads model data into view context
	 *
	 * @param string $tpl the name of the template to be used
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function display($tpl = null)
	{
		$this->lang             = THM_OrganizerHelperLanguage::getLanguage();
		$this->languageTag      = THM_OrganizerHelperLanguage::getShortTag();
		$switchParams           = ['view' => 'search', 'form' => true];
		$this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($switchParams);
		$this->query            = JFactory::getApplication()->input->getString('search', '');
		$this->results          = $this->getModel()->getResults();

		$this->modifyDocument();
		parent::display($tpl);
	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return  void
	 */
	private function modifyDocument()
	{
		JHtml::_('bootstrap.framework');
		JHtml::_('bootstrap.tooltip');
		JHtml::_('jquery.ui');

		$document = JFactory::getDocument();
		$document->setTitle($this->lang->_('COM_THM_ORGANIZER_SEARCH_VIEW_TITLE'));
		$document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/search.css');
	}
}
