<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use OrganizerHelper;

$action = OrganizerHelper::getRedirectBase();
$menuID = OrganizerHelper::getInput()->getInt('Itemid');

$departmentID = $this->state->get('departmentID');
$periodID     = $this->state->get('periodID');
$programID    = $this->state->get('programID');
$showTable    = (!empty($this->columns) and !empty($this->rows));

?>
<div class="toolbar">
    <?php echo $this->languageLinks->render($this->languageParams); ?>
</div>
<div class="lesson-statistics-view">
    <h1 class="componentheading"><?php echo $this->lang->_('THM_ORGANIZER_LESSON_STATISTICS'); ?></h1>
    <form enctype="multipart/form-data" method="post"
          id="form-lesson-statistics" class="form-horizontal">
        <input type="hidden" name="option" value="com_thm_organizer">
        <input type="hidden" name="view" value="lesson_statistics">
        <input type='hidden' name='Itemid' value='<?php echo $menuID; ?>'>
        <?php echo $this->form->getField('planningPeriodID')->input; ?>
        <?php echo $this->form->getField('departmentID')->input; ?>
        <?php echo $this->form->getField('programID')->input; ?>
    </form>
    <div class="table-container">
        <table>
            <tr>
                <?php if ($showTable) : ?>
                    <td>
                        <span class="name"><?php echo $this->lang->_('THM_ORGANIZER_TOTAL'); ?></span>
                        <br>
                        <?php echo $this->total; ?>
                    </td>
                    <?php foreach ($this->columns as $column) : ?>
                        <td>
                            <span class="name"><?php echo $column['name']; ?></span>
                            <br>
                            <span class="total"><?php echo '(' . $column['total'] . ')'; ?></span>
                        </td>
                    <?php endforeach; ?>
                <?php else: ?>
                    <td>
                        <span class="name"><?php echo $this->lang->_('THM_ORGANIZER_NO_LESSONS_FOUND'); ?></span>
                    </td>
                <?php endif; ?>
            </tr>
            <?php foreach ($this->rows as $row) : ?>
                <tr>
                    <td>
                        <span class="name"><?php echo $row['name']; ?></span>
                        <br>
                        <span class="total"><?php echo '(' . $row['total'] . ')'; ?></span>
                    </td>
                    <?php foreach (array_keys($this->columns) as $columnID) : ?>
                        <td>
                            <?php echo (empty($this->lessons[$row['id']]) or empty($this->lessons[$row['id']][$columnID])) ?
                                0 : $this->lessons[$row['id']][$columnID]; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
