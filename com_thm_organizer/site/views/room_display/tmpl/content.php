<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        template for display of content on registered monitors
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
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
