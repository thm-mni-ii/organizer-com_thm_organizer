<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

?>
<script type="text/javascript">
    var timer = null;

    function auto_reload()
    {
        window.location = document.URL;
    }

    window.onload = function () {
        timer = setTimeout('auto_reload()', <?php echo $this->model->params['contentRefresh']; ?>000);
    }
</script>
<img class="room-display-content" src="images/thm_organizer/<?php echo $this->model->params['content']; ?>">
