<?php
/**
 * @category    Joomla <extension type>
 * @package     THM_<extension family>
 * @subpackage  <extension name>.<admin/site>
 * @name        <class name>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Displays event information
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerTemplateSubjectList
{
    /**
     * Renders subject information
     *
     * @param   array   &$view   the view context
     *
     * @return  void
     */
    public static function render(&$view)
    {
        if (count($view->items))
        {
            echo "<table>";
            echo "<tbody>";
            foreach ($view->items AS $item)
            {
                if ($item->teacherResp == 2)
                {
                    continue;
                }
                $moduleNr = empty($item->externalID)? '' : "($item->externalID)";
                $link = empty($item->subjectLink)? 'XXXX' : '<a href="' . $item->subjectLink . '">XXXX</a>';
                echo '<tr>';
                echo '<td>' . str_replace('XXXX', $item->subject . $moduleNr, $link) . '</td>';
                echo '<td>' . str_replace('XXXX', $item->teacherName, $link) . '</td>';
                echo '<td>' . str_replace('XXXX', $item->creditpoints, $link) . '</td>';
                echo '</tr>';
            }
            echo "</tbody>";
            echo '<tfoot><tr><td colspan="3">' . $view->pagination->getListFooter() . '</td></tr></tfoot>';
            echo "</table>";
        }
    }
}
 