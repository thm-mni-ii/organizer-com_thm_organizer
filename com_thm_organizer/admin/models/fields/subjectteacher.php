<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldSubjectTeachers
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class loads a list of teachers for selection
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldSubjectTeacher extends JFormField
{
	protected $type = 'subjectTeacher';

	/**
	 * Returns a select box where stored teachers can be associated with a subject
	 *
	 * @return  string  the HTML output
	 */
	public function getInput()
	{
		$fieldName      = $this->getAttribute('name');
		$subjectID      = JFactory::getApplication()->input->getInt('id', 0);
		$responsibility = $this->getAttribute('responsibility');

		$dbo           = JFactory::getDbo();
		$selectedQuery = $dbo->getQuery(true);
		$selectedQuery->select('teacherID');
		$selectedQuery->from('#__thm_organizer_subject_teachers');
		$selectedQuery->where("subjectID = '$subjectID' AND teacherResp = '$responsibility'");
		$dbo->setQuery($selectedQuery);

		try
		{
			$selected = $dbo->loadColumn();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return $this->getDefault();
		}

		$teachersQuery = $dbo->getQuery(true);
		$teachersQuery->select("id AS value, surname, forename");
		$teachersQuery->from('#__thm_organizer_teachers');
		$teachersQuery->order('surname, forename');
		$dbo->setQuery($teachersQuery);

		try
		{
			$teachers = $dbo->loadAssocList();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return $this->getDefault();
		}

		foreach ($teachers as $key => $teacher)
		{
			$teachers[$key]['text'] = empty($teacher['forename']) ? $teacher['surname'] : "{$teacher['surname']}, {$teacher['forename']}";
		}

		$attributes       = ['multiple' => 'multiple', 'class' => 'inputbox', 'size' => '10'];
		$selectedTeachers = empty($selected) ? [] : $selected;

		return JHtml::_("select.genericlist", $teachers, "jform[$fieldName][]", $attributes, "value", "text", $selectedTeachers);
	}

	/**
	 * Creates a default input in the event of an exception
	 *
	 * @return  string  a default teacher selection field without any teachers
	 */
	private function getDefault()
	{
		$teachers   = [];
		$teachers[] = ['value' => '-1', 'name' => JText::_('JNONE')];
		$fieldName  = $this->getAttribute('name');
		$attributes = ['multiple' => 'multiple', 'class' => 'inputbox', 'size' => '1'];

		return JHtml::_("select.genericlist", $teachers, "jform[$fieldName][]", $attributes, "value", "text");
	}
}
