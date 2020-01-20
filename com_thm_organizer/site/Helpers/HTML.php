<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;

/**
 * Class provides generalized functions useful for several component files.
 */
class HTML extends HTMLHelper
{
	/**
	 * Creates a dynamically translated label.
	 *
	 * @param   mixed   $view       the view this method is applied to
	 * @param   string  $inputName  the name of the form field whose label should be generated
	 *
	 * @return string the HMTL for the field label
	 */
	public static function getLabel($view, $inputName)
	{
		$title  = Languages::_($view->form->getField($inputName)->title);
		$tip    = Languages::_($view->form->getField($inputName)->description);
		$return = '<label id="jform_' . $inputName . '-lbl" for="jform_' . $inputName . '" class="hasPopover"';
		$return .= 'data-content="' . $tip . '" data-original-title="' . $title . '">' . $title . '</label>';

		return $return;
	}

	/**
	 * Creates an array of option objects from an array.
	 *
	 * @param $array
	 *
	 * @return array the HMTL for the field label
	 */
	public static function getOptions(array $array)
	{
		$options = [];
		foreach ($array as $key => $item)
		{
			if (is_object($item))
			{
				$item = (array) $item;
			}

			if (is_array($item))
			{
				if (array_key_exists('text', $item) and array_key_exists('value', $item))
				{
					$text  = $item['text'];
					$value = $item['value'];
				}
				else
				{
					$text  = reset($item);
					$value = end($item);
				}
			}
			else
			{
				$text  = (string) $item;
				$value = $key;
			}

			$options[] = HTML::_('select.option', $value, $text);
		}

		return $options;
	}

	/**
	 * Gets an array of dynamically translated default options.
	 *
	 * @param   object  $field    the field object.
	 * @param   object  $element  the field's xml signature. passed separately to get around its protected status.
	 *
	 * @return array the default options.
	 */
	public static function getTranslatedOptions($field, $element)
	{
		$options = [];

		foreach ($element->xpath('option') as $option)
		{
			$value = (string) $option['value'];
			$text  = trim((string) $option) != '' ? trim((string) $option) : $value;

			$disabled = (string) $option['disabled'];
			$disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');
			$disabled = $disabled || ($field->readonly && $value != $field->value);

			$checked = (string) $option['checked'];
			$checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

			$selected = (string) $option['selected'];
			$selected = ($selected == 'true' || $selected == 'selected' || $selected == '1');

			$tmp = [
				'value'    => $value,
				'text'     => Languages::_($text),
				'disable'  => $disabled,
				'class'    => (string) $option['class'],
				'selected' => ($checked || $selected),
				'checked'  => ($checked || $selected),
			];

			$options[] = $tmp;
		}

		return $options;
	}

	/**
	 * Translates an associative array of attributes into a string suitable for use in HTML.
	 *
	 * @param   array  $array  the element attributes
	 *
	 * @return string the HTML string containing the attributes
	 */
	public static function implodeAttributes(array $array)
	{
		$attributes = [];
		foreach ($array as $key => $value)
		{
			$attributes[] = "$key=\"$value\"";
		}

		return implode(' ', $attributes);
	}

	/**
	 * Creates a select box
	 *
	 * @param   mixed   $options     a set of keys and values
	 * @param   string  $name        the name of the element
	 * @param   mixed   $attributes  optional attributes: object, array, or string in the form key => value(,)+
	 * @param   mixed   $selected    optional selected items
	 * @param   bool    $jform       whether or not the element will be wrapped by a 'jform' element
	 *
	 * @return string  the html output for the select box
	 */
	public static function selectBox($options, $name, array $attributes = [], $selected = null, $jform = false)
	{
		$isMultiple = (!empty($attributes['multiple']) and $attributes['multiple'] == 'multiple');
		$multiple   = $isMultiple ? '[]' : '';

		$name = $jform ? "jform[$name]$multiple" : "$name$multiple";

		return self::_('select.genericlist', $options, $name, $attributes, 'value', 'text', $selected);
	}

	/**
	 * Sets the title for the view and the document.
	 *
	 * @param   string  $default       the default value if a specific resource could not be resolved.
	 * @param   string  $resourceName  the name of the specific resource
	 * @param   string  $icon          The hyphen-separated names of the icon class
	 *
	 * @return  void
	 */
	public static function setMenuTitle($default, $resourceName = '', $icon = '')
	{
		$app    = OrganizerHelper::getApplication();
		$params = Input::getParams();

		if ($params->get('show_page_heading') and $params->get('page_title'))
		{
			$title = $params->get('page_title');
		}
		else
		{
			$title = empty($resourceName) ? Languages::_($default) : $resourceName;
		}

		$icon = $icon ? "<span class=\"icon-$icon\"></span>" : '';

		$html = "<h1 class=\"page-title\">$icon$title</h1>";

		$app->JComponentTitle = $html;
		Factory::getDocument()->setTitle(strip_tags($title) . ' - ' . $app->get('sitename'));
	}

	public static function setPreferencesButton()
	{
		$uri    = (string) Uri::getInstance();
		$return = urlencode(base64_encode($uri));
		$link   = "index.php?option=com_config&view=component&component=com_thm_organizer&return=$return";

		$toolbar = Toolbar::getInstance('toolbar');
		$toolbar->appendButton('Link', 'options', Languages::_('ORGANIZER_SETTINGS'), $link);
	}

	/**
	 * Sets the title for the view and the document.
	 *
	 * @param   string  $title  The title.
	 * @param   string  $icon   The hyphen-separated names of the icon class
	 *
	 * @return  void
	 */
	public static function setTitle($title, $icon = 'generic.png')
	{
		$app                  = OrganizerHelper::getApplication();
		$layout               = new FileLayout('joomla.toolbar.title');
		$html                 = $layout->render(array('title' => $title, 'icon' => $icon));
		$app->JComponentTitle = $html;
		Factory::getDocument()->setTitle(strip_tags($title) . ' - ' . $app->get('sitename'));
	}

	/**
	 * Provides a simplified interface for sortable headers
	 *
	 * @param   string  $constant   the unique portion of the text constant
	 * @param   string  $column     the column name when sorting by this column
	 * @param   string  $direction  the direction in which to sort
	 * @param   string  $ordering   the column name of the column currently being used for sorting
	 *
	 * @return mixed
	 */
	public static function sort($constant, $column, $direction, $ordering)
	{
		$text = Languages::_("ORGANIZER_$constant");

		return self::_('searchtools.sort', $text, $column, $direction, $ordering);
	}
}
