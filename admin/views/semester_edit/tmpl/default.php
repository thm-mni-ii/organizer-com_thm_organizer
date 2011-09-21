<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester editor default template
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined("_JEXEC") or die;?>
<form action="<?php echo JRoute::_("index.php?option=com_thm_organizer"); ?>"
      enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
    <div class="m">
        <div id="toolbar" class="toolbar-list">
            <ul>
                <li id="toolbar-apply" class="button">
                    <button type="submit" class="hasTip"
                       title="<?php echo JText::_('COM_THM_ORGANIZER_APPLY')."::".JText::_('COM_THM_ORGANIZER_SM_APPLY_DESC');?>">
                        <span class="icon-32-apply"></span>
                        <?php echo JText::_('COM_THM_ORGANIZER_APPLY'); ?>
                    </button>
                </li>
                <li id="toolbar-close" class="button">
                    <a onClick="window.parent.location.reload(true);"
                       href="#" class="hasTip"
                       title="<?php echo JText::_('COM_THM_ORGANIZER_CLOSE')."::".JText::_('COM_THM_ORGANIZER_SM_CLOSE_DESC');?>">
                        <span class="icon-32-cancel"></span>
                        <?php echo JText::_('COM_THM_ORGANIZER_CLOSE'); ?>
                    </a>
                </li>
            </ul>
            <div class="clr"></div>
        </div>
        <div class="pagetitle icon-48-generic">
            <h2><?php echo $this->title; ?></h2>
        </div>
    </div>
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