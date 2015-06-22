<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        default layout for thm organizer's index view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
require_once 'ungrouped_list.php';
require_once 'grouped_list.php';
$subjectsText = JText::_('COM_THM_ORGANIZER_SUBJECTS');
?>
<h1 class="componentheading"><?php echo $this->programName; ?></h1>
<div class="language-switches">
    <?php
    foreach ($this->languageSwitches AS $switch)
    {
        echo $switch;
    }
    ?>
</div>
<div id="j-main-container" class="span10">
    <form action="<?php JUri::current(); ?>" id="adminForm"  method="post"
          name="adminForm" xmlns="http://www.w3.org/1999/html">
        <div class="searchArea">
            <div class="js-stools clearfix">
                <div class="clearfix">
                    <div class="js-stools-container-bar">
                        <label for="filter_search" class="element-invisible">
                            <?php echo JText::_('COM_THM_ORGANIZER_SEARCH_SUBJECTS'); ?>
                        </label>
                        <div class="btn-wrapper input-append">
                            <input type="text" name="search" id="filter_search"
                                   value="<?php echo $this->escape($this->state->get('search')); ?>"
                                   title="<?php echo JText::_('COM_THM_ORGANIZER_SEARCH_SUBJECTS'); ?>" />
                            <button type="submit" class="btn hasTooltip"
                                    title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
                                <i class="icon-search"></i>
                            </button>
                        </div>
                        <div class="btn-wrapper">
                            <button type="button" class="btn hasTooltip js-stools-btn-clear"
                                    title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();">
                                <i class="icon-delete"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="programID" name="programID" value="<?php echo $this->state->get('programID'); ?>" />
        <input type="hidden" id="menuID" name="menuID" value="<?php echo $this->state->get('menuID'); ?>" />
        <input type="hidden" id="languageTag" name="languageTag" value="<?php echo $this->state->get('languageTag'); ?>" />
        <?php echo JHtml::_('form.token'); ?>
<?php
echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'list'));
echo JHtml::_('bootstrap.addTab', 'myTab', 'list', JText::_('COM_THM_ORGANIZER_ALPHABETICAL'));
THM_OrganizerTemplateUngroupedList::render($this);
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'myTab', 'pool', JText::_('COM_THM_ORGANIZER_BY_GROUP'));
$poolParams = array('order' => 'lft', 'name' => 'pool', 'id' => 'poolID', 'bgColor' => 'poolColor');
THM_OrganizerTemplateGroupedList::render($this, $poolParams);
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'myTab', 'teacher', JText::_('COM_THM_ORGANIZER_BY_TEACHER'));
$teacherParams = array('order' => 'teacherName', 'name' => 'teacherName', 'id' => 'teacherID', 'bgColor' => 'teacherColor');
THM_OrganizerTemplateGroupedList::render($this, $teacherParams);
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.addTab', 'myTab', 'field', JText::_('COM_THM_ORGANIZER_BY_FIELD'));
$teacherParams = array('order' => 'field', 'name' => 'field', 'id' => 'fieldID', 'bgColor' => 'subjectColor');
THM_OrganizerTemplateGroupedList::render($this, $teacherParams);
echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.endTabSet');
?>
    </form>
</div>