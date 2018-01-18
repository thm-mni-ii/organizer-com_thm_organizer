<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_admin
 * @name        JFormFieldDepartmentList
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/departments.php';

/**
 * Class loads a list of department entries for selection and implements javascript for updating other fields in form
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class JFormFieldDepartmentList extends JFormFieldList
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
     * @return  string  The field input markup.
     */
    protected function getInput()
    {
        JFactory::getDocument()->addScript(JUri::root() . "/media/com_thm_organizer/js/departmentlist.js");

        return parent::getInput();
    }

    /**
     * Method to get the field options for all departments
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $options     = [];
        $departments = THM_OrganizerHelperDepartments::getPlanDepartments();

        foreach ($departments as $key => $department) {
            $options[] = JHtml::_('select.option', $key, $department);
        }

        return array_merge(parent::getOptions(), $options);
    }
}