<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

require_once 'access.php';
require_once 'languages.php';

use OrganizerHelper;
use THM_OrganizerHelperLanguages as Languages;

/**
 * Class provides generalized functions useful for several component files.
 */
class THM_OrganizerHelperHTML extends \Joomla\CMS\HTML\HTMLHelper
{
    /**
     * Gets a div with a given background color and text with a dynamically calculated text color
     *
     * @param string $text    the text to be displayed
     * @param string $bgColor hexadecimal color code
     *
     * @return string  the html output string
     */
    public static function colorField($text, $bgColor)
    {
        $textColor = self::textColor($bgColor);
        $style     = 'color: ' . $textColor . '; background-color: ' . $bgColor . '; text-align:center';

        return '<div class="color-preview" style="' . $style . '">' . $text . '</div>';
    }

    /**
     * Gets an array of dynamically translated default options.
     *
     * @param object $field   the field object.
     * @param object $element the field's xml signature. passed separately to get around its protected status.
     *
     * @return array the default options.
     */
    public static function getTranslatedOptions($field, $element)
    {
        $lang    = Languages::getLanguage();
        $options = [];

        foreach ($element->xpath('option') as $option) {

            $value = (string)$option['value'];
            $text  = trim((string)$option) != '' ? trim((string)$option) : $value;

            $disabled = (string)$option['disabled'];
            $disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');
            $disabled = $disabled || ($field->readonly && $value != $field->value);

            $checked = (string)$option['checked'];
            $checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

            $selected = (string)$option['selected'];
            $selected = ($selected == 'true' || $selected == 'selected' || $selected == '1');

            $tmp = [
                'value'    => $value,
                'text'     => $lang->_($text),
                'disable'  => $disabled,
                'class'    => (string)$option['class'],
                'selected' => ($checked || $selected),
                'checked'  => ($checked || $selected),
            ];

            $options[] = $tmp;
        }

        return $options;
    }

    /**
     * Creates a select box
     *
     * @param mixed  $entries    a set of keys and values
     * @param string $name       the name of the element
     * @param mixed  $attributes optional attributes: object, array, or string in the form key => value(,)+
     * @param mixed  $selected   optional selected items
     * @param bool   $jform      whether or not the element will be wrapped by a 'jform' element
     *
     * @return string  the html output for the select box
     */
    public static function selectBox($entries, $name, $attributes = null, $selected = null, $jform = false)
    {
        $options = [];

        $entriesValid = (is_array($entries) or is_object($entries));
        if ($entriesValid) {
            foreach ($entries as $key => $value) {
                $textValid = (is_string($value) or is_numeric($value));
                if (!$textValid) {
                    continue;
                }

                $options[] = self::_('select.option', $key, $value);
            }
        }

        $attribsInvalid = (empty($attributes)
            or (!is_object($attributes) and !is_array($attributes) and !is_string($attributes)));
        if ($attribsInvalid) {
            $attributes = [];
        } elseif (is_object($attributes)) {
            $attributes = (array)$attributes;
        } elseif (is_string($attributes)) {
            $validString = preg_match("/^((\'[\w]+\'|\"[\w]+\") => (\'[\w]+\'|\"[\w]+\")[,]?)+$/", $attributes);
            if ($validString) {
                $singleAttribs = explode(',', $attributes);
                $attributes    = [];
                array_walk($singleAttribs, 'parseAttribute', $attributes);

                /**
                 * Parses the attribute array entries into text/value pairs for use as options
                 *
                 * @param string $attribute  the attribute being iterated
                 * @param int    $key        the array key from the array being iterated (unused)
                 * @param array  $attributes the array where parsed attributes are stored
                 *
                 * @return void modifies the $attributes array
                 *
                 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
                 */
                function parseAttribute($attribute, $key, &$attributes)
                {
                    list($property, $value) = explode(' => ', $attribute);
                    $attributes[$property] = $value;
                }
            } else {
                $attributes = [];
            }
        }

        if (empty($attributes['class'])) {
            $attributes['class'] = 'organizer-select-box';
        } elseif (strpos('organizer-select-box', $attributes['class']) === false) {
            $attributes['class'] .= ' organizer-select-box';
        }

        $isMultiple = (!empty($attributes['multiple']) and $attributes['multiple'] == 'multiple');
        $multiple   = $isMultiple ? '[]' : '';

        $name = $jform ? "jform[$name]$multiple" : "$name$multiple";

        return self::_('select.genericlist', $options, $name, $attributes, 'value', 'text', $selected);
    }

    /**
     * Provides a simplified interface for sortable headers
     *
     * @param string $constant  the unique portion of the text constant
     * @param string $column    the column name when sorting by this column
     * @param string $direction the direction in which to sort
     * @param string $ordering  the column name of the column currently being used for sorting
     *
     * @return mixed
     */
    public static function sort($constant, $column, $direction, $ordering)
    {
        $constant = "THM_ORGANIZER_$constant";

        return self::_('searchtools.sort', $constant, $column, $direction, $ordering);
    }

    /**
     * Gets an appropriate value for contrasting text color.
     *
     * @param string $bgColor the background color with which do
     *
     * @return string  the hexadecimal value for an appropriate text color
     */
    public static function textColor($bgColor)
    {
        $color              = substr($bgColor, 1);
        $params             = OrganizerHelper::getParams();
        $red                = hexdec(substr($color, 0, 2));
        $green              = hexdec(substr($color, 2, 2));
        $blue               = hexdec(substr($color, 4, 2));
        $relativeBrightness = ($red * 299) + ($green * 587) + ($blue * 114);
        $brightness         = $relativeBrightness / 1000;
        if ($brightness >= 128) {
            return $params->get('darkTextColor', '#4a5c66');
        } else {
            return $params->get('lightTextColor', '#ffffff');
        }
    }

    /**
     * Creates a dynamically translated label.
     *
     * @param mixed  $view      the view this method is applied to
     * @param string $inputName the name of the form field whose label should be generated
     *
     * @return string the HMTL for the field label
     */
    public static function getLabel($view, $inputName)
    {
        $title  = $view->lang->_($view->form->getField($inputName)->title);
        $tip    = $view->lang->_($view->form->getField($inputName)->description);
        $return = '<label id="jform_' . $inputName . '-lbl" for="jform_' . $inputName . '" class="hasPopover"';
        $return .= 'data-content="' . $tip . '" data-original-title="' . $title . '">' . $title . '</label>';

        return $return;
    }
}
