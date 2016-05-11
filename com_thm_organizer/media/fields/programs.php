<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldPrograms
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.helpers.corehelper');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Class creates a form field for subject-degree program association
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldPrograms extends JFormField
{
    /**
     * @var  string
     */
    protected $type = 'programs';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return Select box
     */
    public function getInput()
    {
        $resourceID = $this->form->getValue('id');
        $contextParts = explode('.', $this->form->getName());
        $resourceType = str_replace('_edit', '', $contextParts[1]);
        $this->addScript($resourceID, $resourceType);

        $ranges = THM_OrganizerHelperMapping::getResourceRanges($resourceType, $resourceID);
        $selectedPrograms = !empty($ranges)?
            THM_OrganizerHelperMapping::getSelectedPrograms($ranges) : array();
        $allPrograms = THM_OrganizerHelperMapping::getAllPrograms();

        $defaultOptions = array(array('value' => '-1', 'text' => JText::_('JNONE')));
        $programs = array_merge($defaultOptions, $allPrograms);

        $attributes = array('multiple' => 'multiple', 'size' => '10');
        return JHTML::_("select.genericlist", $programs, "jform[programID][]", $attributes, "value", "text", $selectedPrograms);
    }

    /**
     * Adds the javascript to the page necessary to refresh the parent pool options
     *
     * @param   int     $resourceID    the resource's id
     * @param   string  $resourceType  the resource's type
     *
     * @return  void
     */
    private function addScript($resourceID, $resourceType)
    {
?>
<script type="text/javascript" charset="utf-8">
jQuery(document).ready(function(){
    jQuery('#jformprogramID').change(function(){
        var selectedPrograms = jQuery('#jformprogramID').val();
        if (selectedPrograms === null)
        {
            selectedPrograms = '';
        }
        else
        {
            selectedPrograms = selectedPrograms.join(',');
        }

        var oldSelectedParents = jQuery('#jformparentID').val();
        if (jQuery.inArray('-1', selectedPrograms) != '-1'){
            jQuery("#jformprogramID").find('option').removeAttr("selected");
            return false;
        }

        var poolUrl = "<?php echo JURI::root(); ?>index.php?option=com_thm_organizer";
        poolUrl += "&view=pool_ajax&format=raw&task=parentOptions";
        poolUrl += "&id=<?php echo $resourceID; ?>";
        poolUrl += "&type=<?php echo $resourceType; ?>";
        poolUrl += "&programIDs=" + selectedPrograms;
        jQuery.get(poolUrl, function(options){
            jQuery('#jformparentID').html(options);
            var newSelectedParents = jQuery('#jformparentID').val();
            var selectedParents = [];
            if (newSelectedParents !== null && newSelectedParents.length)
            {
                if (oldSelectedParents !== null && oldSelectedParents.length)
                {
                    selectedParents = jQuery.merge(newSelectedParents, oldSelectedParents);
                }
                else
                {
                    selectedParents = newSelectedParents;
                }
            }
            else if (oldSelectedParents !== null && oldSelectedParents.length)
            {
                selectedParents = oldSelectedParents;
            }

            jQuery('#jformparentID').val(selectedParents);

            // from lib_thm_core
            refreshChoosen('jformparentID');
        });
        refreshChoosen('jformparentID');
    });

    function toggleElement(chosenElement, value)
    {
        jQuery("#jformparentID").chosen("destroy");
        jQuery("select#jformparentID option").each(function() {
            if(chosenElement == $( this).innerHTML)
            {
                jQuery(this).prop('selected', value);
            }
        });
        jQuery("#jformparentID").chosen();
    }

    function addAddHandler()
    {
        jQuery('#jformparentID_chzn div.chzn-drop').click(function(element) {
            toggleElement(element.target.innerHTML, true);
            addRemoveHandler();
        });
    }

    function addRemoveHandler()
    {
        jQuery('div#jformparentID_chzn a.search-choice-close').click(function (element) {
            toggleElement(element.target.parentElement.childNodes[0].innerHTML, false);
            addAddHandler();
        });
    }

    addRemoveHandler();
    addAddHandler();
});
</script>
    <?php
    }
}
