<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        subject selection default template
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.modal');
THM_CoreTemplateModalList::render($this);
?>
<script>
    window.onload = function()
    {
        jQuery('#toolbar-new').click(function() {
            window.parent.closeIframeWindow('#subject_selection-list', 's');
            return false;
        });
    }
</script>