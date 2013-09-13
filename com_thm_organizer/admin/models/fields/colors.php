<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldColor
 * @description JFormFieldColor component admin field
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

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
        $colors = $dbo->loadObjectList();

        $html = "<select id = 'colorID' name='jform[colorID]'>";

        $html .= "<option selected='selected' style='' value=''>";
        $html .= JText::_('COM_THM_ORGANIZER_NONE');
        $html .= "</option>";

        foreach ($colors as $color)
        {
            $html .= "<option style='background-color:#$color->color' value='$color->id' ";
            if ($this->value == $color->id)
            {
                $html .= "selected='selected'";
            }
            $html .= " >$color->name</option>";
        }
        $html .= "</select>";
        return $html;
    }
}
