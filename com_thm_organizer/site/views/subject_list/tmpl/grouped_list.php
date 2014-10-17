<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        default layout for thm organizer's index view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
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
<div class="navi-bar">
    <span class="navi-tab <?php echo $this->alphabeticalActive; ?>">
        <a class='naviLink' href="#"
           onclick="$('#groupBy').val('<?php echo NONE; ?>');$('#adminForm').submit();">
            <?php echo $this->alphabeticalTabText; ?>
        </a>
    </span>
    <span class="navi-tab <?php echo $this->poolActive; ?>">
        <a class='naviLink' href="#"
           onclick="$('#groupBy').val('<?php echo POOL; ?>');$('#adminForm').submit();">
            <?php echo $this->poolTabText; ?>
        </a>
    </span>
    <span class="navi-tab <?php echo $this->teacherActive; ?>">
        <a class='naviLink' href="#"
           onclick="$('#groupBy').val('<?php echo TEACHER; ?>');$('#adminForm').submit();">
            <?php echo $this->teacherTabText; ?>
        </a>
    </span>
    <span class="navi-tab <?php echo $this->fieldActive; ?>">
        <a class='naviLink' href="#"
           onclick="$('#groupBy').val('<?php echo FIELD; ?>');$('#adminForm').submit();">
            <?php echo $this->fieldTabText; ?>
        </a>
    </span>
</div>
<form action="<?php echo JRoute::_('index.php?'); ?>"
      method="post" name="adminForm" id="adminForm">
    <fieldset id="filter-bar" class='filter-bar'>
        <div class="filter-search">
            <label class="filter-search-lbl" for="search">
                <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
            </label>
            <input type="text" name="search" id="filter_search"
                value="<?php echo $this->escape($this->state->get('search')); ?>"
                title="<?php echo JText::_('COM_THM_ORGANIZER_SEARCH_TITLE'); ?>" />
            <button type="submit">
                <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
            </button>
            <button type="button"
                onclick="document.id('filter_search').value='';this.form.submit();">
                <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
            </button>
        </div>
    </fieldset>
    <input type="hidden" name="option" value="com_thm_organizer" />
    <input type="hidden" name="view" value="subject_list" />
    <input type="hidden" id="groupBy" name="groupBy" value="<?php echo $this->state->get('groupBy'); ?>" />
    <input type="hidden" id="languageTag" name="languageTag" value="<?php echo $this->state->get('languageTag'); ?>" />
    <input type="hidden" name="filter_order" value="<?php echo $this->state->get('list.ordering'); ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->get('list.direction'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>
<div id="accordion" class="module_catalogue">
<?php
foreach ($this->groups as $key => $group)
{
?>
    <h3 class="group-bar"
        style="background-color: rgba(<?php echo $group['bgColor']; ?>,.5);">
        <?php echo $group['name']; ?>
    </h3>
    <div class='subject-div'>
        <ul class="subject-list grouped">
<?php
    $count = 0;
    foreach ($this->items as $subject)
    {
        if ($subject->groupID != $group['id'])
        {
            continue;
        }
        $externalID = empty($subject->externalID)? '' : " ({$subject->externalID})";
?>
            <li class="row<?php echo $count % 2;?> subject-item">
                <div class="subject-name">
                    <a target="_blank" href="<?php echo $subject->subjectLink; ?>">
                        <?php echo $subject->name . $externalID; ?>
                    </a>
                </div>
                <div class="subject-responsible">
<?php
        if (!empty($subject->groupsLink))
        {
            echo "<a target='_blank' href='$subject->groupsLink'>$subject->teacherName</a>";
        }
        else
        {
            echo $subject->teacherName;
        }
?>
                </div>
                <div class="subject-crp"><?php echo $subject->creditpoints; ?> CrP</div>
            </li>
<?php
        $count++;
    }
?>
        </ul>
    </div>
<?php
}
?>
</div>
<script type="text/javascript">
    $("#accordion").accordion(
        {
            active: false,
            collapsible: true,
            heightStyle: "content"
        });
</script>