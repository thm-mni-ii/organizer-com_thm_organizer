<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view details default
 * @description THM_Curriculum component site view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
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
$references = ($this->lang == 'de')? 'Literatur' : 'References';
$expenditure = ($this->lang == 'de')? 'Aufwand' : 'Expenditure';
$method = ($this->lang == 'de')? 'Lernmethode' : 'Method of Instruction';
$proof = ($this->lang == 'de')? 'Leistungsnachweis' : 'Proof of Performance';
$hours = ($this->lang == 'de')? ' Stunden' : ' Hours';
$frequency = ($this->lang == 'de')? 'Turnus' : 'Frequency';
$language = ($this->lang == 'de')? 'Sprache' : 'Language';
//$description = ($this->lang == 'de')? 'Beschreibung' : 'Description';
//$description = ($this->lang == 'de')? 'Beschreibung' : 'Description';
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
<div class="lsflist">
    <dl class="lsflist">
<?php
if (!empty($subject['externalID']))
{
    echo '<dt class="lsflist">' . $moduleNumber . '</dt>';
    echo '<dd class="lsflist">' . $subject['externalID'] . '</dd>';
}
if (!empty($subject['short_name']))
{
    echo '<dt class="lsflist">' . $shortName . '</dt>';
    echo '<dd class="lsflist">' . $subject['short_name'] . '</dd>';
}
if (!empty($subject['teachers']))
{
    echo '<dt class="lsflist">' . $teachers . '</dt>';
    echo '<dd class="lsflist"><ul>';
    foreach ($subject['teachers'] as $teacher)
    {
        echo '<li>' . $teacher . '</li>';
    }
    echo '</ul></dd>';
}
if (!empty($subject['description']))
{
    echo '<dt class="lsflist">' . $description . '</dt>';
    echo '<dd class="lsflist">' . $subject['description'] . '</dd>';
}
if (!empty($subject['objectives']))
{
    echo '<dt class="lsflist">' . $objectives . '</dt>';
    echo '<dd class="lsflist">' . $subject['objectives'] . '</dd>';
}
if (!empty($subject['contents']))
{
    echo '<dt class="lsflist">' . $contents . '</dt>';
    echo '<dd class="lsflist">' . $subject['contents'] . '</dd>';
}
if (!empty($subject['language']))
{
    echo '<dt class="lsflist hasTip">' . $language . '</dt>';
    echo '<dd class="lsflist">' . ($subject['language'] == 'D')? 'Deutsch' : 'English' . '</dd>';
}
if (!empty($subject['expenditure']))
{
    echo '<dt class="lsflist">' . $expenditure . '</dt>';
	echo '<dd class="lsflist">' . $subject['expenditure'] . $hours . '</dd>';
}
if (!empty($subject['method']))
{
    echo '<dt class="lsflist">' . $method . '</dt>';
	echo '<dd class="lsflist">' . $subject['method'] . '</dd>';
}
if (!empty($subject['preliminary_work']))
{
    echo '<dt class="lsflist">' . $preliminary_work . '</dt>';
	echo '<dd class="lsflist">' . $subject['preliminary_work'] . '</dd>';
}
if ($subject['proof'] != "")
{
    echo '<dt class="lsflist">' . $proof . '</dt>';
    echo '<dd class="lsflist">' . $subject['proof'] . '</dd>';
}
if (!empty($subject['frequency']))
{
    echo '<dt class="lsflist">' . $frequency . '</dt>';
	echo '<dd class="lsflist">' . $subject['frequency'] . '</dd>';
}
if (!empty($subject['modulLiteraturVerzeichnis']))
{
    echo '<dt class="lsflist">' . $references . '</dt>';
    echo '<dd class="lsflist" id="litverz">' . $subject['modulLiteraturVerzeichnis'] . '</dd>';
}
if (!empty($subject['prerequisites']))
{
    echo '<dt class="lsflist">' . $prerequisites . '</dt>';
    echo '<dd class="lsflist" id="voraussetzung"><ul>';
    foreach ($subject['prerequisites'] as $prerequisite)
    {
        echo '<li>' . $prerequisite . '</li>';
    }
    echo '</ul></dd>';
}
?>
	</dl>
</div>
