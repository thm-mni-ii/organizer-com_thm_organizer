<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		subject details view default layout
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
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
$flagPath = 'media' . DIRECTORY_SEPARATOR . 'com_thm_organizer' . DIRECTORY_SEPARATOR . 'images';
$flagPath .= DIRECTORY_SEPARATOR . 'curriculum' . DIRECTORY_SEPARATOR . $this->otherLanguageTag . '.png';
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
if (!empty($subject['prerequisites']))
{
    echo '<div class="subject-item">';
    echo '<div class="subject-label">' . $prerequisites . '</div>';
    echo '<div class="subject-content" id="voraussetzung"><ul>';
    foreach ($subject['prerequisites'] as $prerequisite)
    {
        echo '<li><a href="' . $prerequisite['link'] . '">' . $prerequisite['name'] . '</a></li>';
    }
    echo '</ul></div>';
    echo '</div>';
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
?>
	</dl>
</div>
