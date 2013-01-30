<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name		JFormFieldSemesterorder
 * @description JFormFieldSemesterorder component admin field
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Class JFormFieldSemesterorder for component com_thm_organizer
 *
 * Class provides methods to create a form field
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class JFormFieldSemesterorder extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 * @since  1.0
	 */
	protected $type = 'Semesterorder';

	/**
	 * Returns a selection box with selectable semesters
	 *
	 * @return Select box
	 */
	public function getInput()
	{
		$db = JFactory::getDBO();
		$scriptDir = str_replace(JPATH_SITE . DS, '', "administrator/components/com_thm_organizer/models/fields/");
		JHTML::script('semesterorder.js', $scriptDir, false);

		// Get the id of the chosen major
		$pk = JRequest::getVar('id');

		// Build the query
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_semesters');
		$query->order('name ASC');

		$db->setQuery($query);
		$semesters = $db->loadObjectList();

		$html = JHTML::_('select.genericlist', $semesters, 'semesters[]', 'class="inputbox" size="10" multiple="multiple"',
				'id', 'name', self::getSelectedSemesters($pk)
		);
		$html .= '<a onclick="roleup()" id="sortup"><img src="../administrator/components/com_thm_groups/img/uparrow.png" ' .
				'title="Rolle eine Position h&ouml;her" /></a>';
		$html .= '<a onclick="roledown()" id="sortdown"><img src="../administrator/components/com_thm_groups/img/downarrow.png" ' .
				'title="Rolle eine Position niedriger" /></a>';

		return $html;
	}

	/**
	 * Determines which semesters belong to a given major
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return  String
	 */
	private function getSelectedSemesters($id)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Build the query
		$query->select("*");
		$query->from('#__thm_organizer_semesters');
		$query->join('inner', '#__thm_organizer_semesters_majors ' .
				'ON #__thm_organizer_semesters.id = #__thm_organizer_semesters_majors.semester_id');
		$query->where("#__thm_organizer_semesters_majors.major_id = $id");
		$query->order('name ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$selectedSemesters = array();

		if (isset($rows))
		{
			foreach ($rows as $row)
			{
				array_push($selectedSemesters, $row->semester_id);
			}
		}

		return $selectedSemesters;
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
