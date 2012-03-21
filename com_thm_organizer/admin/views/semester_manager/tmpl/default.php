<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester manager default template
 * @description standard template display of the list of semesters
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
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
                <col class="thm_organizer_sm_date_column" />
                <col class="thm_organizer_sm_date_column" />
                <col />
            </colgroup>
            <thead>
                <tr>
                    <th />
                    <th><?php echo JText::_('COM_THM_ORGANIZER_SEM_ORG'); ?></th>
                    <th><?php echo JText::_('COM_THM_ORGANIZER_SEM_PP'); ?></th>
                    <th><?php echo JText::_('COM_THM_ORGANIZER_SEM_SD'); ?></th>
                    <th><?php echo JText::_('COM_THM_ORGANIZER_SEM_ED'); ?></th>
                    <th />
                </tr>
            </thead>
<?php if(!empty($this->semesters)){
        $k = 0;
        foreach($this->semesters as $semester){
            $k % 2 == 0? $class = "row0" : $class = "row1";
            $k++;
            $checked = JHTML::_( 'grid.id', $semester["id"], $semester["id"] ); ?>
            <tr class="<?php echo $class; ?>">
                <td class="thm_organizer_sm_checkbox">
                    <?php echo $checked; ?>
                </td>
                <td class="thm_organizer_sm_orgdata">
                    <a href='<?php echo $semester["url"]; ?>'>
                        <?php echo $semester["organization"]; ?>
                    </a>
                </td>
                <td class="thm_organizer_sm_semesterdata">
                    <a href='<?php echo $semester["url"]; ?>'>
                        <?php echo $semester["semesterDesc"]; ?>
                    </a>
                </td>
                <td class="thm_organizer_sm_date">
                    <a href='<?php echo $semester["url"]; ?>'>
                        <?php echo $semester["startdate"]; ?>
                    </a>
                </td>
                <td class="thm_organizer_sm_date">
                    <a href='<?php echo $semester["url"]; ?>'>
                        <?php echo $semester["enddate"]; ?>
                    </a>
                </td>
                <td>
                    <?php echo $semester["schedules_button"]; ?>
                </td>
            </tr>
<?php } } ?>
        </table>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
    </form>
</div>
