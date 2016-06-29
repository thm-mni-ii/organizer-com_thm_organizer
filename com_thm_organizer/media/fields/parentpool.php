<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        JFormFieldParentPool
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Class JFormFieldParent for component com_thm_organizer
 *
 * Class provides methods to create a form field
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class JFormFieldParentPool extends JFormField
{
	/**
	 * Type
	 *
	 * @var    String
	 */
	protected $type = 'parentpool';

	/**
	 * Returns a select box in which pools can be chosen as a parent node
	 *
	 * @return  string  the HTML for the parent pool select box
	 */
	public function getInput()
	{
		$options = $this->getOptions();
		$select  = '<select id="jformparentID" name="jform[parentID][]" multiple="multiple" size="10">';
		$select .= implode('', $options) . '</select>';

		return $select;
	}

	/**
	 * Gets pool options for a select list. All parameters come from the
	 *
	 * @return  array  the options
	 */
	public function getOptions()
	{
		// Get basic resource data
		$resourceID   = JFactory::getApplication()->input->getInt('id', 0);
		$contextParts = explode('.', $this->form->getName());
		$resourceType = str_replace('_edit', '', $contextParts[1]);

		$mappings   = array();
		$mappingIDs = array();
		$parentIDs  = array();
		THM_OrganizerHelperMapping::setMappingData($resourceID, $resourceType, $mappings, $mappingIDs, $parentIDs);

		$options   = array();
		$options[] = '<option value="-1">' . JText::_('JNONE') . '</option>';

		if (!empty($mappings))
		{
			$unwantedMappings = array();
			$programEntries   = THM_OrganizerHelperMapping::getProgramEntries($mappings);
			$programMappings  = THM_OrganizerHelperMapping::getProgramMappings($programEntries);

			// Pools should not be allowed to be placed anywhere where recursion could occur
			if ($resourceType == 'pool')
			{
				$children         = THM_OrganizerHelperMapping::getChildren($mappings);
				$unwantedMappings = array_merge($unwantedMappings, $mappingIDs, $children);
			}

			foreach ($programMappings as $mapping)
			{
				// Recursive mappings or mappings belonging to subjects should not be offered
				if (in_array($mapping['id'], $unwantedMappings) OR !empty($mapping['subjectID']))
				{
					continue;
				}

				if (!empty($mapping['poolID']))
				{
					$options[] = THM_OrganizerHelperMapping::getPoolOption($mapping, $parentIDs);
				}
				else
				{
					$options[] = THM_OrganizerHelperMapping::getProgramOption($mapping, $parentIDs, $resourceType);
				}
			}
		}

		return $options;
	}
}
