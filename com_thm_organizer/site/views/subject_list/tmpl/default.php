<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        default layout for thm organizer's index view
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
require_once 'ungrouped_list.php';
require_once 'grouped_list.php';
$subjectsText = JText::_('COM_THM_ORGANIZER_SUBJECTS');
$query = $this->escape($this->state->get('search'));
$resetVisibility = ' style="visibility: ';
$resetVisibility .= strlen($query)? 'visibible' : 'hidden';
$resetVisibility .= ';"';
?>
<div id="j-main-container" class="span10">
    <form action="<?php JUri::current(); ?>" id="adminForm"  method="post"
          name="adminForm" xmlns="http://www.w3.org/1999/html">
        <div class="toolbar">
            <div class="tool-wrapper language-switches">
                <?php
                foreach ($this->languageSwitches AS $switch)
                {
                    echo $switch;
                }
                ?>
            </div>
            <div class="tool-wrapper search">
                <input type="text" name="search" id="filter_search"
                       value="<?php echo $query; ?>"
                       title="<?php echo JText::_('COM_THM_ORGANIZER_SEARCH_SUBJECTS'); ?>"
                       size="25"/>
                <button type="submit" class="btn-search hasTooltip"
                        title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
                    <i class="icon-search"></i>
                </button>
                <button type="button" class="btn-reset hasTooltip" <?php echo $resetVisibility; ?>
                        title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>"
                        onclick="document.getElementById('filter_search').value='';this.form.submit();">
                    <i class="icon-delete"></i>
                </button>
            </div>
        </div>
        <div class="clearfix"></div>
        <input type="hidden" id="programID" name="programID" value="<?php echo $this->state->get('programID'); ?>" />
        <input type="hidden" id="menuID" name="menuID" value="<?php echo $this->state->get('menuID'); ?>" />
        <input type="hidden" id="languageTag" name="languageTag" value="<?php echo $this->state->get('languageTag'); ?>" />
        <?php echo JHtml::_('form.token'); ?>
        <h1 class="componentheading"><?php echo $this->programName; ?></h1>
<?php
echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'list'));
echo JHtml::_('bootstrap.addTab', 'myTab', 'list', $this->lang->_('COM_THM_ORGANIZER_ALPHABETICAL'));
THM_OrganizerTemplateUngroupedList::render($this);
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'myTab', 'pool', $this->lang->_('COM_THM_ORGANIZER_BY_GROUP'));
$poolParams = array('order' => 'lft', 'name' => 'pool', 'id' => 'poolID', 'bgColor' => 'poolColor');
THM_OrganizerTemplateGroupedList::render($this, $poolParams, 'pool');
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'myTab', 'teacher', $this->lang->_('COM_THM_ORGANIZER_BY_TEACHER'));
$teacherParams = array('order' => 'teacherName', 'name' => 'teacherName', 'id' => 'teacherID', 'bgColor' => 'teacherColor');
THM_OrganizerTemplateGroupedList::render($this, $teacherParams, 'teacher');
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'myTab', 'field', $this->lang->_('COM_THM_ORGANIZER_BY_FIELD'));
$fieldParams = array('order' => 'field', 'name' => 'field', 'id' => 'fieldID', 'bgColor' => 'subjectColor');
THM_OrganizerTemplateGroupedList::render($this, $fieldParams, 'field');
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.endTabSet');
?>
    </form>
</div>