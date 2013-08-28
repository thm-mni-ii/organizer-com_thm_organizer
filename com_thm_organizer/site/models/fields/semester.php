<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        JFormFieldSemester
 * @description JFormFieldSemester component site field
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

/**
 * Class JFormFieldSemester for component com_thm_organizer
 * Class provides methods to create a multiple select which includes the related semesters of the current tree node
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class JFormFieldSemester extends JFormField
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
        JHTML::script('semester.js', $scriptDir, false);

        $arrows = '<a onclick="roleup()" id="sortup"><img src="../administrator/components/com_thm_groups/img/uparrow.png" ';
        $arrows .= 'title="Rolle eine Position h&ouml;her" /></a>';
        $arrows .= '<a onclick="roledown()" id="sortdown"><img src="../administrator/components/com_thm_groups/img/downarrow.png" ';
        $arrows .= '"title="Rolle eine Position niedriger" /></a>';

        $dbo = JFactory::getDBO();

        /* get the major id */
        $menuID = JRequest::getVar('id');

        $menuQuery = $dbo->getQuery(true);
        $menuQuery->select("*");
        $menuQuery->from('#__menu');
        $menuQuery->where("id = '$menuID'");
        $dbo->setQuery($menuQuery);
        $row = $dbo->loadObject();

        $params = isset($row)? json_decode($row->params) : new stdClass;

        if (isset($params->major))
        {
            $major = $params->major;
        }
        else
        {
            $arr = array();
            return JHTML::_('select.genericlist',
                            $arr,
                            'jform[params][semesters][]',
                            'class="inputbox" size="10" multiple="multiple"',
                            'id',
                            'name',
                            $this->value
                            ) . $arrows;
        }
 
        // Build the query
        $semesterQuery = $dbo->getQuery(true);
        $semesterQuery->select("sm.semester_id AS id");
        $semesterQuery->select("name");
        $semesterQuery->from('#__thm_organizer_semesters_majors as sm');
        $semesterQuery->innerJoin('#__thm_organizer_semesters as semesters ON sm.semester_id = semesters.id');
        $semesterQuery->where("major_id = $major");
        $semesterQuery->order('name ASC');
        $dbo->setQuery($semesterQuery);
        $semesters = $dbo->loadObjectList();
        $semesters2 = $dbo->loadResultArray();

        if ($this->value)
        {
            $result = array();
            foreach ($semesters as $semester)
            {
                $orderpos = array_search($semester->id, $this->value);
                if ($orderpos !== false)
                {
                    $result[$orderpos] = $semester;
                }
            }

            $diff = array_diff($semesters2, $this->value);

            foreach ($diff as $value)
            {
                $add = array();
                $add['id'] = $value;

                foreach ($semesters as $semester)
                {
                    if ($semester->id == $value)
                    {
                        $add['name'] = $semester->name;
                    }
                }

                array_push($result, $add);
            }

            ksort($result);
        }
        else
        {
            $result = $semesters;
        }
 
        if ($result == null)
        {
            $result = array();
        }

        return JHTML::_('select.genericlist',
                        $result,
                        'jform[params][semesters][]',
                        'class="inputbox" size="10" multiple="multiple"',
                        'id',
                        'name',
                        $this->value
                       ) . $arrows;
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
        $class = !empty($this->description) ? 'hasTip' : '';
        $class = $this->required == true ? $class . ' required' : $class;

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
