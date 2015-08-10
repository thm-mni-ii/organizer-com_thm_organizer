<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        pool selection default template
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.modal');
THM_CoreTemplateModalList::render($this);
?>
<script>
    jQuery( document ).ready(function() {
        jQuery('div#toolbar-new button').click(function() {
            window.parent.closeIframeWindow('#pool_selection-list', 'p');
        });
    });
</script>