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
        var poolUrl = "<?php echo $this->baseurl; ?>/index.php?option=com_thm_organizer";
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
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=subject_edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="modul-form">
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_SUM_PROPERTIES'); ?></legend>
        <fieldset>
            <legend><?php echo JText::_('COM_THM_ORGANIZER_SUM_PROPERTIES_GENERAL'); ?></legend>
            <div id="propertiesDiv">
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
                        <?php echo $this->form->getLabel('creditpoints'); ?>
                        <?php echo $this->form->getInput('creditpoints'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('expenditure'); ?>
                        <?php echo $this->form->getInput('expenditure'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('present'); ?>
                        <?php echo $this->form->getInput('present'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('independent'); ?>
                        <?php echo $this->form->getInput('independent'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('proofID'); ?>
                        <?php echo $this->form->getInput('proofID'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('pformID'); ?>
                        <?php echo $this->form->getInput('pformID'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('frequencyID'); ?>
                        <?php echo $this->form->getInput('frequencyID'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('methodID'); ?>
                        <?php echo $this->form->getInput('methodID'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('fieldID'); ?>
                        <?php echo $this->form->getInput('fieldID'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('responsible'); ?>
                        <?php echo $this->form->getInput('responsible'); ?>
                    </li>
                    <li>
                        <?php echo $this->form->getLabel('teacherID'); ?>
                        <?php echo $this->form->getInput('teacherID'); ?>
                    </li>
                </ul>
            </div>
        </fieldset>
        <fieldset>
            <legend>
                <?php echo JText::_('COM_THM_ORGANIZER_SUM_PROPERTIES_DE'); ?>
            </legend>
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
                <li>
                    <?php echo $this->form->getLabel('objective_de'); ?>
                    <?php echo $this->form->getInput('objective_de'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('content_de'); ?>
                    <?php echo $this->form->getInput('content_de'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('preliminary_work_de'); ?>
                    <?php echo $this->form->getInput('preliminary_work_de'); ?>
                </li>
            </ul>
        </fieldset>
        <fieldset>
            <legend>
                <?php echo JText::_('COM_THM_ORGANIZER_SUM_PROPERTIES_EN'); ?>
            </legend>
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
                <li>
                    <?php echo $this->form->getLabel('objective_en'); ?>
                    <?php echo $this->form->getInput('objective_en'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('content_en'); ?>
                    <?php echo $this->form->getInput('content_en'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('preliminary_work_en'); ?>
                    <?php echo $this->form->getInput('preliminary_work_en'); ?>
                </li>
            </ul>
        </fieldset>
    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_POM_PROPERTIES_MAPPING'); ?></legend>
        <ul class="adminformlist">
            <li>
                <?php echo $this->form->getLabel('programID'); ?>
                <?php echo $this->form->getInput('programID'); ?>
            </li>
            <li>
                <?php echo $this->form->getLabel('parentID'); ?>
                <?php echo $this->form->getInput('parentID'); ?>
            </li>
        </ul>
    </fieldset>
    <div>
        <input type="hidden" name="task" value="" />
        <?php echo $this->form->getInput('id'); ?>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
