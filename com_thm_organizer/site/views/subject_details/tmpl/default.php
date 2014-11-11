<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        subject details view default layout
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
$subject = $this->subject;
$moduleNumber = ($this->lang == 'de')? 'Modulnummer' : 'Module Number';
$shortName = ($this->lang == 'de')? 'Kurzname' : 'Short Name';
$teachers = ($this->lang == 'de')? 'Dozenten' : 'Taught By';
$description = ($this->lang == 'de')? 'Beschreibung' : 'Description';
$objectives = ($this->lang == 'de')? 'Lernziele' : 'Objectives';
$contents = ($this->lang == 'de')? 'Inhalte' : 'Contents';
$prerequisites = ($this->lang == 'de')? 'Voraussetzungen' : 'Prerequisites';
$prerequisiteOf = ($this->lang == 'de')? 'Voraussetzung von' : 'Prerequisite for';
$references = ($this->lang == 'de')? 'Literatur' : 'References';
$expenditure = ($this->lang == 'de')? 'Aufwand' : 'Expenditure';
$method = ($this->lang == 'de')? 'Lernmethode' : 'Instruction Method';
$proof = ($this->lang == 'de')? 'Leistungsnachweis' : 'Testing Method';
$frequency = ($this->lang == 'de')? 'Turnus' : 'Frequency';
$language = ($this->lang == 'de')? 'Sprache' : 'Language';
$preliminary_work = ($this->lang == 'de')? 'Vorleistung' : 'Requirement';
$aids = ($this->lang == 'de')? 'Studienhilfsmittel' : 'Study Aids';
$evaluation = ($this->lang == 'de')? 'Bewertung' : 'Evaluation';
$semesterHours = ($this->lang == 'de')? 'SWS' : 'Semester Hours';
$expertise = ($this->lang == 'de')? 'Fachkompetenz' : 'Expertise';
$methodCompetence = ($this->lang == 'de')? 'Methodenkompetenz' : 'Method Competence';
$selfCompetence = ($this->lang == 'de')? 'Selbstkompetenz' : 'Self Competence';
$socialCompetence = ($this->lang == 'de')? 'Sozialkompetenz' : 'Social Competence';
$flagPath = 'media' . DIRECTORY_SEPARATOR . 'com_thm_organizer' . DIRECTORY_SEPARATOR . 'images';
$flagPath .= DIRECTORY_SEPARATOR . $this->otherLanguageTag . '.png';
$noStar = JHtml::image(JURI::root() . '/media/com_thm_organizer/images/0stars.png', 'COM_THM_ORGANIZER_ZERO_STARS');
$oneStar = JHtml::image(JURI::root() . '/media/com_thm_organizer/images/1stars.png', 'COM_THM_ORGANIZER_ONE_STAR');
$twoStars = JHtml::image(JURI::root() . '/media/com_thm_organizer/images/2stars.png', 'COM_THM_ORGANIZER_TWO_STARS');
$threeStars = JHtml::image(JURI::root() . '/media/com_thm_organizer/images/3stars.png', 'COM_THM_ORGANIZER_THREE_STARS');
?>
<script type="text/javascript">

    function setLanguage()
    {
        for (i = 0; i < document.getElementsByTagName("span").length; i++) {
            if (document.getElementsByTagName("span")[i].getAttribute("xml:lang")
                    && document.getElementsByTagName("span")[i].getAttribute("xml:lang")!="<?php echo $this->session->get('language'); ?>") {
                document.getElementsByTagName("span")[i].style.display = 'none';
            }
        }
    }
    window.onload = setLanguage;

</script>
<h1 class="componentheading">
    <?php echo $subject['name']; ?>
    <span>
        <a href="<?php echo JRoute::_($this->langUrl); ?>">
            <img class="languageSwitcher"
                 alt="<?php echo $this->otherLanguageTag; ?>"
                 src="<?php echo $flagPath; ?>" />
        </a>
    </span>
