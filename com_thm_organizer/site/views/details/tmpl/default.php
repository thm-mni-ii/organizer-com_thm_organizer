<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view details default
 * @description THM_Curriculum component site view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
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
				echo JText::_('Modulnummer');
			}
			else
			{
				echo JText::_('Course Code');
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
				echo JText::_('Kurzname');
			}
			else
			{
				echo JText::_('Short title');
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
				echo JText::_('Dozenten');
			}
			else
			{
				echo JText::_('Lecturer');
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
				echo JText::_('Kurzbeschreibung');
			}
			else
			{
				echo JText::_('Short description');
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
				echo JText::_('Lernziele');
			}
			else
			{
				echo JText::_('Objectives');
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
				echo JText::_('Lerninhalt');
			}
			else
			{
				echo JText::_('Contents	');
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
				echo JText::_('Moduldauer');
			}
			else
			{
				echo JText::_('Duration');
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
				echo JText::_('Sprache');
			}
			else
			{
				echo JText::_('Language');
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
				echo JText::_('Arbeisaufwand');
			}
			else
			{
				echo JText::_('Credit points');
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
				echo JText::_('Lernformen');
			}
			else
			{
				echo JText::_('Extent and Manner');
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
				echo JText::_('Vorleistung');
			}
			else
			{
				echo JText::_('Requirements');
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
				echo JText::_('Leistungsnachweis');
			}
			else
			{
				echo JText::_('Examination type');
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
				echo JText::_('Turnus');
			}
			else
			{
				echo JText::_('Offer frequency');
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
				echo JText::_('Literatur');
			}
			else
			{
				echo JText::_('References');
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
				echo JText::_('Voraussetzungen');
			}
			else
			{
				echo JText::_('Prerequisites');
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
					$link = "<a href='" . JRoute::_("index.php?option=com_thm_organizer&view=details&layout=default&id=" .
							strtoupper($vorraussetzung)
							. $tmplText
					)
					. "'>" . strtoupper($vorraussetzung) . "</a>&nbsp;";
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
