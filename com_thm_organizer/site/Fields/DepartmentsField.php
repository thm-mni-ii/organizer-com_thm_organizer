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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Access;
use Organizer\Helpers\Departments;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a select box for departments.
 */
class DepartmentsField extends ListField
{
    /**
     * @var  string
     */
    protected $type = 'Departments';

    private function addFilters(&$query)
    {
        $view = OrganizerHelper::getInput()->getCmd('view');
        if (empty($view)) {
            return;
        }

        $action               = null;
        $resource             = OrganizerHelper::getResource($view);
        $isDepartmentResource = in_array($resource, ['category', 'teacher']);
        $isDocumentResource   = in_array($resource, ['program', 'pool', 'subject']);

        if ($isDepartmentResource or $resource === 'schedule') {
            $action = 'schedule';
            if ($isDepartmentResource) {
                $query->innerJoin('#__thm_organizer_department_resources AS dpr ON dpr.departmentID = depts.id');
                $query->where("dpr.{$resource}ID IS NOT NULL");
            }
        } elseif ($isDocumentResource) {
            $action = 'document';
            $table  = OrganizerHelper::getPlural($resource);
            $query->innerJoin("#__thm_organizer_$table AS res ON res.departmentID = depts.id");
        }

        $auth = $this->getAttribute('auth', '0') === '0' ? false : true;

        if ($auth and $action) {
            $allowedIDs = Access::getAccessibleDepartments($action);
            $query->where("depts.id IN ( '" . implode("', '", $allowedIDs) . "' )");
        }
    }

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
        $options = parent::getOptions();

        // Edit views always require access.
        $shortTag = Languages::getShortTag();
        $dbo      = Factory::getDbo();
        $query    = $dbo->getQuery(true);
        $query->select("DISTINCT depts.id AS value, depts.short_name_$shortTag AS text");
        $query->from('#__thm_organizer_departments AS depts');

        $this->addFilters($query);

        $query->order('text ASC');
        $dbo->setQuery($query);
        $departments = OrganizerHelper::executeQuery('loadAssocList');

        if (empty($departments)) {
            return $options;
        }

        foreach ($departments as $department) {
            $options[] = HTML::_('select.option', $department['value'], $department['text']);
        }

        return $options;
    }
}