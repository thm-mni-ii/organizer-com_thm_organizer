<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldSubjectParents
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');
require_once JPATH_COMPONENT . '/assets/helpers/mapping.php';

/**
 * Class JFormFieldParent for component com_thm_organizer
 *
 * Class provides methods to create a form field
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldSubjectParents extends JFormField
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
        $subjectID = JFactory::getApplication()->input->getInt('id', 0);
        $existingMappings = array();
        $selectedParents = array();
        $this->getExistingMappings($subjectID, $existingMappings, $selectedParents);

        $options = array();
        $options[] = '<option value="-1">' . JText::_('COM_THM_ORGANIZER_POM_NO_PARENT') . '</option>';

        if (!empty($existingMappings))
        {
            $language = explode('-', JFactory::getLanguage()->getTag());
            $programEntries = THM_OrganizerHelperMapping::getProgramEntries($existingMappings);
            $programMappings = THM_OrganizerHelperMapping::getProgramMappings($programEntries);

            foreach ($programMappings as $mapping)
            {
                if (!empty($mapping['subjectID']))
                {
                    continue;
                }
                if (!empty($mapping['poolID']))
                {
                    $options[] = THM_OrganizerHelperMapping::getPoolOption($mapping, $language[0], $selectedParents);
                }
                else
                {
                    $options[] = THM_OrganizerHelperMapping::getProgramOption($mapping, $language[0], $selectedParents, true);
                }
            }
        }

        $select = '<select id="jformparentID" name="jform[parentID][]" multiple="multiple" size="10">';
        $select .= implode('', $options) . '</select>';
        return $select;
    }

    /**
     * Retrieves existing mappings
     * 
     * @param   int    $subjectID          the id of the subject
     * @param   array  &$existingMappings  an array to store existing mappings in
     * @param   array  &$selectedParents   an array to store selected parents in
     * 
     * @return  void
     */
    private function getExistingMappings($subjectID, &$existingMappings, &$selectedParents)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('parentID, lft, rgt');
        $query->from('#__thm_organizer_mappings');
        $query->where("subjectID = '$subjectID'");
        $query->order('lft ASC');
        $dbo->setQuery((string) $query);
        
        try 
        {
            $existingMappings = array_merge($existingMappings, $dbo->loadAssocList());
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        try 
        {
            $selectedParents = array_merge($selectedParents, $dbo->loadColumn());
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
    }
}
