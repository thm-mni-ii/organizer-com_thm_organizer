<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        JFormFieldScheduler
 * @description Custom form field of com_thm_groups
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.helper');

// Import the list field type
JFormHelper::loadFieldClass('list');

/**
 * Class JFormFieldScheduler for component com_thm_organizer
 *
 * Class provides methods to display a custom form field
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class JFormFieldScheduler extends JFormField
{
    /**
     * The form field type
     *
     * @var    String
     */
    protected $type = 'Scheduler';

    /**
     * Method to get the form field input
     *
     * @return  object  Custom form field
     */
    protected function getInput()
    {

        $libraryInstalled = jimport('thm_core.js.extjs.extjs');
        if (!$libraryInstalled)
        {
            echo "<div style='color:red;'>" . JText::_('COM_THM_ORGANIZER_EXTJS4_LIBRARY_NOT_INSTALLED') . "</div>";
            return;
        }

        $this->prepareDocument();

        $menuID = JFactory::getApplication()->input->getInt("id", 0);
        $params = $this->getParams($menuID);
?>
<script type="text/javascript" charset="utf-8">
    var prefix = '<?php echo JURI::root(true); ?>',
        menuID = <?php echo $menuID ?>,
        treeIDs = <?php echo $params->id; ?>,
        publicDefaultID = <?php echo $params->publicDefaultID; ?>,
        externLinks = [], images = [];

    externLinks.ajaxHandler = '<?php echo JURI::root() . 'index.php?option=com_thm_organizer&view=ajaxhandler&format=raw'; ?>';
    images.base = prefix + '/components/com_thm_organizer/models/fields/images/';
    images.unchecked = prefix + '/components/com_thm_organizer/models/fields/images/unchecked.png';
    images.unchecked_highlighted = prefix + '/components/com_thm_organizer/models/fields/images/unchecked_highlighted.png';
    images.checked = prefix + '/components/com_thm_organizer/models/fields/images/checked.png';
    images.checked_highlighted = prefix + '/components/com_thm_organizer/models/fields/images/checked_highlighted.png';
    images.intermediate = prefix + '/components/com_thm_organizer/models/fields/images/intermediate.png';
    images.intermediate_highlighted = prefix + '/components/com_thm_organizer/models/fields/images/intermediate_highlighted.png';
    images.selected = prefix + '/components/com_thm_organizer/models/fields/images/selected.png';
    images.selected_highlighted = prefix + '/components/com_thm_organizer/models/fields/images/selected_highlighted.png';
    images.hidden = prefix + '/components/com_thm_organizer/models/fields/images/hidden.png';
    images.hidden_highlighted = prefix + '/components/com_thm_organizer/models/fields/images/hideen_highlighted.png';
    images.notdefault =  prefix + '/components/com_thm_organizer/models/fields/images/notdefault.png';
    images.default = prefix + '/components/com_thm_organizer/models/fields/images/default.png';

    Joomla.submitbutton = function(task, type)
    {

        if (task == "item.apply" || task == "item.save" || task == "item.save2new" || task == "item.save2copy")
        {
            var dbElement = Ext.get('jform_params_id'),
                pdElement = Ext.get('jform_params_publicDefaultID');
            var tst = tree.getChecked();
            //console.log(document.forms.adminForm.elements.jform[params][displayRoomSchedule]);

            /*$('#jform_params_displayRoomSchedule input').each(function() {
                console.log($( this ));
            });*/

            var displayOptions = [];
            var doEL = document.getElementsByName("jform[params][displayRoomSchedule]");
            //console.log(doEL);
            for(var i = 0; i < doEL.length; i++){
                if (doEL[i].checked === true && doEL[i].value == 0){
                    displayOptions.push('room');
                }
            }
            doEL = document.getElementsByName("jform[params][displayTeacherSchedule]");
            //console.log(doEL);
            for(var i = 0; i < doEL.length; i++){
                if (doEL[i].checked === true && doEL[i].value == 0){
                    displayOptions.push('teacher');
                }
            }
            //console.log(selectBoxes.getSelectedValues());
            var vals = selectBoxes.getSelectedValues();//.push(displayOptions);
            dbElement.dom.value = Ext.encode(vals);
            pdElement.dom.value = Ext.encode(tree.getPublicDefault());
            console.log(dbElement);
        }

        //console.log(pdElement);
        //return false;
        if (task == 'item.setType' || task == 'item.setMenuType')
        {
            if (task == 'item.setType')
            {
                document.id('item-form').elements['jform[type]'].value = type;
                document.id('fieldtype').value = 'type';
            }
            else
            {
                document.id('item-form').elements['jform[menutype]'].value = type;
            }
            Joomla.submitform('item.setType', document.id('item-form'));
        }
        else if (task == 'item.cancel' || document.formvalidator.isValid(document.id('item-form')))
        {
            Joomla.submitform(task, document.id('item-form'));
        }
        else
        {
            // special case for modal popups validation response
            $$('#item-form .modal-value.invalid').each(function(field){
                var idReversed = field.id.split("").reverse().join("");
                var separatorLocation = idReversed.indexOf('_');
                var name = idReversed.substr(separatorLocation).split("").reverse().join("")+'name';
                document.id(name).addClass('invalid');
            });
        }

    }

</script>

        <style type="text/css">
        .x-tree-node-cb { float: none; }
        .MySched_scheduler_selection_icons { margin: 0; }
        </style>
        <div style="width: auto; height: auto;" id="tree-div"></div>
<?php
    }

    /**
     * Loads required files into the document
     *
     * @return  void
     */
    private function prepareDocument()
    {
        $doc = JFactory::getDocument();
        $root = JURI::root(true);
        $doc->addStyleSheet("$root/libraries/thm_core/js/extjs/css/ext-theme-gray-all.css");
        $doc->addStyleSheet("$root/components/com_thm_organizer/models/fields/css/schedule_selection_tree.css");
        $doc->addScript("$root/components/com_thm_organizer/models/fields/tree.js");
        $doc->addScript("$root/components/com_thm_organizer/models/fields/selectBoxes.js");
        $doc->addScript("$root/components/com_thm_organizer/models/fields/departmentSemesterSelection.js");
    }

    /**
     * Retrieves the saved menu parameters creating default values if none exist
     *
     * @param   int  $menuID  the id of the menu entry
     *
     * @return  object  an object with the menu parameters
     *
     * @throws  exception
     */
    private function getParams($menuID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('params');
        $query->from($dbo->quoteName('#__menu'));
        $query->where("id = '$menuID'");
        $dbo->setQuery((string) $query);
        try
        {
            $rawParams = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        if (!empty($rawParams))
        {
            $params = json_decode($rawParams);
        }
        else
        {
            $params = new stdClass;
        }

        $defaultValues = empty($params->publicDefaultID)? '{}' : $params->publicDefaultID;
        $params->publicDefaultID = json_encode($defaultValues);

        if (empty($params->id))
        {
            $params->id = json_encode(new stdClass);
        }

        return $params;
    }
}
