<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldSubjectTeachers
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

/**
 * Class loads a list of teachers for selection
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldSubjectTeachers extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 */
	protected $type = 'subjectTeachers';

	/**
	 * Returns a selectionbox where stored coursepool can be chosen as a parent node
	 *
	 * @return Select box
	 */
	public function getInput()
	{
		$dbo = JFactory::getDBO();
		$subjectID = JRequest::getInt('id');
        
        $selectedQuery = $dbo->getQuery(true);
        $selectedQuery->select('teacherID')->from('#__thm_organizer_subject_teachers')->where("subjectID = '$subjectID' AND teacherResp = '2'");
        $dbo->setQuery((string) $selectedQuery);
        $selected = $dbo->loadResultArray();

        $teachersQuery = $dbo->getQuery(true);
        $teachersQuery->select("id AS value, surname, forename, username");
        $teachersQuery->from('#__thm_organizer_teachers');
        $teachersQuery->order('surname, forename');
        $dbo->setQuery((string) $teachersQuery);
        $teachers = $dbo->loadAssocList(); 
        foreach ($teachers as $key => $teacher)
        {
            $teachers[$key]['name'] = empty($teacher['forename'])? $teacher['surname'] : "{$teacher['surname']}, {$teacher['forename']}";
        }

        $attributes = array('multiple' => 'multiple');
        $selectedTeachers = empty($selected)? array() : $selected;
		return JHTML::_("select.genericlist", $teachers, "jform[teacherID][]", $attributes, "value", "name", $selectedTeachers);
	}

	/**
	 * Method to get the field label
	 *
	 * @return String The field label
	 */
	public function getLabel()
	{
		// Initialize variables.
		$label = '';
		$replace = '';

		// Get the label text from the XML element, defaulting to the element name.
		$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];

		// Build the class for the label.
		$class = '';
		$class .= !empty($this->description) ? 'hasTip' : '';
		$class .= $this->required == true ? ' required' : '';

		// Add the opening label tag and main attributes attributes.
		$label .= '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $class . '"';

		// If a description is specified, use it to build a tooltip.
		if (!empty($this->description))
		{
			$title = trim(JText::_($text), ':') . '::' . JText::_($this->description);
			$label .= ' title="' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '"';
		}

		// Add the label text and closing tag.
		$label .= '>' . $replace . JText::_($text) . '</label>';

		return $label;
	}
}
