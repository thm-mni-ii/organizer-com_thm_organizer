<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldParentPool
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_COMPONENT . '/assets/helpers/mapping.php';

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
        $ownID = JFactory::getApplication()->input->getInt('id', 0);
        $mappings = array();
        $parentIDs = array();
        $ownIDs = array();
        THM_OrganizerHelperMapping::getMappingData($ownID, $mappings, $parentIDs, $ownIDs);

        $options = array();
        $options[] = '<option value="-1">' . JText::_('COM_THM_ORGANIZER_POM_NO_PARENT') . '</option>';

        if (!empty($mappings))
        {
            $language = explode('-', JFactory::getLanguage()->getTag());
            $programEntries = THM_OrganizerHelperMapping::getProgramEntries($mappings);
            $programMappings = THM_OrganizerHelperMapping::getProgramMappings($programEntries);
            $children = THM_OrganizerHelperMapping::getChildren($mappings);
            $unwantedMappings = array_merge($ownIDs, $children);

            foreach ($programMappings as $mapping)
            {
                if (in_array($mapping['id'], $unwantedMappings) OR !empty($mapping['subjectID']))
                {
                    continue;
                }
                if (!empty($mapping['poolID']))
                {
                    $options[] = THM_OrganizerHelperMapping::getPoolOption($mapping, $language[0], $parentIDs);
                }
                else
                {
                    $options[] = THM_OrganizerHelperMapping::getProgramOption($mapping, $language[0], $parentIDs);
                }
            }
        }
 
        $select = '<select id="jformparentID" name="jform[parentID][]" multiple="multiple" size="10">';
        $select .= implode('', $options) . '</select>';
        return $select;
    }
}
