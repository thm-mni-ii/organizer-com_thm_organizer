<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/course.php';

/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewCourse_Edit extends JViewLegacy
{
	public $item;

	public $form;

	public $lessonID;

	public $lang;

	public $languageSwitches;

	public $menu;

	public $subjectID;

	/**
	 * Method to get display
	 *
	 * @param Object $tpl template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$input           = JFactory::getApplication()->input;
		$this->subjectID = $input->getInt('id', 0);

		if (empty($this->subjectID))
		{
			JError::raiseError(404, JText::_('COM_THM_ORGANIZER_MESSAGE_NOT_FOUND'));

			return;
		}

		$courseAuth = THM_OrganizerHelperCourse::teachesCourse($this->subjectID);
		$authorized = (JFactory::getUser()->authorise('core.admin') OR $courseAuth);

		if (!$authorized)
		{
			JError::raiseError(401, JText::_('COM_THM_ORGANIZER_MESSAGE_NO_ACCESS_VIEW'));

			return;
		}

		$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		$this->lessonID    = $input->getInt('lessonID', 0);
		$this->languageTag = THM_OrganizerHelperLanguage::getShortTag();


		$this->lang = THM_OrganizerHelperLanguage::getLanguage();

		THM_OrganizerHelperComponent::addMenuParameters($this);

		$params = ['view' => 'course_edit', 'id' => $this->subjectID, 'lessonID' => $this->lessonID];

		$this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);

		$this->modifyDocument();

		parent::display($tpl);
	}

	/**
	 * Adds resource files to the document
	 *
	 * @return  void
	 */
	protected function modifyDocument()
	{
		JHtml::_('bootstrap.tooltip');
		JHtml::_('behavior.framework', true);

		JFactory::getDocument()->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/course_edit.css');
	}
}
