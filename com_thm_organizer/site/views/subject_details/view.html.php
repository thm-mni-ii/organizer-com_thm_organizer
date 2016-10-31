<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewSubject_Details
 * @author      Wolf Rost,  <Wolf.Rost@mni.thm.de>
 * @author      James Antrim,  <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class loads information about a subject into the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSubject_Details extends JViewLegacy
{
	public $languageSwitches = array();

	public $lang;

	public $disclaimer;

	public $disclaimerData;

	/**
	 * Method to get display
	 *
	 * @param Object $tpl template  (default: null)
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{
		$this->modifyDocument();
		$this->lang = THM_OrganizerHelperLanguage::getLanguage();
		$this->item = $this->get('Item');

		if (!empty($this->item->id))
		{
			$params                 = array('view' => 'subject_details', 'id' => $this->item->id);
			$this->languageSwitches = THM_OrganizerHelperLanguage::getLanguageSwitches($params);
		}

		$this->disclaimer     = new JLayoutFile('disclaimer', $basePath = JPATH_ROOT . '/media/com_thm_organizer/layouts');
		$this->disclaimerData = array('language' => $this->lang);

		parent::display($tpl);
	}

	/**
	 * Determines whether or not the attribute should be displayed based on its value
	 *
	 * @param mixed $value the attribute's value
	 *
	 * @return bool true if the attribute should be displayed, otherwise false
	 */
	public function displayStarAttribute($value)
	{
		if ($value === null)
		{
			return false;
		}

		if (is_numeric($value))
		{
			$value = (int) $value;
			$allowedValues = array(0, 1, 2, 3);
			if (in_array($value, $allowedValues))
			{
				return true;
			}
			return false;
		}


		if (is_string($value))
		{
			if ($value === '')
			{
				return false;
			}

			$allowedValues = array('0', '1', '2', '3');

			if (in_array($value, $allowedValues))
			{
				return true;
			}

			return false;
		}

		return false;
	}

	/**
	 * Modifies document variables and adds links to external files
	 *
	 * @return  void
	 */
	private function modifyDocument()
	{
		JHtml::_('bootstrap.tooltip');
		JHtml::_('behavior.framework', true);

		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::root() . '/media/com_thm_organizer/css/subject_details.css');
	}

	/**
	 * Creates a list of depencencies dependent on the type (pre|post)
	 *
	 * @param string $type the type of dependency
	 *
	 * @return string the HTML for the depencency output
	 */
	public function getDependencies($type)
	{
		$dependencies = array();
		switch ($type)
		{
			case 'pre':

				if (empty($this->item->preSubjects))
				{
					return '';
				}

				$dependencies = $this->item->preSubjects;

				break;

			case 'post':

				if (empty($this->item->postSubjects))
				{
					return '';
				}

				$dependencies = $this->item->postSubjects;

				break;

		}

		if (empty($dependencies))
		{
			return '';
		}

		$menuID  = JFactory::getApplication()->input->getInt('Itemid', 0);
		$langTag = THM_OrganizerHelperLanguage::getShortTag();
		$link  = "index.php?option=com_thm_organizer&view=subject_details&languageTag={$langTag}&Itemid={$menuID}&id=";

		$html = '<ul>';
		foreach ($dependencies as $programID => $programData)
		{
			$html .= "<li>{$programData['name']}<ul>";
			foreach ($programData['subjects'] AS $subjectID => $subjectName)
			{
				$subjectLink = JHtml::_('link', $link . $subjectID, $subjectName);
				$html .= "<li>$subjectLink</li>";
			}
			$html .= "</ul></li>";
		}
		$html .= "</ul>";

		return $html;
	}

	/**
	 * Creates teacher output
	 *
	 * @param array $teacher the teacher item
	 *
	 * @return  void  creates HTML output
	 */
	public function getTeacherOutput($teacher)
	{
		if (!empty($teacher['link']))
		{
			echo '<a href="' . $teacher['link'] . '">' . $teacher['name'] . '</a>';
		}
		else
		{
			echo $teacher['name'];
		}
	}
}
