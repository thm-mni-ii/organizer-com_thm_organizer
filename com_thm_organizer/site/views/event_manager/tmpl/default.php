<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        event list default template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$categorySelected = $this->categoryID != -1;
$displayCategyHeader = $categorySelected AND !empty($this->categories[$this->categoryID]);
$displayAuthorColumn = ($this->display_type != 3 AND $this->display_type != 7);
$displayResourcesColumn = ($this->display_type != 2 and $this->display_type != 6);
$displayCategoryColumn = ($this->display_type != 1 and $this->display_type != 5);
$orderby = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
?>
<script type="text/javascript">
    var jq = jQuery.noConflict();
 
    function action_button(task) {
        jq('#task').val(task);
        jq('#adminForm').submit();
    }
</script>
<form id='adminForm'
      name='adminForm'
      enctype='multipart/form-data'
      method='post'
      action='<?php echo JRoute::_("index.php?"); ?>' >
<?php
if ($displayCategyHeader OR $this->canWrite OR $this->canEdit)
{
?>
    <div class="toolbar-box" >
        <div class="title-bar">
<?php
if ($displayCategyHeader)
    {
        echo "<h2>{$this->categories[$this->categoryID]['title']}</h2>";
        if (!empty($this->categories[$this->categoryID]['description']))
        {
            echo "<p>{$this->categories[$this->categoryID]['description']}</p>";
        }
    }
?>
        </div>
        <div class="action-bar">
<?php
    if ($this->canWrite)
    {
?>
            <a class="hasTip action-link"
               title="<?php echo JText::_('COM_THM_ORGANIZER_NEW_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_NEW_DESCRIPTION');?>"
               onClick="action_button('event.edit');" >
                <span id="thm_organizer_new_span" class="new-span action-span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_NEW'); ?>
            </a>
<?php
    }
    if ($this->canEdit)
    {
?>
            <a class="hasTip action-link"
               title="<?php echo JText::_('COM_THM_ORGANIZER_EDIT_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_EDIT_DESCRIPTION');?>"
               onClick="action_button('event.edit');">
                <span id="thm_organizer_edit_span" class="edit-span action-span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_EDIT'); ?>
            </a>
            <a class="hasTip action-link"
               title="<?php echo JText::_('COM_THM_ORGANIZER_DELETE_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_DELETE_DESCRIPTION');?>"
               onClick="action_button('event.delete');">
                <span id="thm_organizer_delete_span" class="delete-span action-span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_DELETE'); ?>
            </a>
<?php
    }
?>
        </div>
    </div>
<?php
}
?>
    <div class="container">
        <fieldset class="filter-bar no-tabs light-border">
            <div class='filter-search fltlft'>
                <label class="filter-search-lbl" for="filter_search">
                    <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
                </label>
                <input type="text" name="filter_search" id="filter_search"
                    value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                    title="<?php echo JText::_('COM_THM_ORGANIZER_SEARCH_TITLE'); ?>" />
                <button type="submit">
                    <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>
                </button>
                <button type="button"
                    onclick="document.id('filter_search').value='';this.form.submit();">
                    <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
                </button>
            </div>
            <div class="filter-select fltrt">
<?php
if ($this->display_type != 1 and $this->display_type != 5)
{
?>
                <select name="filter_category" class="inputbox" onchange="this.form.submit()">
                        <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_EL_SEARCH_CATEGORIES'); ?></option>
                        <option value="*"><?php echo JText::_('COM_THM_ORGANIZER_EL_ALL_CATEGORIES'); ?></option>
                        <?php echo JHtml::_('select.options', $this->categories, 'id', 'title', $this->state->get('filter.category'));?>
                </select>
<?php
}
echo $this->form->getLabel('fromdate');
echo $this->form->getInput('fromdate');
echo $this->form->getLabel('todate');
echo $this->form->getInput('todate');
?>
            </div>
        </fieldset>
        <div class="clr"> </div>
        <div class="adminlist">
            <table class="admintable">
                <thead>
                    <tr>
