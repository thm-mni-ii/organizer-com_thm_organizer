<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerTemplateCurriculumItemPanel
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_COMPONENT_SITE . '/helpers/pool.php';

/**
 * Class renders curriculum item panel information
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerTemplateCurriculumItemPanel
{
    /**
     * Generates the HTML output for a main panel element
     *
     * @param object  &$element the element to be rendered
     * @param integer $width    the width of the element to be displayed
     *
     * @return  void  generates HTML output
     */
    public function render(&$element, $width = 18)
    {
        $headStyle    = '';
        $moduleNumber = empty($element->externalID) ? '' : $element->externalID;
        if ($element->type == 'subject') {
            $linkAttribs      = ['target' => '_blank'];
            $moduleNumberHTML = JHtml::link($element->link, $moduleNumber, $linkAttribs);
            $crpHTML          = JHtml::link($element->link, $element->CrP, $linkAttribs);
            $nameHTML         = JHtml::link($element->link, $element->name, $linkAttribs);
        } else {
            $moduleNumberHTML = $moduleNumber;
            $crpHTML          = THM_OrganizerHelperPool::getCrPText($element);
            $nameHTML         = $element->name;
        }
        if (!empty($element->bgColor)) {
            $textColor = THM_OrganizerHelperComponent::getTextColor($element->bgColor);
            $headStyle .= ' style="background-color: ' . $element->bgColor . '; color: ' . $textColor . ';"';
        }
        ?>
		<div class="item" style="width: <?php echo $width; ?>%;">
			<div class="item-head" <?php echo $headStyle; ?>>
				<span class="item-code"><?php echo $moduleNumberHTML; ?></span>
				<span class="item-crp"><?php echo $crpHTML; ?></span>
			</div>
			<div class="item-name"><?php echo $nameHTML; ?></div>
			<div class="item-tools">
                <?php
                if (!empty($element->teacherName)) {
                    ?>
					<a class="btn hasTooltip" href="#" title="<?php echo $element->teacherName; ?>">
						<icon class="icon-user"></icon>
					</a>
                    <?php
                }
                if (!empty($element->children)) {
                    $script = 'onclick="toggleGroupDisplay(\'#panel-' . $element->mapping . '\')"';
                    echo '<a class="btn hasTooltip" ' . $script . ' title="' . JText::_('COM_THM_ORGANIZER_ACTION_OPEN_POOL') . '">';
                    echo '<icon class="icon-grid-view-2"></icon></a>';
                    THM_OrganizerTemplateCurriculumPanel::render($element);
                }
                ?>
			</div>
		</div>
        <?php
    }
}