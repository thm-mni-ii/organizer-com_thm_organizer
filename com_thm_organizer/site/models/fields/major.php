<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        JFormFieldMajor
 * @description JFormFieldMajor component site field
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

/**
 * Class JFormFieldMajor for component com_thm_organizer
 * Class provides methods to create a multiple select includes the related semesters of the current tree node
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class JFormFieldMajor extends JFormField
{
    /**
     * Type
     *
     * @var    String
     */
    protected $type = 'Semester';

    /**
     * Returns a multiple select which includes the related semesters of the current tree node
     *
     * @return Multiple select box
     */
    public function getInput()
    {
        $scriptDir = str_replace(JPATH_SITE . DS, '', "components/com_thm_organizer/models/fields/");

        // Add script-code to the document head
        JHTML::script('major.js', $scriptDir, false);

        $dbo = JFactory::getDBO();

        // Build the query
        $query = $dbo->getQuery(true);
        $query->select("majors.id AS id");
        $query->select("CONCAT(degrees.name, ' ', majors.subject, ' (', majors.po, ')') AS name");
        $query->from('#__thm_organizer_majors AS majors');
        $query->innerJoin(' #__thm_organizer_degrees AS degrees ON degrees.id = majors.degree_id');
        $query->order('name ASC');
        $dbo->setQuery($query);
        $semesters = $dbo->loadObjectList();

        // Adds an additional item to the select box
        $blankItem = new stdClass;
        $blankItem->id = 0;
        $blankItem->name = '-- None --';
        $items = array_merge(array($blankItem), $semesters);

        $javaScript = "onchange='loadSemesters(value);' ";

        return JHTML::_('select.genericlist', $items, 'jform[params][major]', $javaScript, 'id', 'name', $this->value);
    }

    /**
     * Returns the related semesters of the given tree node
     *
     * @param   Integer  $nodeID  Id
     *
     * @return Array The selected Semesters
     */
    private function getSelectedSemesters($nodeID)
    {
        // Determine all semester mappings of this tree node
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("*");
        $query->from('#__thm_organizer_assets_semesters');
        $query->where("assets_tree_id = $nodeID");
        $dbo->setQuery($query);
        $rows = $dbo->loadObjectList();

        $selectedSemesters = array();

        if (isset($rows))
        {
            foreach ($rows as $row)
            {
                array_push($selectedSemesters, $row->semesters_majors_id);
            }
        }

        return $selectedSemesters;
    }

    /**
     * Method to get the field label
     *
     * @return <String> The field label
     */
    public function getLabel()
    {
        // Initialize variables.
        $label = '';
        $replace = '';

        // Get the label text from the XML element, defaulting to the element name.
        $text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];

        // Build the class for the label.
        $class = '';
        $class .= !empty($this->description)? 'hasTip' : '';
        $class .= $this->required == true ? ' required' : '';

        // Add the opening label tag and main attributes attributes.
        $label .= '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="' . $class . '"';

        // If a description is specified, use it to build a tooltip.
        if (!empty($this->description))
        {
            $label .= ' title="' . htmlspecialchars(trim(JText::_($text), ':') . '::' . JText::_($this->description), ENT_COMPAT, 'UTF-8') . '"';
        }

        // Add the label text and closing tag.
        $label .= '>' . $replace . JText::_($text) . '</label>';

        return $label;
    }
}
