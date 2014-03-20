<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldSubjectFields
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

/**
 * Class loads a list of fields for selection
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldSubjectFields extends JFormField
{
    /**
     * Type
     *
     * @var    String
     */
    protected $type = 'subjectFields';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return Select box
     */
    public function getInput()
    {
        $dbo = JFactory::getDBO();
        $subjectID = JRequest::getInt('id');
 
        $selectedQuery = $dbo->getQuery(true);
        $selectedQuery->select('fieldID')->from('#__thm_organizer_subjects')->where("id = '$subjectID'");
        $dbo->setQuery((string) $selectedQuery);
        $result = $dbo->loadResult();
        $selectedValue = empty($result)? '' : $result;

        $fieldsQuery = $dbo->getQuery(true);
        $fieldsQuery->select("f.id AS value, color, field");
        $fieldsQuery->from('#__thm_organizer_fields AS f');
        $fieldsQuery->leftJoin('#__thm_organizer_colors AS c on f.colorID = c.id');
        $fieldsQuery->order('field');
        $dbo->setQuery((string) $fieldsQuery);
        $fields = $dbo->loadAssocList();

        $options = array();
        foreach ($fields as $field)
        {
            $style = empty($field['color'])? '' : ' style="background-color:#' . $field['color'] . '"';
            $selected = $field['value'] == $selectedValue? ' selected="selected"' : '';
            $options[] = '<option value="' . $field['value'] . '"' . $style . $selected . '>' . $field['field'] . '</option>';
        }
        return '<select name="jform[fieldID]" id="jform_fieldID"">' . implode('', $options) . "</select>";
    }
}
