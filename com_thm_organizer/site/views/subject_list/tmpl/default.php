<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use THM_OrganizerHelperHTML as HTML;
use Joomla\CMS\Uri\Uri;

$query           = $this->escape($this->state->get('search'));
$resetVisibility = ' style="display: ';
$resetVisibility .= strlen($query) ? 'inline-block' : 'none';
$resetVisibility .= ';"';
$groupByArray    = [0 => 'alpha', 1 => 'number', 2 => 'pool', 3 => 'teacher', 4 => 'field'];
?>
<div id="j-main-container" class="span10">
    <form action="<?php Uri::current(); ?>" id="adminForm" method="post" name="adminForm">
        <div class="toolbar">
            <div class="tool-wrapper search">
                <input type="text" name="search" id="filter_search"
                       value="<?php echo $query; ?>"
                       title="<?php echo Languages::_('THM_ORGANIZER_SEARCH_SUBJECTS'); ?>"
                       size="25"/>
                <button type="submit" class="btn-search hasTooltip"
                        title="<?php echo HTML::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
                    <i class="icon-search"></i>
                </button>
                <button type="button" class="btn-reset hasTooltip" <?php echo $resetVisibility; ?>
                        title="<?php echo HTML::tooltipText('JSEARCH_FILTER_CLEAR'); ?>"
                        onclick="document.getElementById('filter_search').value='';this.form.submit();">
                    <i class="icon-delete"></i>
                </button>
            </div>
            <?php echo $this->languageLinks->render(); ?>
        </div>
        <div class="clearfix"></div>
        <input type="hidden" id="programID" name="programID" value="<?php echo $this->state->get('programID'); ?>"/>
        <input type="hidden" id="menuID" name="menuID" value="<?php echo $this->state->get('menuID'); ?>"/>
        <input type="hidden" id="languageTag" name="languageTag"
               value="<?php echo $this->state->get('languageTag'); ?>"/>
        <?php echo HTML::_('form.token'); ?>
        <h1 class="componentheading"><?php echo $this->displayName; ?></h1>
        <?php
        echo HTML::_('bootstrap.startTabSet', 'myTab', ['active' => $groupByArray[$this->state->get('groupBy', 0)]]);

        if ($this->params->get('showByName', 1)) {
            require_once 'basic_list.php';
            echo HTML::_('bootstrap.addTab', 'myTab', 'alpha', Languages::_('THM_ORGANIZER_ALPHABETICAL'));
            THM_OrganizerTemplateBasicList::render($this, 'name');
            echo HTML::_('bootstrap.endTab');
        }

        if ($this->params->get('showByModuleNumber', 1)) {
            require_once 'basic_list.php';
            echo HTML::_('bootstrap.addTab', 'myTab', 'number', Languages::_('THM_ORGANIZER_BY_SUBJECTNO'));
            THM_OrganizerTemplateBasicList::render($this, 'number');
            echo HTML::_('bootstrap.endTab');
        }

        if ($this->params->get('showByPool', 1)) {
            require_once 'pool_list.php';
            echo HTML::_('bootstrap.addTab', 'myTab', 'pool', Languages::_('THM_ORGANIZER_BY_GROUP'));
            THM_OrganizerTemplatePoolList::render($this);
            echo HTML::_('bootstrap.endTab');
        }

        if ($this->params->get('showByTeacher', 1)) {
            require_once 'teacher_list.php';
            echo HTML::_('bootstrap.addTab', 'myTab', 'teacher', Languages::_('THM_ORGANIZER_BY_TEACHER'));
            THM_OrganizerTemplateTeacherList::render($this);
            echo HTML::_('bootstrap.endTab');
        }

        if ($this->params->get('showByField', 0)) {
            require_once 'field_list.php';
            echo HTML::_('bootstrap.addTab', 'myTab', 'field', Languages::_('THM_ORGANIZER_BY_FIELD'));
            THM_OrganizerTemplateFieldList::render($this);
            echo HTML::_('bootstrap.endTab');
        }
        echo HTML::_('bootstrap.endTabSet');
        ?>
    </form>
    <?php echo $this->disclaimer->render([]); ?>
</div>