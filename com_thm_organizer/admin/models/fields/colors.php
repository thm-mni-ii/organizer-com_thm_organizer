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

    /**
     * Method to get the field label
     *
     * @return  String The field label
     */
    public function getLabel()
    {
        $replace = '';

        // Get the label text from the XML element, defaulting to the element name.
        $text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];

        // Build the class for the label.
        $class = '';
        $class .= !empty($this->description) ? 'hasTip' : '';
        $class .= $this->required == true ? ' required' : '';

        // Add the opening label tag and main attributes attributes.
        $label = '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $class . '"';

        // If a description is specified, use it to build a tooltip.
        if (!empty($this->description))
        {
            $title = trim(JText::_($text), ':') . '::' . JText::_($this->description);
            $label .= ' title="' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '"';
        }

        // Add the label text and closing tag.
        $label .= '>' . $replace . JText::_($text) . '</label>';

        return $label;
    }
}
