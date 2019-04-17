<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

defined('_JEXEC') or die;

\JFormHelper::loadFieldClass('list');

/**
 * Class loads a list of department entries for selection and implements javascript for updating other fields in form
 *
 * @todo Does this need its own field? Can the js not just be included with the schedule? Is the normal js field not
 *       adequate for the selection itself?
 */
class DepartmentListField extends \JFormFieldList
{
    /**
     * Type
     *
     * @var    String
     */
    public $type = 'DepartmentList';

    /**
     * Method to get the field input markup for a generic list of schedule values e.g. departments.
     * On change the other fields get updated with the selected value by ajax.
     *
     * @return string  The field input markup.
     */
    protected function getInput()
    {
        \Factory::getDocument()->addScript(\JUri::root() . 'components/com_thm_organizer/js/departmentlist.js');

        return parent::getInput();
    }

    /**
     * Method to get the field options for all departments
     *
     * @return array  The field option objects.
     */
    protected function getOptions()
    {
        $options = [];
        foreach (\Organizer\Helpers\Departments::getOptions() as $key => $department) {
            $options[] = \HTML::_('select.option', $key, $department);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
