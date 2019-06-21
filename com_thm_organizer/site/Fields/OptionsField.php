<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use SimpleXMLElement;
use stdClass;

/**
 * Class creates select input.
 */
class OptionsField extends FormField
{
    use Translated;

    /**
     * The form field type.
     *
     * @var    string
     */
    protected $type = 'List';

    /**
     * Method to get the field input markup for a generic list.
     *
     * @return  string  The field input markup.
     */
    protected function getInput()
    {
        $html = array();
        $attr = '';

        // Initialize some field attributes.
        $attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
        $attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
        $attr .= $this->multiple ? ' multiple' : '';
        $attr .= $this->required ? ' required aria-required="true"' : '';
        $attr .= $this->autofocus ? ' autofocus' : '';

        // To avoid user's confusion, readonly="true" should imply disabled="true".
        if ((bool)$this->readonly == '1' || (bool)$this->disabled) {
            $attr .= ' disabled="disabled"';
        }

        // Initialize JavaScript field attributes.
        $attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

        // Get the field options.
        $options = (array)$this->getOptions();

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

        return implode($html);
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $fieldname = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
        $options   = array();

        foreach ($this->element->xpath('option') as $option) {
            $value = (string)$option['value'];
            $text  = trim((string)$option) != '' ? trim((string)$option) : $value;

            $disabled = (string)$option['disabled'];
            $disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');
            $disabled = $disabled || ($this->readonly && $value != $this->value);

            $checked = (string)$option['checked'];
            $checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

            $selected = (string)$option['selected'];
            $selected = ($selected == 'true' || $selected == 'selected' || $selected == '1');

            $tmp = array(
                'value'    => $value,
                'text'     => Languages::alt('THM_ORGANIZER_' . $text, $fieldname),
                'disable'  => $disabled,
                'class'    => (string)$option['class'],
                'selected' => ($checked || $selected),
                'checked'  => ($checked || $selected),
            );

            // Set some event handler attributes. But really, should be using unobtrusive js.
            $tmp['onclick']  = (string)$option['onclick'];
            $tmp['onchange'] = (string)$option['onchange'];

            // Add the option object to the result set.
            $options[] = (object)$tmp;
        }

        reset($options);

        return $options;
    }

    /**
     * Method to add an option to the list field.
     *
     * @param string $text       Text/Language variable of the option.
     * @param array  $attributes Array of attributes ('name' => 'value' format)
     *
     * @return  OptionsField  For chaining.
     */
    public function addOption($text, $attributes = array())
    {
        if ($text && $this->element instanceof SimpleXMLElement) {
            $child = $this->element->addChild('option', $text);

            foreach ($attributes as $name => $value) {
                $child->addAttribute($name, $value);
            }
        }

        return $this;
    }

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param string $name The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     */
    public function __get($name)
    {
        if ($name == 'options') {
            return $this->getOptions();
        }

        return parent::__get($name);
    }
}