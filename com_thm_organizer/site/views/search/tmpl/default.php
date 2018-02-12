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
$attribs = ['target' => '_blank'];
?>
<div id="j-main-container" class="organizer-search-container">
    <form action="<?php JUri::current(); ?>" id="adminForm" method="get" name="adminForm">
        <div class="language-switches">
            <?php
            foreach ($this->languageSwitches as $switch) {
                echo $switch;
            }
            ?>
        </div>
        <h1 class="componentheading"><?php echo $this->lang->_('COM_THM_ORGANIZER_SEARCH_VIEW_TITLE'); ?></h1>
        <div class="toolbar">
            <div class="tool-wrapper search">
                <input type="text" name="search" id="search-input" class="search-input"
                       value="<?php echo addslashes($this->query); ?>"
                       size="25"/>
                <button type="submit" class="btn-search hasTooltip"
                        title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
                    <i class="icon-search"></i>
                </button>
                <button type="reset" class="btn-reset hasTooltip"
                        title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>"
                        onclick="document.getElementById('search-input').value='';form.submit();">
                    <i class="icon-delete"></i>
                </button>
            </div>
        </div>
        <input type="hidden" id="languageTag" name="languageTag" value="<?php echo $this->languageTag; ?>"/>
        <input type="hidden" id="option" name="option" value="com_thm_organizer"/>
        <input type="hidden" id="view" name="view" value="search"/>
        <?php
        $containerOpened = false;

        foreach ($this->results as $strength => $sResults) {
            if (!empty($sResults)) {
                $headerShown = false;

                foreach ($sResults as $resource => $rResults) {
                    foreach ($rResults as $result) {
                        if (!$containerOpened) {
                            echo '<div class="results-container">';
                            $containerOpened = true;
                        }

                        if (!$headerShown) {

                            $strengthTitle       = 'COM_THM_ORGANIZER_' . strtoupper($strength) . '_MATCHES';
                            $strengthDescription = 'COM_THM_ORGANIZER_' . strtoupper($strength) . '_MATCHES_DESC';
                            echo '<h3>' . $this->lang->_($strengthTitle) . '</h3><hr><ul>';
                            $headerShown = true;
                        }

                        echo '<li>';
                        echo '<div class="resource-item">' . $result['text'] . '</div>';
                        echo '<div class="resource-links">';

                        foreach ($result['links'] as $type => $link) {
                            $constant = 'COM_THM_ORGANIZER_' . strtoupper($type);

                            if ($type == 'curriculum') {
                                $icon = '<span class="icon-grid-2"></span>';
                                echo JHtml::link($link, $icon . $this->lang->_($constant), $attribs);
                            }

                            if ($type == 'schedule') {
                                $icon = '<span class="icon-calendar"></span>';
                                echo JHtml::link($link, $icon . $this->lang->_($constant), $attribs);
                            }

                            if ($type == 'subject_list') {
                                $icon = '<span class="icon-list"></span>';
                                echo JHtml::link($link, $icon . $this->lang->_($constant), $attribs);
                            }

                            if ($type == 'subject_details') {
                                $icon = '<span class="icon-book"></span>';
                                echo JHtml::link($link, $icon . $this->lang->_($constant), $attribs);
                            }
                        }

                        echo '</div>';

                        if (!empty($result['description'])) {
                            echo '<div class="resource-description">';

                            if (is_string($result['description'])) {
                                echo $result['description'];
                            } elseif (is_array($result['description'])) {
                                echo implode(', ', $result['description']);
                            }

                            echo '</div>';
                        }
                        echo '</li>';
                    }

                }

                echo '</ul>';

            }

        }

        if ($containerOpened) {
            echo '</div>';
        }
        ?>
    </form>
</div>
