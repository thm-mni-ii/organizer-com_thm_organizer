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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/component.php';

use THM_OrganizerHelperHTML as HTML;
use THM_OrganizerHelperLanguages as Languages;

/**
 * Class creates a select box for predefined colors.
 */
class JFormFieldColorID extends \Joomla\CMS\Form\FormField
{
    /**
     * Type
     *
     * @var    String
     */
    protected $type = 'colorID';

    /**
     * Returns a select box which contains the colors
     *
     * @return string  the HTML for the color select box
     */
    public function getInput()
    {
        $input   = THM_OrganizerHelperComponent::getInput();
        $fieldID = $input->getInt('id');
        if (empty($fieldID)) {
            $selectedFields = THM_OrganizerHelperComponent::getInput()->get('cid', [], 'array');
        } else {
            $selectedFields = [$fieldID];
        }

        $dbo   = \JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT c.id, c.name_de, c.name_en, c.color, f.id AS fieldID");
        $query->from(' #__thm_organizer_colors as c');

        $merge = count($selectedFields) > 1;
        if ($merge) {
            $query->innerJoin('#__thm_organizer_fields AS f ON f.colorID = c.id');
            $idString = "'" . implode("', '", $selectedFields) . "'";
            $query->where("f.id IN ( $idString )");
            $query->group('c.id');
        } else {
            $query->leftJoin('#__thm_organizer_fields AS f ON f.colorID = c.id');
            $query->group('c.id');
        }

        $dbo->setQuery($query);

        $colors = THM_OrganizerHelperComponent::executeQuery('loadAssocList');
        if (empty($colors)) {
            return '';
        }

        $shortTag    = Languages::getShortTag();
        $property    = "name_$shortTag";
        $hasSelected = false;
        $options     = [];
        foreach ($colors as $color) {

            if (!empty($color['fieldID'])) {
                if ($hasSelected) {
                    $selected = '';
                } else {
                    $selected    = 'selected="selected"';
                    $hasSelected = true;
                }
            }
            $backgroundColor = $color['color'];
            $textColor       = HTML::textColor($backgroundColor);
            $style           = 'style="background-color: ' . $backgroundColor . '; color:' . $textColor . ';"';
            $value           = 'value="' . $color['id'] . '"';

            $options[] = "<option $style $selected $value >" . $color[$property] . "</option>";
        }

        if (!count($options) or !$merge) {
            $selectNone = $hasSelected ? '' : 'selected="selected"';
            $none       = '<option ' . $selectNone . ' value="">' . \JText::_('JNONE') . '</option>';
            $options    = array_merge([$none], $options);
        }

        $select = "<select id = 'jform_colorID' name='jform[colorID]'>OPTIONS</select>";

        return str_replace('OPTIONS', implode('', $options), $select);
    }
}
