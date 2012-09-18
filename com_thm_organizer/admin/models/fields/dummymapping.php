<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		JFormFieldDummymapping
 * @description JFormFieldDummymapping component admin field
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Class JFormFieldDummymapping for component com_thm_organizer
 *
 * Class provides methods to create a form field
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class JFormFieldDummymapping extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 * @since  1.0
	 */
	protected $type = 'Dummymapping';

	/**
	 * Returns a select box
	 *
	 * @return Select box
	 */
	public function getInput()
	{
		$db = JFactory::getDBO();

		$scriptDir = str_replace(JPATH_SITE . DS, '', "administrator/components/com_thm_organizer/models/fields/");
		$sortButtons = true;
		$db = JFactory::getDBO();

		// Select all assets from the database
		$query = $db->getQuery(true);

		$query->select("*, assets.id as id, CONCAT(title_de) as title_de ");
		$query->from(' #__thm_organizer_assets as assets');
		$query->join('inner', '#__thm_organizer_asset_types as asset_types ON asset_types.id = assets.asset_type_id');
		$query->where('asset_types.id = 3');
		$query->order('title_de');
		$db->setQuery($query);
		$assets = $db->loadObjectList();

		foreach ($assets as $asset)
		{
			$asset->title_de .= " (" . $asset->name . ", "
			. ($asset->lsf_course_code ? $asset->lsf_course_code : ($asset->his_course_code ? $asset->his_course_code :
					($asset->lsf_course_id ? $asset->lsf_course_id : $asset->id )))
					. ")";
		}

		// Edit mode: id of the current row
		$pk = JRequest::getVar('id');

		// Adds an additional item to the select box
		$blankItem->id = 0;

		$blankItem->title_de = '-- None --';
		$items = array_merge(array($blankItem), $assets);

		return JHTML::_('select.genericlist', $items, 'jform[asset]', '', 'id', 'title_de', self::getSelectedAssets($pk));
	}

	/**
	 * Returns the selected asset of the given tree node
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return  String
	 */
	private function getSelectedAssets($id)
	{
		$db = JFactory::getDBO();

		// Build the query
		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__thm_organizer_assets_tree');
		$query->where("#__thm_organizer_assets_tree.id = $id");
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Return the id of the asset
		if (isset($rows[0]->asset))
		{
			return $rows[0]->asset;
		}
	}

	/**
	 * Method to get the field label
	 *
	 * @return String The field label
	 */
	public function getLabel()
	{
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
