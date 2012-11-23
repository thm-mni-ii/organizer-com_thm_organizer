<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		JFormFieldParent
 * @description JFormFieldParent component admin field
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Class JFormFieldParent for component com_thm_organizer
 *
 * Class provides methods to create a form field
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class JFormFieldParent extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 * @since  1.0
	 */
	protected $type = 'Parent';

	/**
	 * Returns a selectionbox where stored coursepool can be chosen as a parent node
	 *
	 * @return Select box
	 */
	public function getInput()
	{
		$scriptDir = str_replace(JPATH_SITE . DS, '', "administrator/components/com_thm_organizer/models/fields/");
		$sortButtons = true;
		$db = JFactory::getDBO();

		// Add script-code to the document head
		JHTML::script('parent.js', $scriptDir, false);

		// Get Major-ID from the current session
		$majorId = $_SESSION['stud_id'];

		$id = JRequest::getVar('id');

		// Build the sql statement
		$query = $db->getQuery(true);

		$query->select("*, CONCAT(title_de, ' (', semesters.name, ')') as title_de ");
		$query->select("count(*) as count");
		$query->select("assets_tree.asset as asset_id");
		$query->select("assets_semesters.semesters_majors_id as sem_id");
		$query->select("semesters.name as semester_name");
		$query->from(' #__thm_organizer_assets_tree as assets_tree');
		$query->join('inner', '#__thm_organizer_assets_semesters as assets_semesters ON assets_semesters.assets_tree_id = assets_tree.id');
		$query->join('inner', '#__thm_organizer_semesters_majors as semesters_majors ON assets_semesters.semesters_majors_id = semesters_majors.id');
		$query->join('inner', '#__thm_organizer_assets as assets ON assets.id = assets_tree.asset');
		$query->join('inner', '#__thm_organizer_semesters as semesters ON semesters.id = semesters_majors.semester_id');
		$query->where(' assets.asset_type_id = 2');
		$query->where("semesters_majors.major_id = $majorId");
		$query->order('assets.title_de');

		$db->setQuery($query);
		$pools = $db->loadObjectList();

		// Add an additional line to the selection box
		$blankItem->id = 0;

		$blankItem->asset = 0;
		$blankItem->title_de = '-- None --';
		$items = array_merge(array($blankItem), $pools);

		$js = "onchange='disableDropdown(this)' ";

		return JHTML::_("select.genericlist", $items, "jform[parent_id]", "$js", "asset", "title_de", self::getSelectedParent($id));
	}

	/**
	 * Determines the related parent node of an asset
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return  String
	 */
	private function getSelectedParent($id)
	{
		$db = JFactory::getDBO();

		// Build the query
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_assets_tree');
		$query->where("#__thm_organizer_assets_tree.id = $id");
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (isset($rows[0]->parent_id))
		{
			return $rows[0]->parent_id;
		}
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
