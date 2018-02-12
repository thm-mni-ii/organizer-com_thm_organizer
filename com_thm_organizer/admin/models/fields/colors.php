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
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class creates a select box for predefined colors.
 */
class JFormFieldColors extends JFormField
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
     * @throws Exception
     */
    public function getInput()
    {
        $dbo = JFactory::getDbo();

        // Select all assets from the database
        $query = $dbo->getQuery(true);

        $query->select("*");
        $query->from(' #__thm_organizer_colors as colors');
        $dbo->setQuery($query);

        try {
            $colors = $dbo->loadObjectList();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

            return '';
        }

        $html = "<select id = 'jform_colorID' name='jform[colorID]'>";
        $html .= '<option selected="selected" value="">' . JText::_('JNONE') . '</option>';

        $shortTag = THM_OrganizerHelperLanguage::getShortTag();
        $property = "name_$shortTag";
        foreach ($colors as $color) {
            $selected  = $this->value == $color->id ? "selected='selected'" : '';
            $textColor = THM_OrganizerHelperComponent::getTextColor($color->color);
            $style     = 'style="background-color: ' . $color->color . '; color:' . $textColor . ';"';
            $value     = 'value="' . $color->id . '"';
            $html      .= "<option $style $selected $value >" . $color->$property . "</option>";
        }

        $html .= "</select>";

        return $html;
    }
}
