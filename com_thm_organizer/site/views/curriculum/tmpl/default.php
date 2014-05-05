<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view curriculum default
 * @description curriculum view default layout
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

$flagPath = 'media/com_thm_organizer/images/';
$flagPath .= ($this->languageTag == 'de')? 'en.png' : 'de.png';
?>
<script type="text/javascript">
    /* global parameters */
    var parameters = new Object();
    parameters.itemID = '<?php echo JFactory::getApplication()->input->get('Itemid', ''); ?>';
    parameters.programID = '<?php echo $this->params->get('programID'); ?>';
    parameters.poolIDs = '<?php echo $this->params->get('poolIDs', ''); ?>';
    parameters.languageTag = '<?php echo $this->languageTag; ?>';
    parameters.baseURL = '<?php echo JURI::root(); ?>';

    parameters.rowItems = <?php echo $this->params->get('maxItems', 6); ?>;
    parameters.horizontalSpacing = <?php echo $this->params->get('horizontalSpacing', 2); ?>;
    parameters.verticalSpacing = <?php echo $this->params->get('verticalSpacing', 2); ?>;
    parameters.itemWidth = <?php echo $this->params->get('itemWidth', 120); ?>;
    parameters.itemHeight = <?php echo $this->params->get('itemHeight', 120); ?>;

    parameters.schedulerLink = '<?php $this->params->get('schedulerLink', ''); ?>';
    parameters.ecollabLink = '<?php echo $this->ecollabLink; ?>';

    parameters.schedulerIcon = parameters.baseURL + 'media/com_thm_organizer/images/schedules.png';
    parameters.teacherIcon = parameters.baseURL + 'media/com_thm_organizer/images/teachers.png';
    parameters.poolIcon = parameters.baseURL + 'media/com_thm_organizer/images/pools.png';
    parameters.displayECollab = <?php echo $this->params->get('displayECollabLink', 1); ?>;
    parameters.ecollabIcon = parameters.baseURL + 'media/com_thm_organizer/images/icon-32-moodle.png';

    window.addEvent('domready', function() {
        var curriculumObj = new Curriculum(parameters);
        curriculumObj.getData();
        curriculumObj.render();
    });

    $( ".poolDialog" ).dialog({
        autoOpen: false,
        height: parameters.modalHeight,
        width: parameters.modalWidth,
        buttons: [
            {
                text: "OK",
                click: function() {
                    $( this ).dialog( "close" );
                }
            },
            {
                text: "<?php echo JText::_('JTOOLBAR_CLOSE'); ?>",
                click: function() {
                    $( this ).dialog( "close" );
                }
            }
        ]
    });
</script>
<?php
if ($this->params->get('show_page_heading', 1) AND $this->params->get('plugin_mode', '0') != 1)
{
?>
    <div class="flag" style="float: right;">
        <a class='naviLink' href="<?php echo JRoute::_($this->langUrl); ?>">
            <img class="languageSwitcher"
                 alt="<?php echo ($this->languageTag == 'de') ? 'en' : 'de'; ?>"
                 src="<?php echo $flagPath; ?>" />
        </a>
    </div>
    <h1 class="componentheading"><?php echo 'Curriculum - '; ?><span id="programName"></span></h1>
<?php
}