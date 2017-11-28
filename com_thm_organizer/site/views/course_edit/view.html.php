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
require_once JPATH_ROOT . '/media/com_thm_organizer/views/edit.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/course.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/templates/edit_basic.php';

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

	/**
	 * Method to get display
	 *
	 * @param Object $tpl template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->modifyDocument();

		$this->item = $this->get('Item');
		$this->form = $this->get('Form');

		$this->lessonID = JFactory::getApplication()->input->getInt('lessonID', 0);

		$this->lang = THM_OrganizerHelperLanguage::getLanguage();

		$courseAuth = THM_OrganizerHelperCourse::teachesCourse($this->form->getValue("id"));
		$authorized = (JFactory::getUser()->authorise('core.admin') OR $courseAuth);

		if (!$authorized)
		{
			JError::raiseError(401, $this->lang->_('COM_THM_ORGANIZER_MESSAGE_NO_ACCESS_VIEW'));

			return;
		}

		$params                 = [
			'view'     => 'course_edit',
			'id'       => empty($this->form->getValue("id")) ? 0 : $this->form->getValue("id"),
			'lessonID' => $this->lessonID
		];
		$this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);

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

		JFactory::getDocument()->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/prep_course.css');
	}
}
