<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use THM_OrganizerHelperHTML as HTML;

$casURL         = "document.location.href='index.php?option=com_externallogin&view=server&server=1';return false;";
$containerClass = $this->showRegistration ? ' uses-login' : '';

if (!empty($this->menu)) {
    $menuID   = $this->menu['id'];
    $menuText = Languages::_('THM_ORGANIZER_BACK');
}

$position = OrganizerHelper::getParams()->get('loginPosition');
echo '<div class="toolbar">';
echo $this->languageLinks->render($this->languageParams);
echo '</div>';
echo '<div class="subject-list ' . $containerClass . '">';
if (!empty($this->item['name']['value'])) {
    echo '<h1 class="componentheading">' . $this->item['name']['value'] . '</h1>';
    unset($this->item['name']);
}

if ($this->showRegistration) {
    if (empty(\JFactory::getUser()->id)) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                const registrationLink = jQuery('#login-form ul.unstyled > li:first-child > a'),
                    oldURL = registrationLink.attr('href');

                let queryParams = '&redirect=subject_details';
                queryParams += '&id=<?php echo $this->item['subjectID']; ?>';
                queryParams += '&Itemid=<?php echo $menuID; ?>';
                queryParams += '&languageTag=<?php echo $this->langTag; ?>';

                registrationLink.attr('href', oldURL + queryParams);
            });
        </script>
        <?php
        echo '<div class="tbox-yellow">';
        echo '<p>' . Languages::_('THM_ORGANIZER_COURSE_LOGIN_WARNING') . '</p>';
        echo HTML::_('content.prepare', '{loadposition ' . $position . '}');
        echo '<div class="right">';
        if (!empty($this->menu)) {
            echo '<a href="' . \JRoute::_($this->menu['route'], false) . '" class="btn btn-mini" type="button">';
            echo '<span class="icon-list"></span>' . $menuText . '</a>';
        }
        echo '<a class="btn" onclick="' . $casURL . '">';
        echo '<span class="icon-apply"></span>' . Languages::_('THM_ORGANIZER_COURSE_ADMINISTRATOR_LOGIN');
        echo '</a>';
        echo '</div>';
        echo '<div class="clear"></div>';
        echo '</div>';
    } else {
        echo '<div class="tbox-' . $this->color . ' course-status">';
        echo '<div class="status-container left">';
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
        echo '</div>';
        echo '<div class="right">';
        if (!empty($this->menu)) {
            echo '<a href="' . \JRoute::_($this->menu['route'], false) . '" class="btn btn-mini" type="button">';
            echo '<span class="icon-list"></span>' . $menuText . '</a>';
        }
        echo HTML::_('content.prepare', '{loadposition ' . $position . '}');
        echo '</div>';
        echo '<div class="clear"></div>';
        echo '</div>';
    }
}

if (!empty($this->courses)) {
    if (!empty($this->item['campus']['value'])) {
        $this->renderAttribute('campus', $this->item['campus']);
    }

    $this->renderAttribute('description', $this->item['description']);
} else {
    foreach ($this->item as $key => $data) {
        if (is_array($data)) {
            $this->renderAttribute($key, $data);
        }
    }
    $this->renderCollab();
    echo $this->disclaimer->render([]);
} ?>
</div>
