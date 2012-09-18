<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		JFormFieldLecturers
 * @description JFormFieldLecturers component admin field
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Class JFormFieldLecturers for component com_thm_organizer
 *
 * Class provides methods to create a form field that contains the lecturers
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class JFormFieldLecturers extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 * @since  1.0
	 */
	protected $type = 'Lecturers';

	/**
	 * Returns a select box which contains the lecturers
	 *
	 * @return Select box
	 */
	public function getInput()
	{
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_lecturers');
		$query->order('surname');
		$db->setQuery($query);
		$semesters = $db->loadObjectList();

		// Get the id of the current item
		$pk = JRequest::getVar('id');

		return JHTML::_('select.genericlist', $semesters, 'lecturers[]', 'class="inputbox" size="10" multiple="multiple"', 'id',
				'surname', self::getSelectedLecturers($pk)
		);
	}

	/**
	 * Returns the selected lecturers of the given asset
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return  String
	 */
	private function getSelectedLecturers($id)
	{
		$db = JFactory::getDBO();
		$assetId = JRequest::getVar('id');
		$query = $db->getQuery(true);

		$query->select("*");
		$query->from('#__thm_organizer_lecturers_assets as lecturer_assets');
		$query->where("lecturer_assets.modul_id = $assetId");
		$query->where("lecturer_assets.lecturer_type = 2");
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$selectedLecturers = array();

		if (isset($rows))
		{
			// Iterate over each found lecturer
			foreach ($rows as $row)
			{
				array_push($selectedLecturers, $row->lecturer_id);
			}
		}

		return $selectedLecturers;
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
		$class = !empty($this->description) ? 'hasTip' : '';
		$class = $this->required == true ? $class . ' required' : $class;

		// Add the opening label tag and main attributes attributes.
		$label .= '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $class . '"';

		// If a description is specified, use it to build a tooltip.
		if (!empty($this->description))
		{
			$label .= ' title="' . htmlspecialchars(
					trim(
							JText::_($text), ':') . '::' .
					JText::_($this->description), ENT_COMPAT, 'UTF-8') . '"';
		}

		// Add the label text and closing tag.
		$label .= '>' . $replace . JText::_($text) . '</label>';
		return $label;
	}
}
