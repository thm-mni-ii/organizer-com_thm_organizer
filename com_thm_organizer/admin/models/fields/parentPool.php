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
class JFormFieldParentPool extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 */
	protected $type = 'parentPool';

	/**
	 * Returns a selectionbox where stored coursepool can be chosen as a parent node
	 *
	 * @return Select box
	 */
	public function getInput()
	{
        $language = explode('-', JFactory::getLanguage()->getTag());

		$dbo = JFactory::getDBO();
		$poolID = JRequest::getInt('id');
        
        $existingMappingsQuery = $dbo->getQuery(true);
        $existingMappingsQuery->select('id, parentID, lft, rgt')->from('#__thm_organizer_mappings')->where("poolID = '$poolID'");
        $existingMappingsQuery->order('lft ASC');
        $dbo->setQuery((string) $existingMappingsQuery);
        $existingMappings = $dbo->loadAssocList();
        $ownMappings = $dbo->loadResultArray();
        $selectedParents = $dbo->loadResultArray(1);

        if (!empty($existingMappings))
        {
            $programs = array();
            $programsQuery = $dbo->getQuery(true);
            $programsQuery->select('id, programID, lft, rgt')->from('#__thm_organizer_mappings');
            foreach ($existingMappings AS $mapping)
            {
                $programsQuery->clear('where');
                $programsQuery->where("lft < '{$mapping['lft']}'");
                $programsQuery->where("rgt > '{$mapping['rgt']}'");
                $programsQuery->where("parentID IS NULL");
                $dbo->setQuery((string) $programsQuery);
                $program = $dbo->loadAssoc();
                if (!in_array($program, $programs))
                {
                    $programs[] = $program;
                }
            }

            $language = explode('-', JFactory::getLanguage()->getTag());

            $programMappings = array();
            $programMappingsQuery = $dbo->getQuery(true);
            $programMappingsQuery->select('*');
            $programMappingsQuery->from('#__thm_organizer_mappings');
            foreach ($programs as $program)
            {
                $programMappingsQuery->clear('where');
                $programMappingsQuery->where("lft >= '{$program['lft']}'");
                $programMappingsQuery->where("rgt <= '{$program['rgt']}'");
                $programMappingsQuery->order('lft ASC');
                $dbo->setQuery((string) $programMappingsQuery);
                $results = $dbo->loadAssocList();
                $programMappings = array_merge($programMappings, empty($results)? array() : $results);
            }

            $poolsTable = JTable::getInstance('pools', 'THM_OrganizerTable');
            foreach ($programMappings as $key => $mapping)
            {
                if (in_array($mapping['id'], $ownMappings))
                {
                    unset($programMappings[$key]);
                    continue;
                }

                if (!empty($mapping['poolID']))
                {
                    $poolsTable->load($mapping['poolID']);
                    $name = $language[0] == 'de'? $poolsTable->name_de : $poolsTable->name_en;

                    $level = 0;
                    $indent = '';
                    while ($level < $mapping['level'])
                    {
                        $indent .= "   ";
                        $level++;
                    }
                    $programMappings[$key]['name'] = $indent . "|_" . $name;
                }
                else
                {
                    $programNameQuery = $dbo->getQuery(true);
                    $programNameQuery->select(" CONCAT( dp.subject, ', (', d.abbreviation, ' ', dp.version, ')') AS name");
                    $programNameQuery->from('#__thm_organizer_degree_programs AS dp');
                    $programNameQuery->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
                    $programNameQuery->where("dp.id = '{$mapping['programID']}'");
                    $dbo->setQuery((string) $programNameQuery);
                    $programMappings[$key]['name'] = $dbo->loadResult();
                }
            }

            $selectPools = array();
            $selectPools[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_POM_SEARCH_PARENT'));
            $selectPools[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_POM_NO_PARENT'));
            $pools = array_merge($selectPools, $programMappings);

            $attributes = array('multiple' => 'multiple');
            return JHTML::_("select.genericlist", $pools, "jform[parentID][]", $attributes, "id", "name", $selectedParents);
        }
        
        $attributes = array('multiple' => 'multiple');
        return JHTML::_("select.genericlist", array(), "jform[parentID][]", $attributes, 'id', 'name');
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
