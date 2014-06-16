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
$languageLongTag = explode('-', JFactory::getLanguage()->getTag());
$language = $languageLongTag[0];

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
        poolUrl += "&languageTag=" + '<?php echo $language; ?>';
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
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-horizontal">
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PROPERTIES_DE'); ?></legend>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('name_de'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('name_de'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('short_name_de'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('short_name_de'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('abbreviation_de'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('abbreviation_de'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('description_de'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('description_de'); ?>
            </div>
        </div>
    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PROPERTIES_EN'); ?></legend>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('name_en'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('name_en'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('short_name_en'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('short_name_en'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('abbreviation_en'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('abbreviation_en'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('description_en'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('description_en'); ?>
            </div>
        </div>

    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_POM_PROPERTIES'); ?></legend>


        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('lsfID'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('lsfID'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('hisID'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('hisID'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('externalID'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('externalID'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('minCrP'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('minCrP'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('maxCrP'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('maxCrP'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('fieldID'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('fieldID'); ?>
            </div>
        </div>

    </fieldset>
    <?php include_once JPATH_COMPONENT_ADMINISTRATOR . '/templates/mapping.php'; ?>
    <?php include_once JPATH_COMPONENT_ADMINISTRATOR . '/templates/children.php'; ?>
    <div>
        <?php echo $this->form->getInput('id'); ?>
        <?php echo JHtml::_('form.token'); ?>
        <input type="hidden" name="task" value="" />
    </div>
</form>
