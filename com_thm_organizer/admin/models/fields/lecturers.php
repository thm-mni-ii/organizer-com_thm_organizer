<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldLecturers
 * @description JFormFieldLecturers component admin field
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

/**
 * Class JFormFieldLecturers for component com_thm_organizer
 * Class provides methods to create a form field that contains the lecturers
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldLecturers extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 */
	protected $type = 'Lecturers';

	/**
	 * Returns a select box which contains the lecturers
	 *
	 * @return Select box
	 */
	public function getInput()
	{
		$dbo = JFactory::getDBO();

		$query = $dbo->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_lecturers');
		$query->order('surname');
		$dbo->setQuery($query);
		$semesters = $dbo->loadObjectList();

		return JHTML::_(
						'select.genericlist',
						$semesters,
						'lecturers[]',
						'class="inputbox" size="10" multiple="multiple"',
						'id',
						'surname',
						self::getSelectedLecturers(JRequest::getVar('id'))
		);
	}

	/**
	 * Returns the selected lecturers of the given asset
	 *
	 * @return  String
	 */
	private function getSelectedLecturers()
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		$query->select("lecturer_id");
		$query->from('#__thm_organizer_lecturers_assets as lecturer_assets');
		$query->where("lecturer_assets.modul_id = '" . JRequest::getVar('id') . "'");
		$query->where('lecturer_assets.lecturer_type = 2');
		$dbo->setQuery((string) $query);
		$lecturerIDs = $dbo->loadResultArray();

		return empty($lecturerIDs)? array () : $lecturerIDs;
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
