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

use Joomla\Utilities\ArrayHelper;
use ReflectionException;

defined('_JEXEC') or die;

\JFormHelper::loadFieldClass('list');

/**
 * Class creates a select box for predefined colors.
 */
class ColorsField extends \JFormFieldList
{
    private $context;

    private $selectedFields;

    private $selectedID;

    protected $type = 'Colors';

    /**
     * Returns a select box which contains the colors
     *
     * @return string  the HTML for the color select box
     */
    public function getInput()
    {
        $input = parent::getInput();
        $this->setContext();
        $options = $this->getColoredOptions();

        return str_replace('</select>', implode('', $options) . '</select>', $input);
    }

    /**
     * Retrieves a list of colors associated with fields.
     *
     * @return mixed
     * @throws ReflectionException
     */
    private function getColors()
    {
        $shortTag = \Languages::getShortTag();
        $dbo      = \Factory::getDbo();
        $query    = $dbo->getQuery(true);
        $query->select("DISTINCT c.id, c.name_$shortTag AS name, c.color");
        $query->from(' #__thm_organizer_colors as c');
        $query->group('c.id');

        switch ($this->context) {
            case 'filter':
                $query->innerJoin('#__thm_organizer_fields AS f ON f.colorID = c.id');
                break;
            case 'merge':
                $query->innerJoin('#__thm_organizer_fields AS f ON f.colorID = c.id');
                $query->where("f.id IN ('" . implode("', '", $this->selectedFields) . "')");
                break;
        }

        $dbo->setQuery($query);

        return \OrganizerHelper::executeQuery('loadAssocList');
    }

    /**
     * Creates an option for an individual color
     *
     * @param array $color the color information
     * @param bool  $found whether or not the selected item has already been found
     *
     * @return string
     */
    private function getColoredOption($color, $found)
    {
        switch ($this->context) {
            case 'edit':
                $selected = $color['id'] == $this->selectedID ? 'selected="selected"' : '';
                break;
            case 'filter':
                $selected = $color['id'] == $this->selectedID ? 'selected="selected"' : '';
                break;
            case 'merge':
                $selected = $found ? '' : 'selected="selected"';
                break;
        }

        $style = 'style="background-color: ' . $color['color'] . '; color:' . \HTML::textColor($color['color']) . ';" ';

        return '<option ' . $style . $selected . ' value="' . $color['id'] . '" >' . $color['name'] . "</option>";
    }

    /**
     * Makes the options for the select box
     *
     * @return array the option strings
     * @throws ReflectionException
     */
    private function getColoredOptions()
    {
        $found = false;
        foreach ($this->getColors() as $color) {
            $options[] = $this->getColoredOption($color, $found);
        }

        return $options;
    }

    /**
     * Sets context information about where the field is being used.
     *
     * @return void
     */
    private function setContext()
    {
        $input = \OrganizerHelper::getInput();

        $fieldID = $input->getInt('id');
        if (!empty($fieldID)) {
            $this->context    = 'edit';
            $colorID          = \Organizer\Helpers\Fields::getColorID($fieldID);
            $this->selectedID = empty($colorID) ? '' : $colorID;

            return;
        }

        $fieldIDs = $input->get('cid', [], 'array');
        if (!empty($fieldIDs)) {
            $this->context        = 'merge';
            $this->selectedFields = ArrayHelper::toInteger($fieldIDs);

            return;
        }

        $this->context = 'filter';
        $filter        = $input->get('filter', [], 'array');
        if (empty($filter['colorID'])) {
            $list             = $input->get('list', [], 'array');
            $this->selectedID = empty($list['colorID']) ? '' : $list['colorID'];
        } else {
            $this->selectedID = $filter['colorID'];
        }
    }
}