<?php
if ($this->canEdit)
{
?>
                        <th class="checkbox-column"> </th>
<?php
}
?>
                        <th class="thm_organizer_th hasTip title-column"
                            title="<?php echo JText::_('COM_THM_ORGANIZER_TITLE') . "::"
                                    . JText::_('COM_THM_ORGANIZER_EL_TITLE_DESC'); ?>">
                            <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_TITLE', 'title', $direction, $orderby); ?>
                        </th>
<?php
if ($displayAuthorColumn)
{
?>
                        <th class="thm_organizer_th hasTip author-colum"
                            title="<?php echo JText::_('COM_THM_ORGANIZER_AUTHOR') . "::"
                                    . JText::_('COM_THM_ORGANIZER_EL_AUTHOR_DESC'); ?>">
                            <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_AUTHOR', 'created_by', $direction, $orderby); ?>
                        </th>
<?php
}
if ($displayResourcesColumn)
{
?>
                        <th class="thm_organizer_th hasTip resources-column"
                            title="<?php echo JText::_('COM_THM_ORGANIZER_EL_RESOURCES') . "::"
                                    . JText::_('COM_THM_ORGANIZER_EL_RESOURCES_DESC'); ?>">
                            <?php echo JText::_('COM_THM_ORGANIZER_EL_RESOURCES'); ?>
                        </th>
<?php
}
if ($displayCategoryColumn)
{
?>
                        <th class="thm_organizer_th hasTip category-column"
                            title="<?php echo JText::_('COM_THM_ORGANIZER_CATEGORY') . "::"
                                    . JText::_('COM_THM_ORGANIZER_EL_CATEGORY_DESC'); ?>">
                            <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_CATEGORY', 'categoryID', $direction, $orderby); ?>
                        </th>
<?php
}
?>
                        <th class="thm_organizer_th hasTip date-column"
                            title="<?php echo JText::_('COM_THM_ORGANIZER_DATE') . "::"
                                    . JText::_('COM_THM_ORGANIZER_EL_DATE_DESC'); ?>">
                            <?php echo JHtml::_('grid.sort', 'COM_THM_ORGANIZER_DATE', 'date', $direction, $orderby); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
<?php
if (!empty($this->events))
{
    $rows = 0;
    foreach ($this->events as $event)
    {
        $rowclass = ($rows % 2 === 0)? "0" : "1";
?>
                    <tr class="row<?php echo $rowclass; ?>">
<?php
        if ($this->canEdit)
        {
            echo '<td class="checkbox-column">';
            echo $event['userCanEdit']? JHtml::_('grid.id', $event['id'], $event['id']) : '';
            echo '</td>';
        }
?>
                        <td class="title-column">
                            <span class="title-text hasTip"
                                  title="<?php echo $event['title'];?>">
                                <a href="<?php echo $event['detailsLink'] . $this->itemID; ?>">
                                    <?php echo $event['title']; ?>
                                </a>
                            </span>
                        </td>
<?php
        if ($displayAuthorColumn)
        {
             echo "<td class='author-column'>{$event['author']}</td>";
        }
        if ($displayResourcesColumn)
        {
             echo "<td class='resources-column'>{$event['resources']}</td>";
        }
        if ($displayCategoryColumn)
        {
?>
                        <td class="category-column">
                            <span class="thm_organizer_el_eventcat hasTip"
                                  title="Kategorie Ansicht::Events dieser Kategorie betrachten.">
                                <a href="<?php echo $event['categoryLink'] . $this->itemID; ?>">
                                    <?php echo $event['eventCategory']; ?>
                                </a>
                            </span>
                        </td>
<?php
        }
?>
                        <td class='date-column'><?php echo $event['displayDates']; ?></td>
                    </tr>
<?php
        $rows++;
    }
}
?>
            </table>
        </div>
    </div>    
    <input type="hidden" name="option" value="com_thm_organizer" />
    <input type="hidden" name="view" value="event_manager" />
    <input type="hidden" id="task" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" name="boxchecked" value="0" />
    <input type="hidden" id="orderby" name="orderby" value="<?php echo $this->orderby; ?>" />
    <input type="hidden" id="orderbydir" name="orderbydir" value="<?php echo $this->orderbydir; ?>" />
    <input type="hidden" name="Itemid" value="<?php echo $this->itemID; ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>
