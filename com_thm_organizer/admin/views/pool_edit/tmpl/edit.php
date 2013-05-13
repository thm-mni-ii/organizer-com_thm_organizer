<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view pool edit template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('jquery.jquery');
?>
<script type="text/javascript">
var jq = jQuery.noConflict();
jq(document).ready(function(){
    jq('#jformprogramID').change(function(){
        var selectedPrograms = jq('#jformprogramID').val();
        var oldSelectedParents = jq('#jformparentID').val();
        if (jq.inArray('-1', selectedPrograms) != '-1'){
            jq("#jformprogramID").find('option').removeAttr("selected");
            return false;
        }
        var poolUrl = "<?php echo $this->baseurl; ?>/index.php?option=com_thm_organizer";
        poolUrl += "&view=ajax_handler&format=raw&task=pool.byDegree";
        poolUrl += "&ownID=<?php echo $this->form->getValue('id'); ?>";
        poolUrl += "&programID=" + selectedPrograms.join(',');
        jq.get(poolUrl, function(options){
            jq('#jformparentID').html(options);
            var newSelectedParents = jq('#jformparentID').val();
            var selectedParents = new Array();
            if (newSelectedParents !== null && newSelectedParents.length)
            {
                if (oldSelectedParents !== null && oldSelectedParents.length)
                {
                    selectedParents = jq.merge(newSelectedParenst, oldSelectedParents);
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
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=pool_edit&id=' . (int) $this->item->id); ?>"
      method="post" name="adminForm" id="modul-form">
	<fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_POM_PROPERTIES_DE'); ?></legend>
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
        </ul>
	</fieldset>
	<fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_POM_PROPERTIES_EN'); ?></legend>
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
        </ul>
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
        <?php echo $this->form->getInput('id'); ?>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
