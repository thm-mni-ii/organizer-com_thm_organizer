<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldParent
 * @description JFormFieldParent component admin field
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

/**
 * Class JFormFieldParent for component com_thm_organizer
 *
 * Class provides methods to create a form field
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class JFormFieldParent extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 */
	protected $type = 'Parent';

	/**
	 * Returns a selectionbox where stored coursepool can be chosen as a parent node
	 *
	 * @return Select box
	 */
	public function getInput()
	{
		$dbo = JFactory::getDBO();

		$scriptDir = str_replace(JPATH_SITE . DS, '', "administrator/components/com_thm_organizer/models/fields/");
		JHTML::script('parent.js', $scriptDir, false);

		$majorId = $_SESSION['stud_id'];
		$rowID = JRequest::getVar('id');

		$query = $dbo->getQuery(true);
		$query->select("*");
		$query->select("CONCAT(title_de, ' (', semesters.name, ') ', assets_tree.id) AS title_de ");
		$query->from(' #__thm_organizer_assets_tree AS at');
		$query->innerJoin('#__thm_organizer_assets_semesters AS assets_semesters ON assets_semesters.assets_tree_id = at.id');
		$query->innerJoin('#__thm_organizer_semesters_majors AS semesters_majors ON assets_semesters.semesters_majors_id = semesters_majors.id');
		$query->innerJoin('#__thm_organizer_assets AS assets ON assets.id = at.asset');
		$query->innerJoin('#__thm_organizer_semesters AS semesters ON semesters.id = semesters_majors.semester_id');
		$query->where(' assets.asset_type_id = 2');
		$query->where("semesters_majors.major_id = $majorId");
		$query->order('assets.title_de');
		
		$dbo->setQuery($query);
		$pools = $dbo->loadObjectList();

		$blankItem = new stdClass;
		$blankItem->id = 0;
		$blankItem->asset = 0;
		$blankItem->title_de = '-- None --';
		$items = array_merge(array($blankItem), $pools);

		$javaScript = "onchange='disableDropdown(this)' ";

		return JHTML::_("select.genericlist", $items, "jform[parent_id]", "$javaScript", "asset", "title_de", self::getSelectedParent($rowID));
	}

	/**
	 * Determines the related parent node of an asset
	 *
	 * @param   Integer  $parentID  Id
	 *
	 * @return  String
	 */
	private function getSelectedParent($parentID)
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);

		$query->select("parent_id");
		$query->from('#__thm_organizer_assets_tree');
		$query->where("id = '$parentID'");
		$dbo->setQuery($query);
		return $dbo->loadResult();
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
