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


	<?php
	echo $this->modultitel;
	?>
	<span> <a href="<?php echo JRoute::_($this->langUrl); ?>"><img
			class="languageSwitcher" alt="<?php echo $this->langLink; ?>"
			src="components/com_thm_organizer/css/images/<?php echo $this->langLink; ?>.png" />
	</a>
	</span>
</h1>

<div class="lsflist">
	<dl class="lsflist">


		<?php
		if ($this->modulNrMni != "")
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == 'de')
			{
				echo 'Modulnummer';
			}
			else
			{
				echo 'Course Code';
			}
			?>

		</dt>
		<dd class="lsflist">

			<?php
			echo $this->modulNrMni;
			?>
		</dd>
		<?php 
		}
		else
		{

		}
		?>
		<?php
		if ($this->modulKurzname != "")
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == 'de')
			{
				echo 'Kurzname';
			}
			else
			{
				echo 'Short title';
			}
			?>
		</dt>
		<dd class="lsflist">
			<?php
			echo $this->modulKurzname;
			?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->dozenten != "")
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == 'de')
			{
				echo 'Dozenten';
			}
			else
			{
				echo 'Lecturer';
			}
			?>
		</dt>
		<dd class="lsflist">
			<?php
				echo $this->dozenten;
			?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modulKurzbeschreibung != "")
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == 'de')
			{
				echo 'Kurzbeschreibung';
			}
			else
			{
				echo 'Short description';
			}
			?>
		</dt>
		<dd class="lsflist">
			<?php echo $this->modulKurzbeschreibung; ?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modulLernziel != "")
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == 'de')
			{
				echo 'Lernziele';
			}
			else
			{
				echo 'Objectives';
			}
			?>
		</dt>
		<dd class="lsflist">
			<?php
			echo $this->modulLernziel;
			?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modulLerninhalt != "")
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == 'de')
			{
				echo 'Lerninhalt';
			}
			else
			{
				echo 'Contents	';
			}
			?>
		</dt>
		<dd class="lsflist">
			<?php
			echo $this->modulLerninhalt;
			?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modulDauer != "")
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == 'de')
			{
				echo 'Moduldauer';
			}
			else
			{
				echo 'Duration';
			}
			?>
		</dt>
		<dd class="lsflist">
			<?php
			echo $this->modulDauer;
			?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modulSprache != "")
		{
			?>
		<dt class="lsflist hasTip">
			<?php
			if ($this->lang == 'de')
			{
				echo 'Sprache';
			}
			else
			{
				echo 'Language';
			}
			?>
		</dt>
		<dd class="lsflist">
			<?php
			if ($this->modulSprache == "D")
			{
				echo "Deutsch";
			}
			elseif ($this->modulSprache == "E")
			{
				echo "Englisch";
			}
			?>
		</dd>
		<?php
		}
		else
		{

		}?>
		<?php if ($this->modulAufwand != "")
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == "de")
			{
				echo 'Arbeisaufwand';
			}
			else
			{
				echo 'Credit points';
			}
			?>
		</dt>
		<dd class="lsflist">
			<ul>
				<?php
				echo $this->modulAufwand;
				?>
			</ul>
		</dd>
		<?php
}
		else
		{

		}
		?>
		<?php
		if ($this->modulLernform != "")
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == 'de')
			{
				echo 'Lernformen';
			}
			else
			{
				echo 'Extent and Manner';
			}
			?>
		</dt>
		<dd class="lsflist">
			<ul>
				<?php
				echo $this->modulLernform;
				?>
				<br>
				<br>
			</ul>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modulVorleistung != "")
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == 'de')
			{
				echo 'Vorleistung';
			}
			else
			{
				echo 'Requirements';
			}
			?>
		</dt>
		<dd class="lsflist">
			<?php
			echo $this->modulVorleistung;
			?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modulLeistungsnachweis != "")
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == 'de')
			{
				echo 'Leistungsnachweis';
			}
			else
			{
				echo 'Examination type';
			}
			?>
		</dt>
		<dd class="lsflist">
			<ul>
				<?php
				echo $this->modulLeistungsnachweis;
				?>
			</ul>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modulTurnus != 0)
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == 'de')
			{
				echo 'Turnus';
			}
			else
			{
				echo 'Frequency';
			}
			?>
		</dt>
		<dd class="lsflist">
			<ul>
				<?php
				if ($this->lang == 'de')
				{
					echo $this->mappingTurnus_de[(String) $this->modulTurnus];
				}
				else
				{
					echo $this->mappingTurnus_en[(String) $this->modulTurnus];
				}
				?>
			</ul>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modulLiteraturVerzeichnis != "")
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == "de")
			{
				echo 'Literatur';
			}
			else
			{
				echo 'References';
			}
			?>
		</dt>
		<dd class="lsflist" id="litverz">
			<?php
			echo $this->modulLiteraturVerzeichnis;
			?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modulVorraussetzung != "")
		{
			?>
		<dt class="lsflist">
			<?php
			if ($this->lang == 'de')
			{
				echo 'Voraussetzungen';
			}
			else
			{
				echo 'Prerequisites';
			}
			?>
		</dt>
		<dd class="lsflist" id="voraussetzung">
			<?php
			$splitedVorraussetzung = explode(',', $this->modulVorraussetzung);

			$tmplText = null;

			if (JRequest::getString('mysched'))
			{
				$tmplText = "&tmpl=component&mysched=true";
			}


			if (strpos($this->modulVorraussetzung, 'span'))
			{
				$pos = strpos($this->modulVorraussetzung, '<');
				$result = substr($this->modulVorraussetzung, 0, $pos);
				$splitedVorraussetzung = explode(',', $result);

				foreach ($splitedVorraussetzung as $vorraussetzung)
				{
					$url = "index.php?option=com_thm_organizer&view=details&layout=default&id=";
					$href = JRoute::_($url . strtoupper($vorraussetzung) . $tmplText);
					$link = "<a href='$href'>" . strtoupper($vorraussetzung) . "</a>&nbsp;";
					echo $link;
				}
				echo substr($this->modulVorraussetzung, $pos, strlen($this->modulVorraussetzung));
			}
			else
			{
				foreach ($splitedVorraussetzung as $vorraussetzung)
				{
					$link = "<a href='" . JRoute::_("index.php?option=com_thm_organizer&view=details&id=" . strtoupper($vorraussetzung)
							. $tmplText
					)
					. "'>" . strtoupper($vorraussetzung) . "</a>&nbsp;";
					echo $link;
				}
			}
			?>
		</dd>
		<?php
		}
		?>
	</dl>
</div>
