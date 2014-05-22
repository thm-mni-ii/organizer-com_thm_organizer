<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewRoom_Merge
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * Class provides functions for merging room entries
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewRoom_Merge extends JViewLegacy
{
    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/thm_organizer.css');

        $model = $this->getModel();

        $data = $model->roomInformation;
        $this->generateFormFields($data);

        // Set the toolbar
        $this->addToolBar();

        // Display the template
        parent::display($tpl);
    }

    /**
     * Creates input tags for the merge form.
     *
     * @param   array  &$roomEntries  the data from the database
     *
     * @return void
     */
    private function generateFormFields(&$roomEntries)
    {
        $this->ID = "<input type='hidden' name='id' value='{$roomEntries[0]['id']}' />";

        $this->IDs = array();
        $nameChecked = false;
        $this->name = array();
        $longnameChecked = false;
        $this->longname = array();
        $gpuntisIDChecked = false;
        $this->gpuntisID = array();
        $typeIDChecked = false;
        $this->typeID = array();

        foreach ($roomEntries as $entry)
        {
            if ($entry['id'] != $roomEntries[0]['id'])
            {
                $this->otherIDs[] = $entry['id'];
            }
            $this->setPropertyInput('name', $entry['name'], $nameChecked);
            $this->setPropertyInput('longname', $entry['longname'], $longnameChecked);
            $this->setPropertyInput('gpuntisID', $entry['gpuntisID'], $gpuntisIDChecked);
            if (!empty($entry['typeID']) AND !array_key_exists($entry['typeID'], $this->typeID))
            {
                $this->typeID[$entry['typeID']] = "<label for='{$entry['typeID']}'>{$entry['type']}</label>";
                $this->typeID[$entry['typeID']] .= "<input type='radio' name='typeID' value='{$entry['typeID']}' ";
                $this->typeID[$entry['typeID']] .= $typeIDChecked?  ">" : "checked>";
                $typeIDChecked = true;
            }
        }
        $this->otherIDs = "<input type='hidden' name='otherIDs' value='" . implode(',', $this->otherIDs) . "' />";
    }

    /**
     * Creates a label and radio buttion input for entry values.
     *
     * @param   string   $name      the name of the property
     * @param   string   $value     the value to be sent on form submission
     * @param   boolean  &$checked  if one of the preceding values is checked
     *
     * @return  void
     */
    private function setPropertyInput($name, $value, &$checked = false)
    {
        if ($value != '' AND !array_key_exists($value, $this->{$name}))
        {
            $this->{$name}[$value] = "<label for='$value'>$value</label>";
            $this->{$name}[$value] .= "<input type='radio' name='$name' value='$value' ";
            $this->{$name}[$value] .= $checked?  ">" : "checked>";
            $checked = true;
        }
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return  void
     */
    protected function addToolBar()
    {
        JRequest::setVar('hidemainmenu', true);
        JToolbarHelper::title(JText::_('COM_THM_ORGANIZER_RMM_MERGE_TITLE'));
        JToolbarHelper::custom('room.merge', 'merge', 'merge', 'COM_THM_ORGANIZER_MERGE', false);
        JToolbarHelper::cancel('room.cancel', 'JTOOLBAR_CANCEL');
    }
}
