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
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/prep_course.php';

/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewParticipant_Edit extends JViewLegacy
{
	public $lang;

	public $languageSwitches;

	public $item;

	public $form;

	public $course;

	public $dates;

	public $signedIn;
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

		$this->lang = THM_OrganizerHelperLanguage::getLanguage();

		$this->item     = $this->get('Item');
		$this->form     = $this->get('Form');
		$this->course   = THM_OrganizerHelperPrep_Course::getCourse();
		$this->dates    = THM_OrganizerHelperPrep_Course::getDates();
		$this->signedIn = THM_OrganizerHelperPrep_Course::getRegistrationState();

		$courseOpen = THM_OrganizerHelperPrep_Course::isRegistrationOpen();

		if (!empty($this->course) AND !$courseOpen)
		{
			JError::raiseError(401, $this->lang->_('COM_THM_ORGANIZER_PREP_COURSE_NOTIFICATION_DEADLINE_EXCEEDED'));
		}

		if (empty($this->item) OR empty(JFactory::getUser()->id))
		{
			JError::raiseError(401, $this->lang->_('COM_THM_ORGANIZER_MESSAGE_NO_ACCESS_VIEW'));
		}

		$params = array('view' => 'participant_edit',
			'id' => empty($this->course) ? 0 : $this->course["id"] );
		$this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);


		parent::display($tpl);
	}

	/**
	 * Adds resource files to the document
	 *
	 * @return  void
	 */
	private function modifyDocument()
	{
		JHtml::_('bootstrap.tooltip');
		JHtml::_('behavior.framework', true);

		JFactory::getDocument()->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/prep_course.css');
	}
}