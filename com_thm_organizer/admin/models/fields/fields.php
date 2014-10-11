<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldFields
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class loads a list of fields for selection
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldFields extends JFormField
{
    /**
     * Type
     *
     * @var    String
     */
    protected $type = 'fields';

    /**
     * Returns a selection box where stored course pool can be chosen as a parent node
     *
     * @return  string  a HTML String representing the field selection box
     *
     * @throws  exception
     */
    public function getInput()
    {
        $dbo = JFactory::getDBO();
        $subjectID = JFactory::getApplication()->input->getInt('id', 0);
 
        $selectedQuery = $dbo->getQuery(true);
        $selectedQuery->select('fieldID')->from('#__thm_organizer_subjects')->where("id = '$subjectID'");
        $dbo->setQuery((string) $selectedQuery);
        
        try 
        {
            $result = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        $selectedValue = empty($result)? '' : $result;

        $fieldsQuery = $dbo->getQuery(true);
        $fieldsQuery->select("f.id AS value, color, field");
        $fieldsQuery->from('#__thm_organizer_fields AS f');
        $fieldsQuery->leftJoin('#__thm_organizer_colors AS c on f.colorID = c.id');
        $fieldsQuery->order('field');
        $dbo->setQuery((string) $fieldsQuery);
        
        try
        {
            $fields = $dbo->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        $options = array();
        foreach ($fields as $field)
        {
            $options[] = $this->getOption($field, $selectedValue);
        }
        return '<select name="jform[fieldID]" id="jform_fieldID"">' . implode('', $options) . "</select>";
    }

    /**
     * Creates a HTML Option Tag for the subject field
     *
     * @param   array   $field          the array containing field information
     * @param   string  $selectedValue  the selected field value
     *
     * @return string
     */
    private function getOption($field, $selectedValue)
    {
        if (empty($field['color']))
        {
            $style = '';
        }
        else
        {
            $textColor = THM_ComponentHelper::getTextColor($field['color']);
            $style = ' style="background-color:#' . $field['color'] . '; color:#' . $textColor . '"';
        }
        $selected = $field['value'] == $selectedValue? ' selected="selected"' : '';
        return '<option value="' . $field['value'] . '"' . $style . $selected . '>' . $field['field'] . '</option>';
    }
}
