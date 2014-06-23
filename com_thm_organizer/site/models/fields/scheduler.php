<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        JFormFieldScheduler
 * @description Custom form field of com_thm_groups
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
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
        $libraryInstalled = jimport('extjs4.extjs4');
        if (!$libraryInstalled)
        {
            echo "<div style='color:red;'>" . JText::_('COM_THM_ORGANIZER_EXTJS4_LIBRARY_NOT_INSTALLED') . "</div>";
        }

        $menuid = JRequest::getInt("id", 0);

        // Get database
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('params');
        $query->from($dbo->quoteName('#__menu'));
        $query->where('id = ' . $menuid);
        $dbo->setQuery($query);
        try 
        {
            $rows = $dbo->loadObjectList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        if (count($rows) > 0)
        {
            $jsonObj = json_decode($rows[0]->params);
        }

        if (isset($jsonObj->publicDefaultID))
        {
            $publicDefaultID = $jsonObj->publicDefaultID;
        }
        else
        {
            $publicDefaultID = "";
        }

        if (isset($jsonObj->id))
        {
            $idString = $jsonObj->id;
        }
        else
        {
            $idString = "";
        }

        $doc = JFactory::getDocument();

        $doc->addStyleSheet(JURI::root(true) . '/libraries/extjs4/css/ext-all-gray.css');
        $doc->addStyleSheet(JURI::root(true) . "/components/com_thm_organizer/models/fields/css/schedule_selection_tree.css");
        $doc->addScript(JURI::root(true) . "/components/com_thm_organizer/models/fields/tree.js");
        $doc->addScript(JURI::root(true) . "/components/com_thm_organizer/models/fields/departmentSemesterSelection.js");

        if ($idString != "")
        {
            $treeids = json_decode($idString);
        }
        else
        {
            $treeids = array();
        }


        ?>

<script type="text/javascript" charset="utf-8">
    var prefix = '<?php echo JURI::root(true); ?>';
    var menuID = <?php echo $menuid ?>;
    var treeIDs = <?php echo json_encode($treeids); ?>;
    var publicDefaultID = <?php echo json_encode($publicDefaultID); ?>;
    var externLinks = [];
    externLinks.ajaxHandler = '<?php echo JURI::root() . 'index.php?option=com_thm_organizer&view=ajaxhandler&format=raw'; ?>';
    var images = [];
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
        if(task == "item.apply" || task == "item.save" || task == "item.save2new" || task == "item.save2copy")
        {
            var paramID = Ext.get('jform_params_id');
            var treeChecked = tree.getChecked();
            var paramValue = Ext.encode(treeChecked);
            paramID.dom.value = paramValue;

            var paramID = Ext.get('jform_params_publicDefaultID');
            var publicDefault = tree.getPublicDefault();
            var paramValue = Ext.encode(publicDefault);
            paramID.dom.value = paramValue;
        }

        if (task == 'item.setType' || task == 'item.setMenuType') {
        if(task == 'item.setType') {
            document.id('item-form').elements['jform[type]'].value = type;
            document.id('fieldtype').value = 'type';
        } else {
            document.id('item-form').elements['jform[menutype]'].value = type;
        }
        Joomla.submitform('item.setType', document.id('item-form'));
        } else if (task == 'item.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
            Joomla.submitform(task, document.id('item-form'));
        } else {
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
.x-tree-node-cb {
    float: none;
}

.MySched_scheduler_selection_icons
{
    margin: 0;
}
</style>


<div style="width: auto; height: auto;" id="tree-div"></div>
<?php
    echo "<div>" . JText::_("COM_THM_ORGANIZER_RIA_TREE_DESCRIPTION") . "</div>";
    }
}
