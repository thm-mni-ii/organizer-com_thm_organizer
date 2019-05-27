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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a select box for predefined colors.
 */
class ColorsField extends BaseField
{
    /**
     * Type
     *
     * @var    String
     */
    protected $type = 'Colors';

    /**
     * Returns a select box which contains the colors
     *
     * @return string  the HTML for the color select box
     */
    public function getInput()
    {
        $selectedFields = OrganizerHelper::getSelectedIDs();

        $dbo   = Factory::getDbo();
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

        $colors = OrganizerHelper::executeQuery('loadAssocList');
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
            $none       = '<option ' . $selectNone . ' value="">' . Languages::_('JNONE') . '</option>';
            $options    = array_merge([$none], $options);
        }

        $select = "<select id = 'jform_colorID' name='jform[colorID]'>OPTIONS</select>";

        return str_replace('OPTIONS', implode('', $options), $select);
    }
}
