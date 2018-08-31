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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class creates a select box for predefined colors.
 */
class JFormFieldColorID extends JFormField
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
     * @throws Exception
     */
    public function getInput()
    {
        $input   = JFactory::getApplication()->input;
        $fieldID = $input->getInt('id');
        if (empty($fieldID)) {
            $selectedFields = JFactory::getApplication()->input->get('cid', [], 'array');
        } else {
            $selectedFields = [$fieldID];
        }

        $dbo   = JFactory::getDbo();
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

        try {
            $colors = $dbo->loadAssocList();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return '';
        }

        $shortTag    = THM_OrganizerHelperLanguage::getShortTag();
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
            $textColor       = THM_OrganizerHelperComponent::getTextColor($backgroundColor);
            $style           = 'style="background-color: ' . $backgroundColor . '; color:' . $textColor . ';"';
            $value           = 'value="' . $color['id'] . '"';

            $options[] = "<option $style $selected $value >" . $color[$property] . "</option>";
        }

        if (!count($options) or !$merge) {
            $selectNone = $hasSelected ? '' : 'selected="selected"';
            $none       = '<option ' . $selectNone . ' value="">' . JText::_('JNONE') . '</option>';
            $options = array_merge([$none], $options);
        }

        $select = "<select id = 'jform_colorID' name='jform[colorID]'>OPTIONS</select>";
        return str_replace('OPTIONS', implode('', $options), $select);
    }
}
