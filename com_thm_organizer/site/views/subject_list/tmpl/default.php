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
require_once 'subject_list.php';
?>
<span class="flag" style="float: right;">
    <a class='naviLink' href="#"
       onclick="$('#languageTag').val('<?php echo $this->otherLanguageTag; ?>');$('#adminForm').submit();">
        <img class="languageSwitcher"
             alt="<?php echo $this->otherLanguageTag; ?>"
             src="<?php echo $this->flagPath; ?>" />
    </a>
</span>
<h1 class="componentheading"><?php echo $this->subjectListText . ' - ' . $this->programName; ?></h1>
<div id="j-main-container" class="span10">
    <form action="index.php?" id="adminForm"  method="post"
          name="adminForm" xmlns="http://www.w3.org/1999/html">
        <div class="searchArea">
            <div class="js-stools clearfix">
                <div class="clearfix">
                    <div class="js-stools-container-bar">
                        <label for="filter_search" class="element-invisible">
                            <?php echo JText::_('JSEARCH_FILTER'); ?>
                        </label>
                        <div class="btn-wrapper input-append">
                            <input type="text" name="search" id="filter_search"
                                   value="<?php echo $this->escape($this->state->get('search')); ?>"
                                   title="<?php echo JText::_('COM_THM_ORGANIZER_SEARCH_TITLE'); ?>" />
                            <button type="submit" class="btn hasTooltip"
                                    title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
                                <i class="icon-search"></i>
                            </button>
                        </div>
                        <div class="btn-wrapper">
                            <button type="button" class="btn hasTooltip js-stools-btn-clear"
                                    title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';">
                                <i class="icon-delete"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="option" value="com_thm_organizer" />
        <input type="hidden" name="view" value="subject_list" />
        <input type="hidden" id="groupBy" name="groupBy" value="<?php echo $this->state->get('groupBy'); ?>" />
        <input type="hidden" id="languageTag" name="languageTag" value="<?php echo $this->state->get('languageTag'); ?>" />
        <?php echo JHtml::_('form.token'); ?>
<?php
    echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'list'));
    echo JHtml::_('bootstrap.addTab', 'myTab', 'list', JText::_('COM_THM_ORGANIZER_OVERVIEW'));
    THM_OrganizerTemplateSubjectList::render($this);
    echo JHtml::_('bootstrap.endTab');
//    echo JHtml::_('bootstrap.addTab', 'myTab', $set->name, JText::_($set->label, true));
//    THM_OrganizerTemplateSubjectGroupList::render($this->items, 'pool');
//    echo JHtml::_('bootstrap.endTab');
//    echo JHtml::_('bootstrap.addTab', 'myTab', $set->name, JText::_($set->label, true));
//    THM_OrganizerTemplateSubjectGroupList::render($this->items, 'teacher');
//    echo JHtml::_('bootstrap.endTab');
//    echo JHtml::_('bootstrap.addTab', 'myTab', $set->name, JText::_($set->label, true));
//    THM_OrganizerTemplateSubjectGroupList::render($this->items, 'field');
//    echo JHtml::_('bootstrap.endTab');
    echo JHtml::_('bootstrap.endTabSet');
?>
    </form>
</div>