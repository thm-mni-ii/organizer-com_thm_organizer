<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		JFormFieldSemester
 * @description JFormFieldSemester component site field
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.form.formfield');

/**
 * Class JFormFieldSemester for component com_thm_organizer
 *
 * Class provides methods to create a multiple select which includes the related semesters of the current tree node
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class JFormFieldSemester extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 * @since  1.0
	 */
	protected $type = 'Semester';

	/**
	 * Returns a multiple select which includes the related semesters of the current tree node
	 *
	 * @return Multiple select box
	 */
	public function getInput()
	{
		$js = "";
		$sortButtons = true;
		$db = JFactory::getDBO();

		$scriptDir = str_replace(JPATH_SITE . DS, '', "components/com_thm_organizer/models/fields/");
		JHTML::script('semester.js', $scriptDir, false);

		$arrows = '<a onclick="roleup()" id="sortup"><img src="../administrator/components/com_thm_groups/img/uparrow.png" " .
		"title="Rolle eine Position h&ouml;her" /></a>';
		$arrows .= '<a onclick="roledown()" id="sortdown"><img src="../administrator/components/com_thm_groups/img/downarrow.png" " .
		"title="Rolle eine Position niedriger" /></a>';

		$db = JFactory::getDBO();

		/* get the major id */
		$id = JRequest::getVar('id');

		$query = $db->getQuery(true);
		$query->select("*");
		$query->from('#__menu');
		$query->where("id = $id");
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$params = json_decode($rows[0]->params);

		if(isset($params->major))
		{
			$major = $params->major;
		}
		else
		{
			$arr = array();
			return JHTML::_('select.genericlist', $arr, 'jform[params][semesters][]',
					$js . 'class="inputbox" size="10" multiple="multiple"', 'id', 'name', $this->value
			) . $arrows;
		}
		
		// Build the query
		$query = $db->getQuery(true);
		$query->select("sem_major.semester_id AS id");
		$query->select("name");
		$query->from('#__thm_organizer_semesters_majors as sem_major');
		$query->join('inner', '#__thm_organizer_semesters as semesters ON sem_major.semester_id = semesters.id');
		$query->where("major_id = $major");
		$query->order('name ASC');
		$db->setQuery($query);
		$semesters = $db->loadObjectList();
		$semesters2 = $db->loadResultArray();
		$semesters3 = $db->loadRowList();

		if ($this->value)
		{
			$result = array();

			foreach ($semesters as $key => $semester)
			{
				$orderpos = array_search($semester->id, $this->value);

				if ($orderpos !== false)
				{
					$result[$orderpos] = $semester;
				}
			}

			$diff = array_diff($semesters2, $this->value);

			foreach ($diff as $value)
			{
				$add = array();
				$add['id'] = $value;

				foreach ($semesters as $tempSem)
				{
					if ($tempSem->id == $value)
					{
						$add['name'] = $tempSem->name;
					}
				}

				array_push($result, $add);
			}

			ksort($result);
		}
		else
		{
			$result = $semesters;
		}
		
		if($result == null)
		{
			$result = array();
		}

		$html = JHTML::_('select.genericlist', $result, 'jform[params][semesters][]', $js .
				'class="inputbox" size="10" multiple="multiple"', 'id', 'name', $this->value
		);
		$html .= $arrows;

		return $html;
	}

	/**
	 * Returns the related semesters of the given tree node
	 *
	 * @param   Integer  $id  Id
	 *
	 * @return Array The selected Semesters
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
	 * @return <String> The field label
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
			$label .= ' title="' . htmlspecialchars(trim(JText::_($text), ':') . '::' . JText::_($this->description), ENT_COMPAT, 'UTF-8') . '"';
		}

		// Add the label text and closing tag.
		$label .= '>' . $replace . JText::_($text) . '</label>';

		return $label;
	}
}
