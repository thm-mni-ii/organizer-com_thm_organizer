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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
JFormHelper::loadFieldClass('list');

/**
 * Class creates a select box for (degree) programs.
 */
class JFormFieldProgramID extends JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'programID';

    /**
     * Returns a select box where stored degree programs can be chosen
     *
     * @return array  the available degree programs
     */
    protected function getOptions()
    {
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();
        $dbo      = JFactory::getDbo();
        $query    = $dbo->getQuery(true);

        $query->select("dp.id AS value, dp.name_$shortTag AS name, d.abbreviation AS degree, dp.version");
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON dp.id = m.programID');
        $query->order('name ASC, degree ASC, version DESC');
        $dbo->setQuery($query);

        try {
            $programs = $dbo->loadAssocList();
        } catch (Exception $exc) {
            return parent::getOptions();
        }

        // Whether or not the program display should be prefiltered according to user resource access
        $access = $this->getAttribute('access', 'false') == 'true';

        // Unique will only use the most recently accredited program for a specific name and degree
        $unique      = $this->getAttribute('unique', 'false') == 'true';
        $uniqueNames = [];
        $options     = [];

        foreach ($programs as $program) {
            $index = "{$program['name']} {$program['degree']}";

            if ($unique and in_array($index, $uniqueNames)) {
                continue;
            } else {
                $uniqueNames[$index] = $index;
            }

            $text = "{$program['name']}, {$program['degree']} ({$program['version']})";

            if (!$access or THM_OrganizerHelperAccess::allowDocumentAccess('program', $program['value'])) {
                $options[] = JHtml::_('select.option', $program['value'], $text);
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}
