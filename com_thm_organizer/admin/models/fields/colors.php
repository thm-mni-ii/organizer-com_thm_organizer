<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldColor
 * @description JFormFieldColor component admin field
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componenthelper.php';

/**
 * Class JFormFieldColor for component com_thm_organizer
 *
 * Class provides methods to create a form field that contains the colors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
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
     * @return Select box
     */
    public function getInput()
    {
        $dbo = JFactory::getDBO();

        // Select all assets from the database
        $query = $dbo->getQuery(true);

        $query->select("*");
        $query->from(' #__thm_organizer_colors as colors');
        $dbo->setQuery($query);
        
        try
        {
            $colors = $dbo->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        $html = "<select id = 'colorID' name='jform[colorID]'>";
        $html .= '<option selected="selected" value="">' . JText::_('JNONE') . '</option>';

        foreach ($colors as $color)
        {
            $selected = $this->value == $color->id? "selected='selected'" : '';
            $textColor = THM_OrganizerHelperComponent::getTextColor($color->color);
            $style = 'style="background-color:#' . $color->color . '; color:#' . $textColor . ';"';
            $value = 'value="' . $color->id . '"';
            $html .= "<option $style $selected $value >$color->name</option>";
        }
        $html .= "</select>";
        return $html;
    }
}
