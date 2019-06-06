<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

require_once 'list_modal.php';
?>
<script>
    window.onload = function () {
        jQuery('#toolbar-new').click(function () {
            window.parent.closeIframeWindow('#subject_selection-list', 's');
            return false;
        });
    }
</script>