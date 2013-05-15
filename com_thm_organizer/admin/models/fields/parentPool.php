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
        $existingMappingsQuery->select('*')->from('#__thm_organizer_mappings')->where("poolID = '$poolID'");
        $existingMappingsQuery->order('lft ASC');
        $dbo->setQuery((string) $existingMappingsQuery);
        $existingMappings = $dbo->loadAssocList();

        $selectedParents = array();
        if (!empty($existingMappings))
        {
            $roots = array();
            $rootsQuery = $dbo->getQuery(true);
            $rootsQuery->select('id, lft, rgt')->from('#__thm_organizer_mappings');
            foreach ($existingMappings AS $mapping)
            {
                $rootsQuery->clear('where');
                $rootsQuery->where("lft < '{$mapping['lft']}'");
                $rootsQuery->where("rgt > '{$mapping['rgt']}'");
                $rootsQuery->where("parentID IS NULL'");
                $dbo->setQuery((string) $rootsQuery);
                $rootID = $dbo->loadAssoc();
                if (!in_array($rootID, $roots))
                {
                    $roots[] = $rootID;
                }
                if (!in_array($mapping['parentID'], $selectedParents))
                {
                    $selectedParents[] = $mapping['parentID'];
                }
            }

            if (!empty($roots))
            {
                $language = explode('-', JFactory::getLanguage()->getTag());

                $programMappings = array();
                $programMappingsQuery = $dbo->getQuery(true);
                $programMappingsQuery->select('*');
                $programMappingsQuery->from('#__thm_organizer_mappings');
                foreach ($roots as $root)
                {
                    $programMappingsQuery->clear('where');
                    $programMappingsQuery->where("lft >= '{$root['lft']}'");
                    $programMappingsQuery->where("rgt <= '{$root['rgt']}'");
                    $programMappingsQuery->order('lft ASC');
                    $dbo->setQuery((string) $programMappingsQuery);
                    $results = $dbo->loadAssocList();
                    $programMappings = array_merge($programMappings, empty($results)? array() : $results);
                }

                if (!empty($programMappings))
                {
                    $poolsTable = JTable::getInstance('pools', 'THM_OrganizerTable');
                    foreach ($programMappings as $key => $mapping)
                    {
                        if (!empty($mapping['poolID']))
                        {
                            $poolsTable->load($mapping['poolID']);
                            $programMappings[$key]['name'] = $language[0] == 'de'? $poolsTable->name_de : $poolsTable->name_en;
                        }
                        else
                        {
                            $programNameQuery = $dbo->getQuery(true);
                            $programNameQuery->select(" CONCAT( dp.subject, ', (', d.abbreviation, ' ', dp.version, ')', ' Root') AS name");
                            $programNameQuery->from('#__thm_organizer_degree_programs AS dp');
                            $programNameQuery->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
                            $programNameQuery->where("d.id = '{$mapping['programID']}'");
                            $dbo->setQuery((string) $programNameQuery);
                            $programMappings[$key]['name'] = $dbo->loadResult();
                        }

                        $level = 0;
                        if ($mapping['level'] != 0)
                        {
                            $indent = '';
                            while ($level < $mapping['level'])
                            {
                                $indent .= ".    ";
                            }
                            $programMappings[$key]['name'] = $indent . "|_" . $mapping['name'];
                        }
                    }

                    $selectPools = array();
                    $selectPools[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_POM_SEARCH_PARENT'));
                    $selectPools[] = array('id' => '-1', 'name' => JText::_('COM_THM_ORGANIZER_POM_NO_PARENT'));
                    $pools = array_merge($selectPools, empty($programMappings)? array() : $programMappings);
                    
                    $attributes = array('multiple' => 'multiple');
                    return JHTML::_("select.genericlist", $pools, "jform[parentID][]", $attributes, "id", "name", $selectedParents);
                }
            }
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
