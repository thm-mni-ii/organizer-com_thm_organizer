<?php

/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldSubjectMappings
 * @author      Florian Fenz, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
class JFormFieldSubjectMapping extends JFormField
{
	protected $type = 'subjectMapping';

	/**
	 * Returns a selectionbox where stored coursepool can be chosen as a parent node
	 *
	 * @return  string  the HTML output
	 */
	public function getInput()
	{
		$fieldName      = $this->getAttribute('name');
		$subjectID      = JFactory::getApplication()->input->getInt('id', 0);

		$dbo           = JFactory::getDbo();
		$selectedQuery = $dbo->getQuery(true);
		$selectedQuery->select('plan_subjectID');
		$selectedQuery->from('#__thm_organizer_subject_mappings');
		$selectedQuery->where("subjectID = '$subjectID'");
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

		$planSubjectQuery = $dbo->getQuery(true);
		$planSubjectQuery->select("id AS value, name");
		$planSubjectQuery->from('#__thm_organizer_plan_subjects');
		$planSubjectQuery->order('name');
		$dbo->setQuery($planSubjectQuery);

		try
		{
			$planSubjects = $dbo->loadAssocList();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return $this->getDefault();
		}

		foreach ($planSubjects as $key => $planSubject)
		{
			$planSubjects[$key]['text'] = $planSubject['name'];
		}

		$attributes       = array('multiple' => 'multiple', 'class' => 'inputbox', 'size' => '10');
		$selectedMappings = empty($selected) ? array() : $selected;

		return JHtml::_("select.genericlist", $planSubjects, "jform[$fieldName][]", $attributes, "value", "text", $selectedMappings);
	}

	/**
	 * Creates a default input in the event of an exception
	 *
	 * @return  string  a default teacher selection field without any teachers
	 */
	private function getDefault()
	{
		$planSubjects   = array();
		$planSubjects[] = array('value' => '-1', 'name' => JText::_('JNONE'));
		$fieldName  = $this->getAttribute('name');
		$attributes = array('multiple' => 'multiple', 'class' => 'inputbox', 'size' => '1');

		return JHtml::_("select.genericlist", $planSubjects, "jform[$fieldName][]", $attributes, "value", "text");
	}

}