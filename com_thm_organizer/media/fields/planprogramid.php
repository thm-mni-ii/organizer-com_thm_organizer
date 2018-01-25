<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldPlanProgramID
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

/**
 * Class creates a form field for merging plan pool programs
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldPlanProgramID extends JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'planProgramID';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return  array the options for the select box
     */
    public function getOptions()
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT ppr.id AS value, ppr.name AS text");
        $query->from("#__thm_organizer_plan_programs AS ppr");
        $query->order('text ASC');

        // For use in the merge view
        $selectedIDs = JFactory::getApplication()->input->get('cid', [], 'array');
        if (!empty($selectedIDs)) {
            $selectedIDs = Joomla\Utilities\ArrayHelper::toInteger($selectedIDs);
            $query->innerJoin("#__thm_organizer_plan_pools AS ppl ON ppl.programID = ppr.id");
            $query->where("ppl.id IN ( '" . implode("', '", $selectedIDs) . "' )");
        }

        $departmentRestrict = $this->getAttribute('departmentRestrict', 'false');

        if ($departmentRestrict == 'true') {
            $formData     = JFactory::getApplication()->input->get('jform', [], 'array');
            $departmentID = (!empty($formData) AND !empty($formData['departmentID']) AND is_numeric($formData['departmentID'])) ?
                $formData['departmentID'] : 0;

            if (!empty($departmentID)) {
                $query->innerJoin("#__thm_organizer_department_resources AS dr ON dr.programID = ppr.id");
                $query->where("dr.departmentID = '$departmentID'");
            }
        }

        $dbo->setQuery($query);
        $defaultOptions = parent::getOptions();

        try {
            $values = $dbo->loadAssocList();
        } catch (Exception $exc) {
            return $defaultOptions;
        }
        $options = [];

        foreach ($values as $value) {
            if (!empty($value['value'])) {
                $options[] = JHtml::_('select.option', $value['value'], $value['text']);
            }
        }

        // An empty/default value should not be allowed in a merge view.
        if (empty($selectedIDs)) {
            $options = array_merge($defaultOptions, $options);
            return $options;
        }

        return count($options) ? $options : $defaultOptions;
    }
}
