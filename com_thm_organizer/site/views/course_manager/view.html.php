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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/campuses.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/course.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class which loads data into the view output context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewCourse_Manager extends JViewLegacy
{
	public $course;

	public $courseAuth = false;

	public $capacityText;

	public $dateText = "";

	public $form;

	public $lang;

	public $languageSwitches;

	public $menu;

	public $items;

	/**
	 * Method to get display
	 *
	 * @param Object $tpl template  (default: null)
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->lang = THM_OrganizerHelperLanguage::getLanguage();
		$user       = JFactory::getUser();
		$lessonID   = JFactory::getApplication()->input->getInt("lessonID", 0);

		if (empty($lessonID) OR !THM_OrganizerHelperCourse::isCourseAdmin($lessonID))
		{
			JError::raiseError(401, $this->lang->_('COM_THM_ORGANIZER_MESSAGE_NO_ACCESS_VIEW'));
		}

		$this->course       = THM_OrganizerHelperCourse::getCourse();
		$this->participants = $this->get('Items');
		$this->circularForm = $this->get('Form');
		$this->dateText     = THM_OrganizerHelperCourse::getDateDisplay();
		$participantCount = count(THM_OrganizerHelperCourse::getParticipants($this->course["id"]));
		$allowedParticipants = (!empty($this->course["lessonP"]) ? $this->course["lessonP"] : $this->course["subjectP"]);
		$this->capacityText = "$participantCount/$allowedParticipants";

		$this->course['campus'] = THM_OrganizerHelperCourse::getCampus($this->course);
		THM_OrganizerHelperComponent::addMenuParameters($this);

		$params                 = ['view' => 'course_manager', 'id' => empty($this->course) ? 0 : $this->course["id"]];
		$this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);
		$this->modifyDocument();

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

		JFactory::getDocument()->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/course_manager.css');
	}

	/**
	 * Creates a drop down list of links for changing the assigned campus
	 *
	 * @return void renders the print selection
	 *
	 * @since version
	 */
	public function renderCampusSelect()
	{
		$defaultCampusID = $this->course['campus']['id'];

		$js = "if(this.value != ''){jQuery('#task').val('course.saveCampus');this.form.submit();}";
		echo '<select id="campusID" name="campusID" onchange="' . $js . '">';

		$selected = empty($defaultCampusID) ? 'selected="selected"' : '';
		echo '<option value="" ' . $selected . '>' . $this->lang->_('COM_THM_ORGANIZER_CAMPUS_SELECT_PLACEHOLDER') . '</option>';

		$campuses = THM_OrganizerHelperCampuses::getOptions();

		if (!empty($campuses))
		{
			foreach ($campuses AS $campusID => $name)
			{
				$selected = $campusID == $defaultCampusID ? 'selected="selected"' : '';
				echo '<option value="' . $campusID . '" ' . $selected . '>' . $name . '</option>';
			}
		}

		echo '</select>';
	}

	/**
	 * Creates a drop down list of links listing print options
	 *
	 * @return void renders the print selection
	 *
	 * @since version
	 */
	public function renderPrintSelect()
	{
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$baseURL  = "index.php?option=com_thm_organizer&lessonID={$this->course['id']}&languageTag=$shortTag";
		$baseURL  .= "&view=course_list&format=pdf&type=";

		$participantListRoute = JRoute::_($baseURL . 0, false);
		$departmentListRoute  = JRoute::_($baseURL . 1, false);
		$badgesRoute          = JRoute::_($baseURL . 2, false);

		?>
		<a class="dropdown-toggle print btn" data-toggle="dropdown" href="#">
			<span class="icon-print"></span>
			<?php echo $this->lang->_('COM_THM_ORGANIZER_PRINT_OPTIONS'); ?>
			<span class="icon-arrow-down-3"></span>
		</a>
		<ul id="print" class="dropdown-menu">
			<li>
				<a href="<?php echo $participantListRoute; ?>" target="_blank">
					<span class="icon-file-pdf"></span><?php echo JText::_('COM_THM_ORGANIZER_EXPORT_PARTICIPANTS'); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo $departmentListRoute; ?>" target="_blank">
					<span class="icon-file-pdf"></span><?php echo JText::_('COM_THM_ORGANIZER_EXPORT_DEPARTMENTS'); ?>
				</a>
			</li>
			<li>
				<a href="<?php echo $badgesRoute; ?>" target="_blank">
					<span class="icon-file-pdf"></span><?php echo JText::_('COM_THM_ORGANIZER_EXPORT_BADGES'); ?>
				</a>
			</li>
		</ul>
		<?php
	}
}