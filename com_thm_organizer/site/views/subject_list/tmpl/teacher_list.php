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

/**
 * Displays a filtered set of subjects into the display context, grouped by associated teachers/coordinators.
 */
class THM_OrganizerTemplateTeacherList
{
    const RESPONSIBLE = 1;
    const TEACHES = 2;

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

        foreach (array_keys($view->teachers) as $teacherID) {
            $rows = [];

            foreach ($view->items as $subjectKey => $subject) {
                $isResponsible = (isset($subject->teachers[1]) and array_key_exists($teacherID, $subject->teachers[1]));
                $isTeacher     = (isset($subject->teachers[2]) and array_key_exists($teacherID, $subject->teachers[2]));

                switch ($view->params->get('teacherResp', 0)) {
                    case self::RESPONSIBLE:
                        $relevant = $isResponsible;
                        break;

                    case self::TEACHES:
                        $relevant = $isTeacher;
                        break;

                    default:
                        $relevant = ($isResponsible or $isTeacher);
                        break;
                }

                if ($relevant) {
                    $rows[] = $view->getItemRow($view->items[$subjectKey], 'teacher', $teacherID);
                }

            }

            if (!empty($rows)) {
                ?>
                <fieldset class="teacher-group">
                    <legend>
                        <span class="teacher-title"><?php echo $view->getTeacherText($teacherID); ?></span>
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