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

\JFormHelper::loadFieldClass('list');
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/campuses.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/OrganizerHelper.php';

use THM_OrganizerHelperHTML as HTML;

/**
 * Class creates a form field for campus selection.
 */
class JFormFieldCampusID extends \JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'campusID';

    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return string  The field input markup.
     */
    protected function getInput()
    {
        $html = [];
        $attr = '';

        // Initialize some field attributes.
        $attr        .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
        $attr        .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
        $attr        .= $this->multiple ? ' multiple' : '';
        $attr        .= $this->required ? ' required aria-required="true"' : '';
        $attr        .= $this->autofocus ? ' autofocus' : '';
        $placeHolder = $this->getAttribute('placeholder', '');
        $attr        .= empty($placeHolder) ? '' : ' data-placeholder="' . Languages::_($placeHolder) . '"';

        // To avoid user's confusion, readonly="true" should imply disabled="true".
        if ((string)$this->readonly == '1' || (string)$this->readonly == 'true' || (string)$this->disabled == '1' || (string)$this->disabled == 'true') {
            $attr .= ' disabled="disabled"';
        }

        // Initialize JavaScript field attributes.
        $attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

        // Get the field options.
        $options = (array)$this->getOptions();

        // Create a read-only list (no name) with hidden input(s) to store the value(s).
        if ((string)$this->readonly == '1' || (string)$this->readonly == 'true') {
            $html[] = HTML::_(
                'select.genericlist',
                $options,
                '',
                trim($attr),
                'value',
                'text',
                $this->value,
                $this->id
            );

            // E.g. form field type tag sends $this->value as array
            if ($this->multiple && is_array($this->value)) {
                if (!count($this->value)) {
                    $this->value[] = '';
                }

                foreach ($this->value as $value) {
                    $value  = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
                    $html[] = '<input type="hidden" name="' . $this->name . '" value="' . $value . '"/>';
                }
            } else {
                $value  = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');
                $html[] = '<input type="hidden" name="' . $this->name . '" value="' . $value . '"/>';
            }
        } else // Create a regular list.
        {
            $html[] = HTML::_(
                'select.genericlist',
                $options,
                $this->name,
                trim($attr),
                'value',
                'text',
                $this->value,
                $this->id
            );
        }

        return implode($html);
    }

    /**
     * Returns an array of pool options
     *
     * @return array  the pool options
     */
    protected function getOptions()
    {
        $defaultOptions = HTML::getTranslatedOptions($this, $this->element);
        $campuses       = THM_OrganizerHelperCampuses::getOptions();

        if (empty($campuses)) {
            return $defaultOptions;
        }

        $options = [];

        foreach ($campuses as $campusID => $name) {
            $options[$campusID] = HTML::_('select.option', $campusID, $name);
        }

        return array_merge($defaultOptions, $options);
    }
}
