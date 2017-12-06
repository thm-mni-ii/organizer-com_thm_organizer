<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldCampusID
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
JFormHelper::loadFieldClass('list');

/**
 * Class creates a form field for subject-degree program association
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldCampusID extends JFormFieldList
{
	/**
	 * @var  string
	 */
	protected $type = 'campusID';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
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
		$attr        .= empty($placeHolder) ? '' : ' data-placeholder="' . JText::_($placeHolder) . '"';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1' || (string) $this->disabled == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		// Initialize JavaScript field attributes.
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with hidden input(s) to store the value(s).
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true')
		{
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);

			// E.g. form field type tag sends $this->value as array
			if ($this->multiple && is_array($this->value))
			{
				if (!count($this->value))
				{
					$this->value[] = '';
				}

				foreach ($this->value as $value)
				{
					$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"/>';
				}
			}
			else
			{
				$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
			}
		}
		else
			// Create a regular list.
		{
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}

		return implode($html);
	}

	/**
	 * Returns an array of pool options
	 *
	 * @return  array  the pool options
	 */
	public function getOptions()
	{
		$shortTag = THM_OrganizerHelperLanguage::getShortTag();
		$dbo      = JFactory::getDbo();
		$query    = $dbo->getQuery(true);

		$select = "c1.id as value, c1.name_$shortTag as name, c2.name_$shortTag as parentName";
		$query->select($select);
		$query->from('#__thm_organizer_campuses as c1');
		$query->leftJoin('#__thm_organizer_campuses as c2 on c1.parentID = c2.id');
		$dbo->setQuery($query);

		try
		{
			$campuses = $dbo->loadAssocList();
		}
		catch (Exception $exc)
		{
			return parent::getOptions();
		}

		$options = [];
		foreach ($campuses as $campus)
		{
			if (empty($campus['parentName']))
			{
				$index = $campus['name'];
				$name  = $campus['name'];
			}
			else
			{
				$index = "{$campus['parentName']}-{$campus['name']}";
				$name  = "|&nbsp;&nbsp;-&nbsp;{$campus['name']}";
			}

			$options[$index] = JHtml::_('select.option', $campus['value'], $name);
		}

		ksort($options);

		return array_merge(parent::getOptions(), $options);
	}
}
