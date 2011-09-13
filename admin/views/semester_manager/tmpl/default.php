<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester manager default template
 * @description standard template display of the list of semesters
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     1.7.0
 */
defined('_JEXEC') or die('Restricted access');?>
<div id="thm_organizer_sm" >
    <form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>"  name="adminForm" method="post" >
        <table class="adminlist" cellpadding="0" cellspacing="0">
            <colgroup>
                <col id="thm_organizer_sm_checkbox_column" />
                <col id="thm_organizer_sm_org_column" />
                <col id="thm_organizer_sm_pp_column" />
            </colgroup>
            <thead>
                <tr>
                    <th />
                    <th><?php echo JText::_('Organization'); ?></th>
                    <th><?php echo JText::_('Planning Period'); ?></th>
                </tr>
            </thead>
<?php if(!empty($this->semesters)){
        $k = 0;
        foreach($this->semesters as $semester){
            $k % 2 == 0? $class = "row0" : $class = "row1";
            $k++;
            $checked = JHTML::_( 'grid.id', $semester["id"], $semester["id"] ); ?>
            <tr class="<?php echo "row$k"; ?>">
                <td class="thm_organizer_sm_checkbox">
                    <?php echo $checked; ?>
                </td>
                <td class="thm_organizer_sm_orgdata">
                    <a href='<?php echo $semester["link"]; ?>'><?php echo $semester["organization"]; ?></a>
                </td>
                <td class="thm_organizer_sm_semesterdata">
                    <a href='<?php echo $semester["link"]; ?>'><?php echo $semester["semesterDesc"]; ?></a>
                </td>
            </tr>
<?php } } ?>
        </table>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
    </form>
</div>
