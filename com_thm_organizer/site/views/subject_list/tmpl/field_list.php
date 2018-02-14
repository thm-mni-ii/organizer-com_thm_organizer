<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';

/**
 * Displays a filtered set of subjects into the display context, grouped by their corresponding fields of expertise.
 */
class THM_OrganizerTemplateFieldList
{
    /**
     * Renders subject information
     *
     * @param array &$view the view context
     *
     * @return void
     */
    public static function render(&$view)
    {
        if (empty($view->items) or empty($view->teachers)) {
            return;
        }

        foreach ($view->fields as $fieldID => $field) {
            $rows = [];

            foreach ($view->items as $subject) {
                if ($subject->fieldID == $fieldID) {
                    $rows[] = $view->getItemRow($subject, 'field');
                }

            }

            if (!empty($rows)) {
                ?>
                <fieldset class="teacher-group">
                    <legend>
                        <span class="pool-title"><?php echo $field['name']; ?></span>
                    </legend>
                    <table>
                        <?php
                        foreach ($rows as $row) {
                            echo $row;
                        }
                        ?>
                    </table>
                </fieldset>
                <?php
            }
        }
    }
}