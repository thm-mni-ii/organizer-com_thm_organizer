<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view pool edit template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('jquery.jquery');
$poolID = empty($this->item->id)? 0 : $this->item->id;
if (!empty($this->children))
{
    $maxOrdering = max(array_keys($this->children));
}
$rawPoolURL = 'index.php?option=com_thm_organizer&view=pool_manager';
$poolURL = JRoute::_($rawPoolURL, false);
$rawSubjectURL = 'index.php?option=com_thm_organizer&view=subject_manager';
$subjectURL = JRoute::_($rawSubjectURL, false);
?>
<script type="text/javascript">
var jq = jQuery.noConflict();
jq(document).ready(function(){
    jq('#jformprogramID').change(function(){
        var selectedPrograms = jq('#jformprogramID').val();
        if (selectedPrograms === null)
        {
            selectedPrograms = '';
        }
        else
        {
            selectedPrograms = selectedPrograms.join(',');
        }
        var oldSelectedParents = jq('#jformparentID').val();
        if (jq.inArray('-1', selectedPrograms) != '-1'){
            jq("#jformprogramID").find('option').removeAttr("selected");
            return false;
        }
        var poolUrl = "<?php echo JURI::root(); ?>index.php?option=com_thm_organizer";
        poolUrl += "&view=pool_ajax&format=raw&task=poolDegreeOptions";
        poolUrl += "&ownID=<?php echo $this->form->getValue('id'); ?>";
        poolUrl += "&programID=" + selectedPrograms;
        jq.get(poolUrl, function(options){
            jq('#jformparentID').html(options);
            var newSelectedParents = jq('#jformparentID').val();
            var selectedParents = new Array();
            if (newSelectedParents !== null && newSelectedParents.length)
            {
                if (oldSelectedParents !== null && oldSelectedParents.length)
                {
                    selectedParents = jq.merge(newSelectedParents, oldSelectedParents);
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
            jq('#jformparentID').val(selectedParents);
        });
    });
});
</script>
<form action="<?php echo JRoute::_("index.php?option=com_thm_organizer&view=pool_edit&id=$poolID"); ?>"
      method="post" name="adminForm" id="adminForm">
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PROPERTIES_DE'); ?></legend>
        <ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('name_de'); ?>
                <?php echo $this->form->getInput('name_de'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('short_name_de'); ?>
                <?php echo $this->form->getInput('short_name_de'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('abbreviation_de'); ?>
                <?php echo $this->form->getInput('abbreviation_de'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('description_de'); ?>
                <?php echo $this->form->getInput('description_de'); ?>
            </li>
        </ul>
    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PROPERTIES_EN'); ?></legend>
        <ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('name_en'); ?>
                <?php echo $this->form->getInput('name_en'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('short_name_en'); ?>
                <?php echo $this->form->getInput('short_name_en'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('abbreviation_en'); ?>
                <?php echo $this->form->getInput('abbreviation_en'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('description_en'); ?>
                <?php echo $this->form->getInput('description_en'); ?>                
            </li>
        </ul>
    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_POM_PROPERTIES'); ?></legend>
        <ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('lsfID'); ?>
                <?php echo $this->form->getInput('lsfID'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('hisID'); ?>
                <?php echo $this->form->getInput('hisID'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('externalID'); ?>
                <?php echo $this->form->getInput('externalID'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('minCrP'); ?>
                <?php echo $this->form->getInput('minCrP'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('maxCrP'); ?>
                <?php echo $this->form->getInput('maxCrP'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('fieldID'); ?>
                <?php echo $this->form->getInput('fieldID'); ?>
            </li>
        </ul>
    </fieldset>
    <?php    include JPATH_COMPONENT_ADMINISTRATOR . '/templates/mapping.php'; ?>
    <?php    include JPATH_COMPONENT_ADMINISTRATOR . '/templates/children.php'; ?>
    <div>
        <?php echo $this->form->getInput('id'); ?>
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
