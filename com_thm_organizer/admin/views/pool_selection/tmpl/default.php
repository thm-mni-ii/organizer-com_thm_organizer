<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/templates/list_modal.php';
THM_OrganizerTemplateList_Modal::render($this);
?>
<script>
    jQuery(document).ready(function () {
        jQuery('div#toolbar-new button').click(function () {
            window.parent.closeIframeWindow('#pool_selection-list', 'p');
        });
    });
</script>