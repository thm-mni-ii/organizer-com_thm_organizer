<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        subject edit view edit template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
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
        poolUrl += "&programID=" + selectedPrograms + "&subject=true";
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
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=subject_edit&id=' . (int) $this->item->id); ?>"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-horizontal">
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_SUM_PROPERTIES'); ?></legend>
        <fieldset>
            <legend><?php echo JText::_('COM_THM_ORGANIZER_SUM_PROPERTIES_GENERAL'); ?></legend>
            <div id="propertiesDiv">

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
                        <?php echo $this->form->getLabel('creditpoints'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('creditpoints'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('sws'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('sws'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('expenditure'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('expenditure'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('present'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('present'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('independent'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('independent'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('proofID'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('proofID'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('pformID'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('pformID'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('frequencyID'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('frequencyID'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('methodID'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('methodID'); ?>
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

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('expertise'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('expertise'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('self_competence'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('self_competence'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('method_competence'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('method_competence'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('social_competence'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('social_competence'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('responsible'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('responsible'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('teacherID'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('teacherID'); ?>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>
                <?php echo JText::_('COM_THM_ORGANIZER_SUM_PROPERTIES_DE'); ?>
            </legend>

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

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('objective_de'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('objective_de'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('content_de'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('content_de'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('preliminary_work_de'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('preliminary_work_de'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('aids_de'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('aids_de'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('evaluation_de'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('evaluation_de'); ?>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>
                <?php echo JText::_('COM_THM_ORGANIZER_SUM_PROPERTIES_EN'); ?>
            </legend>

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

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('objective_en'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('objective_en'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('content_en'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('content_en'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('preliminary_work_en'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('preliminary_work_en'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('aids_en'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('aids_en'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('evaluation_en'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('evaluation_en'); ?>
                </div>
            </div>
        </fieldset>
    </fieldset>
    <?php    include JPATH_COMPONENT_ADMINISTRATOR . '/templates/mapping.php'; ?>
    <div>
        <input type="hidden" name="task" value="" />
        <?php echo $this->form->getInput('id'); ?>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
