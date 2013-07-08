<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		default layout for thm organizer's index view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$baseLink = "index.php?option=com_thm_organizer&view=subject_list&view=subject_list&Itemid={$this->state->get('menuID')}&groupBy=";
$languageTag = $this->state->get('languageTag');
$subjectIndex = ($languageTag == 'de')? 'Modulhandbuch' : 'Subject List';
$defaultTabText = ($languageTag == 'de')? "...im Übersicht" : "...in Overview";
$defaultLink = JRoute::_($baseLink . "0");
$poolTabText = ($languageTag == 'de')? "...nach Modulpool" : "...by subjectpool";
$poolLink = JRoute::_($baseLink . "1");
$teacherTabText = ($languageTag == 'de')? "...nach Dozent" : "...by teacher";
$teacherLink = JRoute::_($baseLink . "2");
$fieldTabText = ($languageTag == 'de')? "...nach Fachgruppe" : "...by field of study";
$fieldLink = JRoute::_($baseLink . "3");
$subjectTitle = $this->state->get('languageTag') == 'de'? "Modultitel" : "Subject Name";
$responsibleTitle = $this->state->get('languageTag') == 'de'? "Verantwortliche" : "Responsible Teacher";
$CrPTitle = $this->state->get('languageTag') == 'de'? "Credit Punkte" : "Credit Points";
$flagPath = 'media' . DIRECTORY_SEPARATOR . 'com_thm_organizer' . DIRECTORY_SEPARATOR . 'images';
$flagPath .= DIRECTORY_SEPARATOR . 'curriculum' . DIRECTORY_SEPARATOR . $this->otherLanguageTag . '.png';
$defaultActive = 'active';
$poolActive = $teacherActive = $fieldActive = 'inactive';
?>
<span class="flag" style="float: right;">
    <a class='naviLink' href="<?php echo JRoute::_($this->langURI); ?>">
        <img class="languageSwitcher"
             alt="<?php echo $this->otherLanguageTag; ?>"
             src="<?php echo $flagPath; ?>" />
    </a>
</span>
<h1 class="componentheading"><?php echo $subjectIndex . ' - ' . $this->programName; ?></h1>
<div class="navi-bar">
	<span class="navi-tab <?php echo $defaultActive; ?>">
        <a class='naviLink'
           href="<?php echo $defaultLink; ?>">
            <?php echo $defaultTabText; ?>
        </a>
	</span>
    <span class="navi-tab <?php echo $poolActive; ?>">
        <a class='naviLink'
           href="<?php echo $poolLink; ?>">
            <?php echo $poolTabText; ?>
        </a>
	</span>
    <span class="navi-tab <?php echo $teacherActive; ?>">
        <a class='naviLink'
           href="<?php echo $teacherLink; ?>">
            <?php echo $teacherTabText; ?>
        </a>
	</span>
    <span class="navi-tab <?php echo $fieldActive; ?>">
        <a class='naviLink'
           href="<?php echo $fieldLink; ?>">
            <?php echo $fieldTabText; ?>
        </a>
	</span>
</div>
<form action="<?php echo JRoute::_('index.php?'); ?>"
      method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search">
			<label class="filter-search-lbl" for="search">
				<?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
			</label>
			<input type="text" name="search" id="filter_search"
				value="<?php echo $this->escape($this->state->get('search')); ?>"
				title="<?php echo JText::_('COM_CATEGORIES_ITEMS_SEARCH_FILTER'); ?>" />
			<button type="submit">
				<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
			</button>
			<button type="button"
				onclick="document.id('filter_search').value='';this.form.submit();">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
		</div>
        <div class="filter-select">
            <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
            <?php echo $this->pagination->getLimitBox(); ?>
        </div>
	</fieldset>
	<div class='subject-div'>
		<table class="subject-list">
			<thead>
				<tr>
					<th><?php echo $subjectTitle; ?></th>
					<th class="respColumn"><?php echo $responsibleTitle; ?></th>
					<th class="crpColumn"><?php echo $CrPTitle; ?></th>
				</tr>
			</thead>
            <tfoot class="pagination" align="center" colspan="3">
                <?php echo $this->pagination->getPagesLinks(); ?>
            </tfoot>
            <tbody>
<?php
$count = 0;
foreach ($this->items as $subject)
{
?>
				<tr class="row<?php echo $count % 2;?>">
					<td>
                        <a href="<?php echo $subject->subjectLink; ?>">
                            <?php echo $subject->name; ?>
                        </a>
                    </td>
					<td>
                        <a href="<?php echo $subject->groupsLink; ?>">
                            <?php echo $subject->teacherName; ?>
                        </a>
                    </td>
					<td><?php echo $subject->creditpoints; ?></td>
				</tr>
<?php
$count++;
}
?>
			</tbody>
		</table>
    </div>
    <input type="hidden" name="option" value="com_thm_organizer" />
    <input type="hidden" name="view" value="subject_list" />
	<input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>