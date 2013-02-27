<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldResponsible
 * @description JFormFieldResponsible component admin field
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

/**
 * Class JFormFieldResponsible for component com_thm_organizer
 * Class provides methods to create a form field that contains the responsibles
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldResponsible extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 * @since  1.0
	 */
	protected $type = 'Responsible';

	/**
	 * Returns a selection box which contains persons for the responsible selection
	 *
	 * @return Select box
	 */
	public function getInput()
	{
		$dbo = JFactory::getDBO();

		// Build the query
		$query = $dbo->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_lecturers');
		$query->order('surname');
		$dbo->setQuery((string) $query);
		$responsible = $dbo->loadObjectList();

		return JHTML::_(
						'select.genericlist',
						$responsible,
						'jform[responsible_id]',
						'',
						'id',
						'surname',
						self::getSelectedResponsible(JRequest::getVar('id'))
					   );
	}

	/**
	 * Gets the selected responsible from the given asset id
	 *
	 * @param   Integer  $moduleID  Id
	 *
	 * @return  array  array with the lecturers responsible for the selected
	 *				   modules or empty if none were found
	 */
	private function getSelectedResponsible($moduleID)
	{
		$dbo = JFactory::getDBO();

		// Build the query
		$query = $dbo->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_lecturers_assets as lecturer_assets');
		$query->where("lecturer_assets.modul_id = $moduleID");
		$query->where("lecturer_assets.lecturer_type = 1");
		$dbo->setQuery($query);
		$rows = $dbo->loadObjectList();

		$selectedLecturers = array();
		if (isset($rows))
		{
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
			$title = trim(JText::_($text), ':');
			$description = JText::_($this->description);
			$titleText = htmlspecialchars($title . '::' . $description, ENT_COMPAT, 'UTF-8');
			$label .= ' title="' . $titleText . '"'; 
		}

		// Add the label text and closing tag.
		$label .= '>' . $replace . JText::_($text) . '</label>';

		return $label;
	}
}