</h1>
<div class="subject-list">
<?php
if (!empty($subject['externalID']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $moduleNumber . '</div>';
    echo '<div class="subject-content">' . $subject['externalID'] . '</div>';
    echo '</div>';
}
if (!empty($subject['short_name']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $shortName . '</div>';
    echo '<div class="subject-content">' . $subject['short_name'] . '</div>';
    echo '</div>';
}
if (!empty($subject['teachers']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $teachers . '</div>';
    echo '<div class="subject-content"><ul>';
    foreach ($subject['teachers'] as $teacher)
    {
        echo '<li>';
        if (!empty($teacher['link']))
        {
            echo '<a href="' . $teacher['link'] . '">' . $teacher['name'] . '</a>';
        }
        else
        {
            echo $teacher['name'];
        }
        echo '</li>';
    }
    echo '</ul></div>';
    echo '</div>';
}
if (!empty($subject['description']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $description . '</div>';
    echo '<div class="subject-content">' . $subject['description'] . '</div>';
    echo '</div>';
}
if (!empty($subject['objective']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $objectives . '</div>';
    echo '<div class="subject-content">' . $subject['objective'] . '</div>';
    echo '</div>';
}
if (!empty($subject['content']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $contents . '</div>';
    echo '<div class="subject-content">' . $subject['content'] . '</div>';
    echo '</div>';
}
if ($subject['expertise']  !== null)
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $expertise . '</div>';
    echo '<div class="subject-content">';
    if ($subject['expertise'] == '3')
    {
        echo $threeStars;
    }
    elseif ($subject['expertise'] == '2')
    {
        echo $twoStars;
    }
    elseif ($subject['expertise'] == '1')
    {
        echo $oneStar;
    }
    elseif ($subject['expertise'] == '0')
    {
        echo $noStar;
    }
    echo '</div></div>';
}
if ($subject['method_competence'] !== null)
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $methodCompetence . '</div>';
    echo '<div class="subject-content">';
    if ($subject['method_competence'] == '3')
    {
        echo $threeStars;
    }
    elseif ($subject['method_competence'] == '2')
    {
        echo $twoStars;
    }
    elseif ($subject['method_competence'] == '1')
    {
        echo $oneStar;
    }
    elseif ($subject['method_competence'] == '0')
    {
        echo $noStar;
    }
    echo '</div></div>';
}
if ($subject['social_competence']  !== null)
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $socialCompetence . '</div>';
    echo '<div class="subject-content">';
    if ($subject['social_competence'] == '3')
    {
        echo $threeStars;
    }
    elseif ($subject['social_competence'] == '2')
    {
        echo $twoStars;
    }
    elseif ($subject['social_competence'] == '1')
    {
        echo $oneStar;
    }
    elseif ($subject['social_competence'] == '0')
    {
        echo $noStar;
    }
    echo '</div></div>';
}
if ($subject['self_competence'] !== null)
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $selfCompetence . '</div>';
    echo '<div class="subject-content">';
    if ($subject['self_competence'] == '3')
    {
        echo $threeStars;
    }
    elseif ($subject['self_competence'] == '2')
    {
        echo $twoStars;
    }
    elseif ($subject['self_competence'] == '1')
    {
        echo $oneStar;
    }
    elseif ($subject['self_competence'] == '0')
    {
        echo $noStar;
    }
    echo '</div></div>';
}
if (!empty($subject['language']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $language . '</div>';
    echo '<div class="subject-content">' . ($subject['language'] == 'D')? 'Deutsch' : 'English' . '</div>';
    echo '</div>';
}
if (!empty($subject['expenditureOutput']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $expenditure . '</div>';
    echo '<div class="subject-content">' . $subject['expenditureOutput'] . '</div>';
    echo '</div>';
}
if (!empty($subject['sws']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $semesterHours . '</div>';
    echo '<div class="subject-content">' . $subject['sws'] . '</div>';
    echo '</div>';
}
if (!empty($subject['method']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $method . '</div>';
    echo '<div class="subject-content">' . $subject['method'] . '</div>';
    echo '</div>';
}
if (!empty($subject['preliminary_work']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $preliminary_work . '</div>';
    echo '<div class="subject-content">' . $subject['preliminary_work'] . '</div>';
    echo '</div>';
}
if (!empty($subject['proof']))
{
    echo '<div class="subject-item">';
    $method = empty($subject['pform'])? '' : ' ( ' . $subject['pform'] . ' )';
    echo '<div class="subject-label">' . $proof . '</div>';
    echo '<div class="subject-content">' . $subject['proof'] . $method . '</div>';
    echo '</div>';
}
if (!empty($subject['frequency']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $frequency . '</div>';
    echo '<div class="subject-content">' . $subject['frequency'] . '</div>';
    echo '</div>';
}
if (!empty($subject['literature']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $references . '</div>';
    echo '<div class="subject-content" id="litverz">' . $subject['literature'] . '</div>';
    echo '</div>';
}
if (!empty($subject['aids']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $aids . '</div>';
    echo '<div class="subject-content">' . $subject['aids'] . '</div>';
    echo '</div>';
}
if (!empty($subject['prerequisites']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $prerequisites . '</div>';
    echo '<div class="subject-content" id="voraussetzung">';
    echo $subject['prerequisites'];
    echo '</div></div>';
}
if (!empty($subject['prerequisiteOf']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $prerequisiteOf . '</div>';
    echo '<div class="subject-content" id="voraussetzung"><ul>';
    foreach ($subject['prerequisiteOf'] as $of)
    {
        echo '<li><a href="' . $of['link'] . '">' . $of['name'] . '</a></li>';
    }
    echo '</ul></div>';
    echo '</div>';
}
$displayeCollab = JComponentHelper::getParams('com_thm_organizer')->get('displayeCollabLink');
if (!empty($subject['externalID']) AND !empty($displayeCollab))
{
    $ecollabLink = JComponentHelper::getParams('com_thm_organizer')->get('eCollabLink');
    $ecollabIcon = JURI::root() . 'media/com_thm_organizer/images/icon-32-moodle.png';
    echo '<div class="subject-item">';
    echo '<div class="subject-label">eCollaboration Link</div>';
    echo '<div class="subject-content">';
    echo '<a href="' . $ecollabLink . $subject['externalID'] . '" target="_blank">';
    echo "<img class='eCollabImage' src='$ecollabIcon' title='eCollabLink'></a>";
    echo '</div></div>';
}
?>
    </dl>
</div>
