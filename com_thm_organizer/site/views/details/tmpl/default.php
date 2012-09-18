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
	echo $this->modul->getModultitel();
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
		if ($this->modul->getNrMni() != "")
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
			echo $this->modul->getNrMni();
			?>
		</dd>
		<?php 
		}
		else
		{

		}
		?>
		<?php
		if ($this->modul->getKurzname() != "")
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
			echo $this->modul->getKurzname();
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
		if ($this->modul->getKurzbeschreibung() != "")
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
			<?php echo $this->modul->getKurzbeschreibung(); ?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modul->getLernziel() != "")
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
			echo $this->modul->getLernziel();
			?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modul->getLerninhalt() != "")
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
			echo $this->modul->getLerninhalt();
			?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modul->getDauer() != "")
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
			echo $this->modul->getDauer();
			?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modul->getSprache() != "")
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
			if ($this->modul->getSprache() == "D")
			{
				echo "Deutsch";
			}
			elseif ($this->modul->getSprache() == "E")
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
		<?php if ($this->modul->getAufwand() != "")
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
				echo $this->modul->getAufwand();
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
		if ($this->modul->getLernform() != "")
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
				echo $this->modul->getLernform();
				?>
				<br>
				<br>
			</ul>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modul->getVorleistung() != "")
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
			echo $this->modul->getVorleistung();
			?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modul->getLeistungsnachweis() != "")
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
				echo $this->modul->getLeistungsnachweis();
				?>
			</ul>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modul->getTurnus() != 0)
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
					echo $this->mappingTurnus_de[(String) $this->modul->getTurnus()];
				}
				else
				{
					echo $this->mappingTurnus_en[(String) $this->modul->getTurnus()];
				}
				?>
			</ul>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modul->getLiteraturVerzeichnis() != "")
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
			echo $this->modul->getLiteraturVerzeichnis();
			?>
		</dd>
		<?php
		}
		?>
		<?php
		if ($this->modul->getVorraussetzung() != "")
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
			$splitedVorraussetzung = explode(',', $this->modul->getVorraussetzung());

			$tmplText = null;

			if (JRequest::getString('mysched'))
			{
				$tmplText = "&tmpl=component&mysched=true";
			}


			if (strpos($this->modul->getVorraussetzung(), 'span'))
			{
				$pos = strpos($this->modul->getVorraussetzung(), '<');
				$result = substr($this->modul->getVorraussetzung(), 0, $pos);
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
				echo substr($this->modul->getVorraussetzung(), $pos, strlen($this->modul->getVorraussetzung()));
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
