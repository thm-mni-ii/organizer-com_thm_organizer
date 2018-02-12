<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
$casURL         = "document.location.href='index.php?option=com_externallogin&view=server&server=1';return false;";
$containerClass = $this->showRegistration ? ' uses-login' : '';

if (!empty($this->menu)) {
    $menuID   = $this->menu['id'];
    $menuText = $this->lang->_('COM_THM_ORGANIZER_BACK');
}

$position = JComponentHelper::getParams('com_thm_organizer')->get('loginPosition');
?>
<div class="toolbar">
    <div class="tool-wrapper language-switches">
        <?php
        foreach ($this->languageSwitches as $switch) {
            echo $switch;
        }
        ?>
    </div>
</div>
<div class="subject-list <?php echo $containerClass; ?>">
    <?php if (!empty($this->item->name)): ?>
        <h1 class="componentheading"><?php echo $this->item->name; ?></h1>
    <?php endif; ?>
    <?php if ($this->showRegistration): ?>
        <?php if (empty(JFactory::getUser()->id)): ?>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    const registrationLink = jQuery('#login-form ul.unstyled > li:first-child > a'),
                        oldURL = registrationLink.attr('href');

                    let queryParams = '&redirect=subject_details';
                    queryParams += '&id=<?php echo $this->subjectID; ?>';
                    queryParams += '&Itemid=<?php echo $menuID; ?>';
                    queryParams += '&languageTag=<?php echo $this->langTag; ?>';

                    registrationLink.attr('href', oldURL + queryParams);
                });
            </script>
            <div class="tbox-yellow">
                <p><?php echo $this->lang->_("COM_THM_ORGANIZER_COURSE_LOGIN_WARNING"); ?></p>
                <?php echo JHtml::_('content.prepare', '{loadposition ' . $position . '}'); ?>
                <div class="right">
                    <?php if (!empty($this->menu)): ?>
                        <a href="<?php echo JRoute::_($this->menu['route'], false); ?>" class="btn btn-mini"
                           type="button">
                            <span class="icon-list"></span>
                            <?php echo $menuText ?>
                        </a>
                    <?php endif; ?>
                    <a class="btn" onclick="<?php echo $casURL; ?>">
                        <span class="icon-apply"></span>
                        <?php echo $this->lang->_('COM_THM_ORGANIZER_COURSE_ADMINISTRATOR_LOGIN'); ?>
                    </a>
                </div>
                <div class="clear"></div>
            </div>
        <?php else: ?>
            <div class="tbox-<?php echo $this->color; ?> course-status">
                <div class="status-container left">
                    <?php
                    foreach ($this->courses as $course) {
                        echo '<div class="course-item">';

                        if (!empty($course['campus']['name'])) {
                            echo '<span class="campus">' . $course['campus']['name'] . '</span>';
                        }

                        echo '<span>' . $course['dateText'] . '</span>';
                        echo '<span class="status">' . $course['statusDisplay'] . '</span>';
                        echo '<div class="right">';
                        echo $course['registrationButton'];
                        echo '</div>';
                        echo '<div class="clear"></div>';
                        echo '</div>';
                    }
                    ?>
                </div>
                <div class="right">
                    <?php if (!empty($this->menu)): ?>
                        <a href="<?php echo JRoute::_($this->menu['route'], false); ?>" class="btn btn-mini"
                           type="button">
                            <span class="icon-list"></span>
                            <?php echo $menuText ?>
                        </a>
                    <?php endif; ?>
                    <?php echo JHtml::_('content.prepare', '{loadposition ' . $position . '}'); ?>
                </div>
                <div class="clear"></div>
            </div>
        <?php endif; ?>
    <?php endif;

    if (!empty($this->courses)) {
        if (!empty($this->item->campusID)) {
            require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/campuses.php';
            $campusName     = THM_OrganizerHelperCampuses::getName($this->item->campusID);
            $campusLocation = THM_OrganizerHelperCampuses::getLocation($this->item->campusID);
            echo '<div class="subject-item">';
            echo '<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_CAMPUS') . '</div>';
            echo '<div class="subject-content">' . $campusName . ' ' . $campusLocation . '</div>';
            echo '</div>';
        }

        $this->displayAttribute('description', 'COURSE_DESCRIPTION');
    } else {
        $this->displayAttribute('externalID', 'MODULE_CODE');
        $this->displayAttribute('short_name');
        $this->displayTeacherAttribute('executors', 'MODULE_COORDINATOR');
        $this->displayTeacherAttribute('teachers', 'teacher', $this->lang->_('COM_THM_ORGANIZER_TEACHERS_PLACEHOLDER'));
        $this->displayAttribute('description', 'SHORT_DESCRIPTION');
        $this->displayAttribute('objective', 'objectives');
        $this->displayAttribute('content', 'contents');
        $this->displayStarAttribute('expertise');
        $this->displayStarAttribute('method_competence');
        $this->displayStarAttribute('social_competence');
        $this->displayStarAttribute('self_competence');
        $this->displayAttribute('duration');

        if (!empty($this->item->instructionLanguage)) {
            $value = ($this->item->instructionLanguage == 'D') ?
                $this->lang->_('COM_THM_ORGANIZER_GERMAN') : $this->lang->_('COM_THM_ORGANIZER_ENGLISH');
            $this->displayValue('INSTRUCTION_LANGUAGE', $value);
        }

        $this->displayAttribute('expenditureOutput', 'EXPENDITURE');
        $this->displayAttribute('sws');
        $this->displayAttribute('method');
        $this->displayAttribute('preliminary_work');

        if (!empty($this->item->proof)) {
            $method = empty($this->item->pform) ? '' : ' ( ' . $this->item->pform . ' )';
            echo '
	<div class="subject-item">';
            echo '
		<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_PROOF') . '</div>
		';
            echo '
		<div class="subject-content">' . $this->item->proof . $method . '</div>
		';
            echo '
	</div>
	';
        }

        $this->displayAttribute('evaluation');
        $this->displayAttribute('frequency', 'AVAILABILITY');
        $this->displayAttribute('literature');
        $this->displayAttribute('aids', 'STUDY_AIDS');

        // Prerequisites which could not be completely resolved to specific modules
        $this->displayAttribute('prerequisites');
        $prerequisites = $this->getDependencies('pre');
        $this->displayValue('PREREQUISITE_MODULES', $prerequisites);
        $this->displayAttribute('recommended_prerequisites');
        $this->displayAttribute('prerequisiteOf', 'PREREQUISITE_FOR');
        $postrequisites = $this->getDependencies('post');
        $this->displayValue('POSTREQUISITE_MODULES', $postrequisites);

        $displayeCollab = JComponentHelper::getParams('com_thm_organizer')->get('displayeCollabLink');

        if (!empty($this->item->externalID) and !empty($displayeCollab)) {
            $ecollabLink = JComponentHelper::getParams('com_thm_organizer')->get('eCollabLink');
            $ecollabIcon = JUri::root() . 'media/com_thm_organizer/images/icon-32-moodle.png';
            echo '
	<div class="subject-item">';
            echo '
		<div class="subject-label">eCollaboration Link</div>
		';
            echo '
		<div class="subject-content">';
            echo '<a href="' . $ecollabLink . $this->item->externalID . '" target="_blank">';
            echo "<img class='eCollabImage' src='$ecollabIcon' title='eCollabLink'></a>";
            echo '
		</div>
	</div>
	';
        }

        ?>
        <?php echo $this->disclaimer->render($this->disclaimerData); ?>
    <?php } ?>
</div>
