<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

$time    = date('H:i');
$blockNo = 0;
?>
<script type="text/javascript">
    var timer = null;

    function auto_reload()
    {
        window.location = document.URL;
    }

    window.onload = function () {
        var scheduleBlockElement = document.getElementsByClassName('schedule-block'),
            activeExists = checkIfActiveExists(scheduleBlockElement);

        timer = setTimeout('auto_reload()', <?php echo $this->model->params['scheduleRefresh']; ?>000);

        /**
         * Falls keine aktive Veranstaltung vorliegt, soll der vorhandene Platz genutzt werden,
         * dazu wird ein neuer Klassennamen benötigt.
         */
        if (activeExists === false)
        {
            for (var i = 0; i < scheduleBlockElement.length; i++)
            {
                scheduleBlockElement[i].className += ' nothingActive';
            }
        }
    };

    checkIfActiveExists = function (scheduleBlockElement) {
        var active = false;

        for (var i = 0; i < scheduleBlockElement.length; i++)
        {
            if (scheduleBlockElement[i].classList.contains('active'))
            {
                active = true;
            }
        }

        return active;
    }
</script>
<div class='display-schedule'>
    <div class='head'>
        <div class='banner'>
            <div class='thm-logo'><img src="components/com_thm_organizer/images/thm.svg" alt="THM-Logo"/></div>
            <div class="room-name"><?php echo $this->model->roomName; ?></div>
        </div>
        <div class='date-info'>
            <div class='time'><?php echo $time; ?></div>
            <div class='date'><?php echo date('d.m.Y'); ?></div>
        </div>
    </div>
    <div class="schedule-area schedule-wide">
		<?php
		if (!empty($this->model->blocks))
		{
			foreach ($this->model->blocks as $blockKey => $block)
			{
				$blockClass  = ($blockNo % 2) ? 'block-odd' : 'block-even';
				$activeClass = ($time >= $block['startTime'] and $time <= $block['endTime']) ? 'active' : 'inactive';
				?>
                <div class="schedule-block <?php echo $blockClass . ' ' . $activeClass; ?>">
                    <div class="block-time">
						<?php echo $block['startTime'] . ' - ' . $block['endTime']; ?>
                    </div>
                    <div class="block-data">
						<?php
						if (!empty($block['lessons']))
						{
							echo '<div class="block-title">';
							foreach ($block['lessons'] as $lesson)
							{
								$title = implode($lesson['titles']);
								if (!empty($lesson['method']))
								{
									$title .= $lesson['method'];
								}
								echo '<span class="lesson-title">' . $title . '</span>';
								if (!empty($lesson['divTime']))
								{
									echo '<span class="lesson-time">' . $lesson['divTime'] . '</span>';
								}
								echo '<br/>';
							}
							echo '</div>';
							echo '<div class="block-extra">';
							foreach ($block['lessons'] as $lesson)
							{
								echo '<span class="lesson-person">' . implode('/', $lesson['persons']) . '</span>';
								echo '<br/>';
							}
							echo '</div>';
						}
						?>
                    </div>
                </div>
				<?php
				$blockNo++;
			}
		}
		?>
    </div>
</div>
