<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        template for display of content on registered monitors
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;

?>
<script type="text/javascript">
var timer = null;
function auto_reload()
{
  window.location = document.URL;
}
window.onload = function(){
    timer = setTimeout('auto_reload()', <?php echo $this->content_refresh; ?>000);
}
</script>
<img width="100%" src="images/thm_organizer/<?php echo $this->content; ?>" >
