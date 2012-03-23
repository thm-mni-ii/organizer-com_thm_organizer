<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester editor default template
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined("_JEXEC") or die;?>
<form action="<?php echo JRoute::_("index.php?option=com_thm_organizer"); ?>"
      enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
    <div id="thm_organizer_se">
        <table class="admintable">
            <tr>
                <td class="thm_organizer_se_label" >
                    <label for="organization"><?php echo JText::_("Organization:"); ?></label>
                </td>
                <td class="thm_organizer_se_meta_data" >
                    <input class="text_area" type="text" name="organization" id="ecname" size="25" maxlength="20"
                    value="<?php echo $this->organization;?>" />
                </td>
                <td class="thm_organizer_se_label" >
                    <label for="semester"><?php echo JText::_("Planning Period Name:"); ?></label>
                </td>
                <td class="thm_organizer_se_data" >
                    <input class="text_area" type="text" name="semester" id="ecname" size="25" maxlength="20"
                    value="<?php echo $this->semesterDesc;?>" />
                </td>
            </tr>
        </table>
    </div>
    <input type="hidden" name="task" value="semester.apply" />
    <input type="hidden" name="semesterID" value="<?php echo $this->semesterID; ?>" />
</form>