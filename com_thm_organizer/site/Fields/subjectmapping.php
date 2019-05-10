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
use Organizer\Helpers\HTML;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a select box for explicitly mapping subject documentation to plan subjects. This is also done implicitly
 * during the schedule import process according to degree programs and the subject's module number.
 */
class JFormFieldSubjectMapping extends \Joomla\CMS\Form\FormField
{
    protected $type = 'subjectMapping';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return string  the HTML output
     */
    public function getInput()
    {
        $fieldName = $this->getAttribute('name');
        $subjectID = OrganizerHelper::getInput()->getInt('id', 0);

        $dbo           = Factory::getDbo();
        $selectedQuery = $dbo->getQuery(true);
        $selectedQuery->select('plan_subjectID');
        $selectedQuery->from('#__thm_organizer_subject_mappings');
        $selectedQuery->where("subjectID = '$subjectID'");
        $dbo->setQuery($selectedQuery);
        $selected = OrganizerHelper::executeQuery('loadColumn', []);

        $planSubjectQuery = $dbo->getQuery(true);
        $planSubjectQuery->select('id AS value, name');
        $planSubjectQuery->from('#__thm_organizer_plan_subjects');
        $planSubjectQuery->order('name');
        $dbo->setQuery($planSubjectQuery);

        $planSubjects = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($planSubjects)) {
            $planSubjects = [];
        }

        $options = [];
        foreach ($planSubjects as $planSubject) {
            $options[$planSubject['value']] = $planSubject['name'];
        }

        $attributes       = ['multiple' => 'multiple', 'class' => 'inputbox', 'size' => '10'];
        $selectedMappings = empty($selected) ? [] : $selected;

        return HTML::selectBox($options, $fieldName, $attributes, $selectedMappings, true);
    }
}
