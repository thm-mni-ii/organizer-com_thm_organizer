<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Pools;

/**
 * Class loads curriculum information into the display context.
 */
class Curriculum extends ItemView
{
    protected $_layout = 'curriculum';

    public $fields = [];

    /**
     * Filters out invalid and true empty values. (0 is allowed.)
     *
     * @return void modifies the item
     */
    protected function filterAttributes()
    {
        return;
    }

    /**
     * Sets document scripts and styles
     *
     * @return void
     */
    protected function modifyDocument()
    {
        parent::modifyDocument();

        Factory::getDocument()->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/curriculum.css');
    }

    /**
     * Creates the HTML for a panel item.
     *
     * @param array $item the date for the panel item to create
     *
     * @return string the HTML for the panel item
     */
    private function getPanelItem($item)
    {
        $itemTemplate = '<div class="item ITEMCLASS">ITEMCONTENT</div>';
        $itemClass    = 'item-blank';
        $itemContent  = '';
        if (!empty($item) and !empty($item['name'])) {
            $bgColor = '#ffffff';
            if (!empty($item['bgColor']) and !empty($item['field'])) {
                $this->fields[$item['bgColor']] = $item['field'];
                $bgColor                        = $item['bgColor'];
            }

            $itemContent .= '<div class="item-color" style="background-color: ' . $bgColor . '"></div>';
            $itemContent .= '<div class="item-body">';

            $additionalLinks = '';
            $linkAttributes  = ['target' => '_blank'];
            if ($item['subjectID']) {
                $crp = empty($item['creditpoints']) ? '' : "{$item['creditpoints']} CrP";
                $url = "?option=com_thm_organizer&view=subject_item&id={$item['subjectID']}";

                $documentLinkAttributes = $linkAttributes + ['title' => Languages::_('THM_ORGANIZER_SUBJECT_ITEM')];
                $scheduleLinkAttributes = $linkAttributes + ['title' => Languages::_('THM_ORGANIZER_SCHEDULE')];

                $documentLink = HTML::link($url, '<span class="icon-file-2"></span>', $documentLinkAttributes);

                $scheduleUrl = "?option=com_thm_organizer&view=schedule_item&subjectIDs={$item['subjectID']}";

                $scheduleLink    =
                    HTML::link($scheduleUrl, '<span class="icon-info-calender"></span>', $scheduleLinkAttributes);
                $additionalLinks .= $documentLink . $scheduleLink;

                $itemClass = 'item-subject';
            } else {
                $crp = Pools::getCrPText($item);
                $url = '?option=com_thm_organizer&view=subjects';
                $url .= "&programID={$this->item['programID']}&poolID={$item['poolID']}";

                $itemClass = 'item-pool';
            }

            $title       = HTML::link($url, $item['name'], $linkAttributes);
            $itemContent .= '<div class="item-title">' . $title . '</div>';
            $itemContent .= $crp ? '<div class="item-crp">' . $crp . '</div>' : '';
            $itemContent .= $additionalLinks ? '<div class="item-tools">' . $additionalLinks . '</div>' : '';

            $itemContent .= '</div>';
        }

        $item = str_replace('ITEMCLASS', $itemClass, $itemTemplate);
        $item = str_replace('ITEMCONTENT', $itemContent, $item);

        return $item;
    }

    /**
     * Outputs the pool information in the form of a panel
     *
     * @param array $pool the pool to be displayed
     *
     * @return void displays HTML
     */
    public function renderPanel($pool)
    {
        $crpText = Pools::getCrPText($pool);
        ?>
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title"><?php echo $pool['name']; ?></div>
                <div class="panel-crp"><?php echo $crpText; ?></div>
            </div>
            <div class="panel-body">
                <?php $this->renderPanelBody($pool['children']); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Displays the body of the panel while iterating through child items
     *
     * @param array $children the subordinate elements to the pool modeled by the panel
     *
     * @return  void displays the panel body
     */
    private function renderPanelBody($children)
    {
        $maxOrdering = 0;
        $items       = [];
        foreach ($children as $child) {
            $items[$child['ordering']] = $this->getPanelItem($child);
            $maxOrdering               = $maxOrdering > $child['ordering'] ? $maxOrdering : $child['ordering'];
        }

        $trailingBlanks = 5 - $maxOrdering % 5;
        if ($trailingBlanks < 5) {
            $maxOrdering += $trailingBlanks;
        }

        for ($current = 1; $current <= $maxOrdering; $current++) {
            if ($current % 5 === 1) {
                echo '<div class="panel-row">';
            }
            echo empty($items[$current]) ? $this->getPanelItem([]) : $items[$current];
            if ($current % 5 === 0) {
                echo '</div>';
            }
        }
    }
}
