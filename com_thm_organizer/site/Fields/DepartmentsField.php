<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Departments;

/**
 * Class creates a select box for departments.
 */
class DepartmentsField extends OptionsField
{
    /**
     * @var  string
     */
    protected $type = 'Departments';

    /**
     * Method to get the field input markup for department selection.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string  The field input markup.
     */
    protected function getInput()
    {
        // Add custom js script to update other fields like programs
        if (!empty($this->class) and $this->class === 'departmentlist') {
            Factory::getDocument()->addScript(Uri::root() . 'components/com_thm_organizer/js/departmentlist.js');
        }

        return parent::getInput();
    }

    /**
     * Returns an array of options
     *
     * @return array  the department options
     */
    protected function getOptions()
    {
        $options     = parent::getOptions();
        $departments = Departments::getOptions($this->getAttribute('access', ''));

        return array_merge($options, $departments);
    }
}
