<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester manager default template
 * @description standard template for the display of semesters
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined('_JEXEC') or die('Restricted access');?>
<div id="thm_organizer_sm" >
    <form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>"  name="adminForm" method="post" >
        <table class="adminlist">
            <colgroup>
                <col id="thm_organizer_sm_checkbox_column" />
                <col id="thm_organizer_sm_org_column" />
                <col id="thm_organizer_sm_semester_column" />
                <col id="thm_organizer_sm_mng_column" />
            </colgroup>
            <thead>
                <tr>
                    <th />
                    <th><?php echo JText::_('Organization'); ?></th>
                    <th><?php echo JText::_('Semester'); ?></th>
                    <th><?php echo JText::_('Management (Group)'); ?></th>
                </tr>
            </thead>
<?php
if(!empty($this->semesters)){
    $k = 0;
    foreach($this->semesters as $semester){
        $k % 2 == 0? $class = "row0" : $class = "row1"; $k++;
        $checked = JHTML::_( 'grid.id', $semester->sid, $semester->sid ); ?>
            <tr class="<?php echo "row$k"; ?>">
                <td align="center">
                    <?php echo $checked; ?>
                </td>
                <td align="center">
                    <a href='<?php echo $semester->link; ?>'><?php echo $row->orgunit; ?></a>
                </td>
                <td align="center">
                    <a href='<?php echo $semester->link; ?>'><?php echo $row->semester; ?></a>
                </td>
                <td align="center">
                    <a href='<?php echo $semester->link; ?>'><?php echo $row->author; ?></a>
                </td>
            </tr>
<?php } } ?>
        </table>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
    </form>
</div>
