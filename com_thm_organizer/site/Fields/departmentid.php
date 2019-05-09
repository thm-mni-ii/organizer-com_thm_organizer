<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

require_once JPATH_ROOT . '/components/com_thm_organizer/autoloader.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Access;
use Organizer\Helpers\Departments;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

FormHelper::loadFieldClass('list');

/**
 * Class creates a select box for departments.
 */
class JFormFieldDepartmentID extends \JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'departmentID';

    /**
     * Method to get the field input markup for department selection.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string  The field input markup.
     */
    protected function getInput()
    {
        // Add custom js script to update other fields like programs
        if (!empty($this->class) && $this->class === 'departmentlist') {
            Factory::getDocument()->addScript(Uri::root() . 'components/com_thm_organizer/js/departmentlist.js');
        }

        $resource = $this->getAttribute('resource');
        if (empty($resource) or !in_array($resource, ['program', 'teacher'])) {
            return parent::getInput();
        }

        $attr = '';

        $attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
        $attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
        $attr .= $this->multiple ? ' multiple' : '';
        $attr .= $this->required ? ' required aria-required="true"' : '';
        $attr .= $this->autofocus ? ' autofocus' : '';

        // Initialize JavaScript field attributes.
        $attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

        // Get the field options.
        $options = (array)$this->getOptions();

        $resourceID = OrganizerHelper::getInput()->getInt('id');
        if (empty($resourceID)) {
            $selected = OrganizerHelper::getSelectedIDs();
        } else {
            $selected = [$resourceID];
        }

        $departmentIDs = Departments::getDepartmentsByResource($resource, $selected);

        return HTML::_(
            'select.genericlist',
            $options,
            $this->name,
            trim($attr),
            'value',
            'text',
            $departmentIDs,
            $this->id
        );
    }

    /**
     * Returns an array of options
     *
     * @return array  the department options
     */
    protected function getOptions()
    {
        $shortTag = Languages::getShortTag();
        $dbo      = Factory::getDbo();
        $query    = $dbo->getQuery(true);
        $query->select("DISTINCT d.id AS value, d.short_name_$shortTag AS text");
        $query->from('#__thm_organizer_departments AS d');

        // For use in the merge view
        $app               = OrganizerHelper::getApplication();
        $isBackend         = $app->isClient('administrator');
        $selectedIDs       = OrganizerHelper::getSelectedIDs();
        $resource          = $this->getAttribute('resource', '');
        $validResources    = ['program', 'teacher'];
        $isValidResource   = in_array($resource, $validResources);
        $filterForSelected = ($isBackend and !empty($selectedIDs) and $isValidResource);
        if ($filterForSelected) {
            // Set the selected items
            $this->value = $selectedIDs;

            // Apply the filter
            $query->innerJoin('#__thm_organizer_department_resources AS dpr ON dpr.departmentID = d.id');
            $query->where("dpr.{$resource}ID IN ( '" . implode("', '", $selectedIDs) . "' )");
        }

        // Should a restriction be made according to access rights?
        $action = $this->getAttribute('action', '');

        if (!empty($action)) {
            $allowedIDs = Access::getAccessibleDepartments($action);
            $query->where("d.id IN ( '" . implode("', '", $allowedIDs) . "' )");
        }

        $query->order('text ASC');
        $dbo->setQuery($query);

        $defaultOptions = parent::getOptions();
        $departments    = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($departments)) {
            return $defaultOptions;
        }

        $options = [];
        foreach ($departments as $department) {
            $options[] = HTML::_('select.option', $department['value'], $department['text']);
        }

        return array_merge($defaultOptions, $options);
    }
}
