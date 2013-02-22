<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldMapping
 * @description JFormFieldMapping component admin field
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

/**
 * Class JFormFieldMapping for component com_thm_organizer
 *
 * Class provides methods to create a form field
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class JFormFieldMapping extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 * @since  1.0
	 */
	protected $type = 'Mapping';

	/**
	 * Returns a multiple select which includes the related semesters of the current tree node
	 *
	 * @return Select box
	 */
	public function getInput()
	{
		$db = JFactory::getDBO();

		// Get the major id
		$id = $_SESSION['stud_id'];

		// Build the query
		$query = $db->getQuery(true);
		$query->select("sem_major.id AS id");
		$query->select("name");
		$query->from('#__thm_organizer_semesters_majors as sem_major');
		$query->join('inner', '#__thm_organizer_semesters as semesters ON sem_major.semester_id = semesters.id');
		$query->where("major_id = $id");
		$query->order('name ASC');
		$db->setQuery($query);
		$semesters = $db->loadObjectList();

		// Get the id of the item
		$pk = JRequest::getVar('id');

		return JHTML::_('select.genericlist', $semesters, 'semesters[]', 'class="inputbox" size="10" multiple="multiple"', 'id',
				'name', self::getSelectedSemesters($pk)
		);
	}

	/**
	 * Returns the related semesters of the given tree node
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return  String
	 */
	private function getSelectedSemesters($id)
	{
		// Determine all semester mappings of this tree node
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_assets_semesters');
		$query->where("assets_tree_id = $id");
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$selectedSemesters = array();

		if (isset($rows))
		{
			foreach ($rows as $row)
			{
				array_push($selectedSemesters, $row->semesters_majors_id);
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
