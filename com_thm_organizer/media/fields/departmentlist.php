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

defined('_JEXEC') or die;

use \THM_OrganizerHelperHTML as HTML;

\JFormHelper::loadFieldClass('list');

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/departments.php';

/**
 * Class loads a list of department entries for selection and implements javascript for updating other fields in form
 *
 * @todo Does this need its own field? Can the js not just be included with the schedule? Is the normal js field not
 *       adequate for the selection itself?
 */
class JFormFieldDepartmentList extends \JFormFieldList
{
    /**
     * Type
     *
     * @var    String
     */
    public $type = 'departmentlist';

    /**
     * Method to get the field input markup for a generic list of schedule values e.g. departments.
     * On change the other fields get updated with the selected value by ajax.
     *
     * @return string  The field input markup.
     */
    protected function getInput()
    {
        \JFactory::getDocument()->addScript(\JUri::root() . '/media/com_thm_organizer/js/departmentlist.js');

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
        foreach (THM_OrganizerHelperDepartments::getOptions() as $key => $department) {
            $options[] = HTML::_('select.option', $key, $department);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
