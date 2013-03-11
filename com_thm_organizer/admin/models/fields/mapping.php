<?php
/**
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
defined('_JEXEC') or die;
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
 */
class JFormFieldMapping extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 */
	protected $type = 'Mapping';

	/**
	 * Returns a multiple select which includes the related semesters of the current tree node
	 *
	 * @return Select box
	 */
	public function getInput()
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		$query->select("sem_major.id AS id, name");
		$query->from('#__thm_organizer_semesters_majors as sem_major');
		$query->join('inner', '#__thm_organizer_semesters as semesters ON sem_major.semester_id = semesters.id');
		$query->where("major_id = '{$_SESSION['stud_id']}'");
		$query->order('name ASC');
		$dbo->setQuery($query);
		$semesters = $dbo->loadObjectList();


		return JHTML::_(
						'select.genericlist',
						$semesters,
						'semesters[]',
						'class="inputbox" size="10" multiple="multiple"',
						'id',
						'name',
						self::getSelectedSemesters(JRequest::getVar('id'))
					   );
	}

	/**
	 * Returns the related semesters of the given tree node
	 *
	 * @param   Integer  $assetID  Id
	 *
	 * @return  String
	 */
	private function getSelectedSemesters($assetID)
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		$query->select("semesters_majors_id");
		$query->from('#__thm_organizer_assets_semesters');
		$query->where("assets_tree_id = '$assetID'");
		$dbo->setQuery((string) $query);
		$selectedSemesters = $dbo->loadResultArray();

		return empty($selectedSemesters)? array (): $selectedSemesters;
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
			$title = trim(JText::_($text), ':') . '::' . JText::_($this->description);
			$label .= ' title="' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '"';
		}

		// Add the label text and closing tag.
		$label .= '>' . $replace . JText::_($text) . '</label>';

		return $label;
	}
}
