<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
require_once 'basic_list.php';
require_once 'field_list.php';
require_once 'pool_list.php';
require_once 'teacher_list.php';

$query           = $this->escape($this->state->get('search'));
$resetVisibility = ' style="display: ';
$resetVisibility .= strlen($query) ? 'inline-block' : 'none';
$resetVisibility .= ';"';
$groupByArray    = [0 => 'alpha', 1 => 'number', 2 => 'pool', 3 => 'teacher', 4 => 'field'];
?>
<div id="j-main-container" class="span10">
	<form action="<?php JUri::current(); ?>" id="adminForm" method="post" name="adminForm">
		<div class="toolbar">
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
			<div class="tool-wrapper language-switches">
                <?php
                foreach ($this->languageSwitches AS $switch) {
                    echo $switch;
                }
                ?>
			</div>
		</div>
		<div class="clearfix"></div>
		<input type="hidden" id="programID" name="programID" value="<?php echo $this->state->get('programID'); ?>"/>
		<input type="hidden" id="menuID" name="menuID" value="<?php echo $this->state->get('menuID'); ?>"/>
		<input type="hidden" id="languageTag" name="languageTag"
			   value="<?php echo $this->state->get('languageTag'); ?>"/>
        <?php echo JHtml::_('form.token'); ?>
		<h1 class="componentheading"><?php echo $this->displayName; ?></h1>
        <?php
        echo JHtml::_('bootstrap.startTabSet', 'myTab', ['active' => $groupByArray[$this->state->get('groupBy', 0)]]);

        if ($this->params->get('showByName', 1)) {
            echo JHtml::_('bootstrap.addTab', 'myTab', 'alpha', $this->lang->_('COM_THM_ORGANIZER_ALPHABETICAL'));
            THM_OrganizerTemplateBasicList::render($this, 'name');
            echo JHtml::_('bootstrap.endTab');
        }

        if ($this->params->get('showByModuleNumber', 1)) {
            echo JHtml::_('bootstrap.addTab', 'myTab', 'number', $this->lang->_('COM_THM_ORGANIZER_BY_SUBJECTNO'));
            THM_OrganizerTemplateBasicList::render($this, 'number');
            echo JHtml::_('bootstrap.endTab');
        }

        if ($this->params->get('showByPool', 1)) {
            echo JHtml::_('bootstrap.addTab', 'myTab', 'pool', $this->lang->_('COM_THM_ORGANIZER_BY_GROUP'));
            THM_OrganizerTemplatePoolList::render($this);
            echo JHtml::_('bootstrap.endTab');
        }

        if ($this->params->get('showByTeacher', 1)) {
            echo JHtml::_('bootstrap.addTab', 'myTab', 'teacher', $this->lang->_('COM_THM_ORGANIZER_BY_TEACHER'));
            THM_OrganizerTemplateTeacherList::render($this);
            echo JHtml::_('bootstrap.endTab');
        }

        if ($this->params->get('showByField', 0)) {
            echo JHtml::_('bootstrap.addTab', 'myTab', 'field', $this->lang->_('COM_THM_ORGANIZER_BY_FIELD'));
            THM_OrganizerTemplateFieldList::render($this);
            echo JHtml::_('bootstrap.endTab');
        }
        echo JHtml::_('bootstrap.endTabSet');
        ?>
	</form>
    <?php echo $this->disclaimer->render($this->disclaimerData); ?>
</div>