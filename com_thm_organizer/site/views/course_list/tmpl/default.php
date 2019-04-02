<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use \THM_OrganizerHelperHTML as HTML;

$header      = $this->lang->_('COM_THM_ORGANIZER_COURSE_OVERVIEW_HEADER');
$campusName  = empty($this->state->filter_campus) ? '' : THM_OrganizerHelperCampuses::getName($this->state->filter_campus);
$coursesType = empty($this->state->filter_prep_courses) ?
    $this->lang->_('COM_THM_ORGANIZER_COURSES') : $this->lang->_('COM_THM_ORGANIZER_PREP_COURSES');
$specTitle   = "$coursesType $campusName";
$header      = sprintf($header, $specTitle);

$action = \JUri::current();
$action .= empty(THM_OrganizerHelperComponent::getApplication()->getMenu()->getActive()) ? '' : '?option=com_thm_organizer&view=course_list';

$casURL        = "document.location.href='index.php?option=com_externallogin&view=server&server=1';return false;";
$loginRoute    = \JRoute::_('index.php?option=com_users&view=login&tmpl=component', false, 1);
$registerRoute = \JRoute::_('index.php?option=com_users&view=registration&tmpl=component', false, 1);
$profileRoute  = \JRoute::_("index.php?option=com_thm_organizer&view=participant_edit&languageTag={$this->shortTag}");

$position = THM_OrganizerHelperComponent::getParams()->get('loginPosition');

// This variable is also used in the subordinate template
$menuID = THM_OrganizerHelperComponent::getInput()->getInt('Itemid', 0);
if (!empty($menuID)):
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            let registrationLink = jQuery('#login-form ul.unstyled > li:first-child > a'),
                oldURL = registrationLink.attr('href');

            registrationLink.attr('href', oldURL + '&Itemid=<?php echo $menuID; ?>');
        });
    </script>
<?php endif; ?>
<div class="toolbar">
    <?php echo $this->languageLinks->render($this->languageParams); ?>
</div>
<div class="course-list-view uses-login">
    <h1><?php echo $header; ?></h1>

    <?php if (empty(\JFactory::getUser()->id)): ?>
        <div class="tbox-yellow">
            <p><?php echo $this->lang->_('COM_THM_ORGANIZER_COURSE_LOGIN_WARNING'); ?></p>
            <?php echo HTML::_('content.prepare', '{loadposition ' . $position . '}'); ?>
            <div class="right">
                <a class="btn" onclick="<?php echo $casURL; ?>">
                    <span class="icon-apply"></span>
                    <?php echo $this->lang->_('COM_THM_ORGANIZER_COURSE_ADMINISTRATOR_LOGIN'); ?>
                </a>
            </div>
            <div class="clear"></div>
        </div>
    <?php else: ?>
        <div class="toolbar">
            <div class="tool-wrapper">
                <a class='btn btn-max' href='<?php echo $profileRoute; ?>'>
                    <span class='icon-address'></span> <?php echo $this->lang->_('COM_THM_ORGANIZER_EDIT_USER_PROFILE'); ?>
                </a>
                <?php echo HTML::_('content.prepare', '{loadposition ' . $position . '}'); ?>
            </div>
        </div>
    <?php endif; ?>
    <div id="form-container" class="form-container">
        <form action="<?php echo $action; ?>"
              method="post" name="adminForm" id="adminForm">
            <div class="filter-item short-item">
                <?php echo $this->filters['campusID']; ?>
            </div>
            <?php if (!empty($this->filters['subjectID'])): ?>
                <div class="filter-item short-item">
                    <?php echo $this->filters['subjectID']; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($this->filters['status'])): ?>
                <div class="filter-item short-item">
                    <?php echo $this->filters['status']; ?>
                </div>
            <?php endif; ?>
            <input type="hidden" name="languageTag" value="<?php echo $this->shortTag; ?>"/>
        </form>
    </div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th><?php echo $this->lang->_('COM_THM_ORGANIZER_NAME'); ?></th>
            <th><?php echo $this->lang->_('COM_THM_ORGANIZER_DATES'); ?></th>
            <th class='course-state'><?php echo $this->lang->_('COM_THM_ORGANIZER_COURSE_STATE'); ?></th>
            <th class='user-state'><?php echo $this->lang->_('COM_THM_ORGANIZER_REGISTRATION_STATE'); ?></th>
            <th class='registration'></th>
        </tr>
        </thead>
        <tbody>
        <?php echo $this->loadTemplate('list'); ?>
        </tbody>
    </table>
</div>