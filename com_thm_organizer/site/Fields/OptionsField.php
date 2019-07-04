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

use Joomla\CMS\Form\FormField;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
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
        $fieldName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
        $options   = array();

        foreach ($this->element->xpath('option') as $optionTag) {

            $option        = new stdClass;
            $option->value = (string)$optionTag['value'];

            $text         = trim((string)$optionTag) != '' ? trim((string)$optionTag) : $option->value;
            $option->text = Languages::alt('THM_ORGANIZER_' . $text, $fieldName);

            $option->class = (string)$optionTag['class'];

            $disabled        = (string)$optionTag['disabled'];
            $disabled        = ($disabled == 'true' or $disabled == 'disabled' or $disabled == '1');
            $option->disable = ($disabled or ($this->readonly && $option->value != $this->value));

            $checked = (string)$optionTag['checked'];
            $checked = ($checked == 'true' or $checked == 'checked' or $checked == '1');

            $selected = (string)$optionTag['selected'];
            $selected = ($selected == 'true' or $selected == 'selected' or $selected == '1');

            $option->selected = ($checked or $selected);
            $option->checked  = ($checked or $selected);

            // Set some event handler attributes. But really, should be using unobtrusive js.
            $option->onclick  = (string)$optionTag['onclick'];
            $option->onchange = (string)$optionTag['onchange'];

            // Add the option object to the result set.
            $options[] = $option;
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