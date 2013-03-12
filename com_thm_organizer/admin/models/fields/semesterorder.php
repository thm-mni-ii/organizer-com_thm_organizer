<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldSemesterorder
 * @description JFormFieldSemesterorder component admin field
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

/**
 * Class JFormFieldSemesterorder for component com_thm_organizer
 *
 * Class provides methods to create a form field
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldSemesterorder extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 */
	protected $type = 'Semesterorder';

	/**
	 * Returns a selection box with selectable semesters
	 *
	 * @return Select box
	 */
	public function getInput()
	{
		$scriptDir = str_replace(JPATH_SITE . DS, '', "administrator/components/com_thm_organizer/models/fields/");
		JHTML::script('semesterorder.js', $scriptDir, false);

		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		$query->select("*");
		$query->from('#__thm_organizer_semesters');
		$query->order('name ASC');
		$dbo->setQuery((string) $query);
		$semesters = $dbo->loadObjectList();

		$html = JHTML::_(
						 'select.genericlist',
						 $semesters,
						 'semesters[]',
						 'class="inputbox" size="10" multiple="multiple"',
						 'id',
						 'name',
						 self::getSelectedSemesters(JRequest::getVar('id'))
						);
		$html .= '<a onclick="roleup()" id="sortup"><img src="../administrator/components/com_thm_groups/img/uparrow.png" ';
		$html .= 'title="Rolle eine Position h&ouml;her" /></a>';
		$html .= '<a onclick="roledown()" id="sortdown"><img src="../administrator/components/com_thm_groups/img/downarrow.png" ';
		$html .= 'title="Rolle eine Position niedriger" /></a>';

		return $html;
	}

	/**
	 * Determines which semesters belong to a given major
	 *
	 * @param   Integer  $majorID  Id
	 *
	 * @return  String
	 */
	private function getSelectedSemesters($majorID)
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		$query->select("semester_id");
		$query->from('#__thm_organizer_semesters AS s');
		$query->innerJoin('#__thm_organizer_semesters_majors AS sm ON s.id = sm.semester_id');
		$query->where("#__thm_organizer_semesters_majors.major_id = '$majorID'");
		$query->order('name ASC');
		$dbo->setQuery((string) $query);
		$selectedSemesters = $dbo->loadResultArray();

		return empty($selectedSemesters)? array() : $selectedSemesters;
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
