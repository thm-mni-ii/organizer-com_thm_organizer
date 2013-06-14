<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerViewTeacher_Merge
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * Class loads persistent teacher information into display context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewTeacher_Merge extends JView
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

		$model = $this->getModel();

		$data = $model->teacherInformation;
		$this->generateFormFields($data);

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	/**
	 * Creates input tags for the merge form.
	 * 
	 * @param   array  &$teacherEntries  the data from the database
	 * 
	 * @return void
	 */
	private function generateFormFields(&$teacherEntries)
	{
		$this->ID = "<input type='hidden' name='id' value='{$teacherEntries[0]['id']}' />";

		$this->IDs = array();
		$surnameChecked = false;
		$this->surname = array();
		$forenameChecked = false;
		$this->forename = array();
		$titleChecked = false;
		$this->title = array();
		$usernameChecked = false;
		$this->username = array();
		$gpuntisIDChecked = false;
		$this->gpuntisID = array();
		$fieldIDChecked = false;
		$this->fieldID = array();

		foreach ($teacherEntries as $entry)
		{
			if ($entry['id'] != $teacherEntries[0]['id'])
			{
				$this->otherIDs[] = $entry['id'];
			}
			$this->setPropertyInput('surname', $entry['surname'], $surnameChecked);
			$this->setPropertyInput('forename', $entry['forename'], $forenameChecked);
			$this->setPropertyInput('title', $entry['title'], $titleChecked);
			$this->setPropertyInput('username', $entry['username'], $usernameChecked);
			$this->setPropertyInput('gpuntisID', $entry['gpuntisID'], $gpuntisIDChecked);
			if (!empty($entry['fieldID']) AND !array_key_exists($entry['fieldID'], $this->fieldID))
			{
				$this->fieldID[$entry['fieldID']] = "<label for='{$entry['fieldID']}'>{$entry['field']}</label>";
				$this->fieldID[$entry['fieldID']] .= "<input type='radio' name='fieldID' value='{$entry['fieldID']}' ";
				$this->fieldID[$entry['fieldID']] .= $fieldIDChecked?  ">" : "checked>";
				$fieldIDChecked = true;
			}
		}
		$this->otherIDs = "<input type='hidden' name='otherIDs' value='" . implode(',', $this->otherIDs) . "' />";
	}

	/**
	 * Creates a label and radio buttion input for entry values.
	 * 
	 * @param   string   $name      the name of the property
	 * @param   string   $value     the value to be sent on form submission
	 * @param   boolean  &$checked  if one of the preceding values is checked
	 * 
	 * @return  void
	 */
	private function setPropertyInput($name, $value, &$checked = false)
	{
		if ($value != '' AND !array_key_exists($value, $this->{$name}))
		{
			$this->{$name}[$value] = "<label for='$value'>$value</label>";
			$this->{$name}[$value] .= "<input type='radio' name='$name' value='$value' ";
			$this->{$name}[$value] .= $checked?  ">" : "checked>";
			$checked = true;
		}
	}

	/**
	 * Method to generate buttons for user interaction
	 *
	 * @return  void
	 */
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_THM_ORGANIZER_TRM_MERGE_TITLE'));
		JToolBarHelper::save('teacher.merge', 'COM_THM_ORGANIZER_MERGE');
		JToolBarHelper::cancel('teacher.cancel', 'JTOOLBAR_CANCEL');
	}
}
