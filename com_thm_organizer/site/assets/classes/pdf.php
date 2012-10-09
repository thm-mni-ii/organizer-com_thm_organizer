<?php
/**
 * @version	    v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		PDFBauer
 * @description PDFBauer file from com_thm_organizer
 * @author	    Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */
defined('_JEXEC') or die;

require_once dirname(__FILE__) . "/abstrakterBauer.php";
require_once dirname(__FILE__) . "/mySched_pdf.php";

/**
 * Class PDFBauer for component com_thm_organizer
 *
 * Class provides methods to create a schedule in pdf format
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class PDFBauer extends abstrakterBauer
{
	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 * @since  1.0
	 */
	private $_JDA = null;

	/**
	 * Config
	 *
	 * @var    Object
	 * @since  1.0
	 */
	private $_cfg = null;

	/**
	 * Constructor with the joomla data abstraction object and configuration object
	 *
	 * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
	 * @param   Object	 		 $cfg  A object which has configurations including
	 *
	 * @since  1.5
	 *
	 */
	public function __construct($JDA, $cfg, $options)
	{
		$this->JDA = $JDA;
		$this->cfg = $cfg;
		$this->startdate = $options["startdate"];
		$this->enddate = $options["enddate"];
		$this->semesterID = $options["semesterID"];
	}

	/**
	 * Method to create a ical schedule
	 *
	 * @param   Object  $arr 	   The event object
	 * @param   String  $username  The current logged in username
	 * @param   String  $title 	   The schedule title
	 *
	 * @return Array An array with information about the status of the creation
	 */
	public function erstelleStundenplan($arr, $username, $title)
	{
		// Defaultangaben fuer Header, Zellen und Tabelle definieren
		$table_default_header_type = array(
			 'WIDTH' => 6,
				'T_COLOR' => array(
						80,
						80,
						80
				),
				'T_SIZE' => 14,
				'T_FONT' => 'Arial',
				'T_ALIGN' => 'C',
				'V_ALIGN' => 'T',
				'T_TYPE' => 'B',
				'LN_SIZE' => 7,
				'BG_COLOR' => array(
						255,
						255,
						255
				),
				'BRD_COLOR' => array(
						150,
						150,
						150
				),
				'BRD_SIZE' => 0.1,
				'BRD_TYPE' => '1',
				'BRD_TYPE_NEW_PAGE' => '',
				'TEXT' => ''
		);
		$table_default_data_type   = array(
			 'T_COLOR' => array(
			 		0,
			 		0,
			 		0
			 ),
				'T_SIZE' => 11,
				'T_FONT' => 'Arial',
				'T_ALIGN' => 'C',
				'V_ALIGN' => 'T',
				'T_TYPE' => '',
				'LN_SIZE' => 4,
				'BG_COLOR' => array(
						255,
						255,
						255
				),
				'BRD_COLOR' => array(
						150,
						150,
						150
				),
				'BRD_SIZE' => 0.1,
				'BRD_TYPE' => '1',
				'BRD_TYPE_NEW_PAGE' => ''
		);
		$table_default_table_type  = array(
			 'TB_ALIGN' => 'C',
				'BRD_COLOR' => array(
						150,
						150,
						150
				),
				'BRD_SIZE' => 0.7
		);

		if (isset($username) && isset($title))
		{
			if ($this->cfg['sync_files'] == 1)
			{
				$res = $JDA->query("SELECT registerDate FROM " . $this->cfg['jdb_table_user'] . " WHERE username='" . $username . "'");

				if (count($res) > 0 && trim($username) != "" && trim($username) != "undefined")
				{
					$path = $username . strtotime($res[0]->registerDate) . "/";
				}
				else
				{
					$path = "";
				}
			}
			else
			{
				$path = "";
			}

			if (!$title)
			{
				$title = 'stundenplan';
			}

			if ($title == JText::_("COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE") && $username != "")
			{
				$title = $username . " - " . $title;
			}

			if ($username != "" && $this->cfg['sync_files'] == 1)
			{
				if (!is_dir($this->cfg['pdf_downloadFolder'] . $path))
				{
					// Ordner erstellen
					@mkdir($this->cfg['pdf_downloadFolder'] . $path, 0700);
				}
			}

			// $pdfLink = $this->cfg['pdf_downloadFolder'] . $path . $title . '.pdf';
			$pdfLink = $this->cfg['pdf_downloadFolder'] . $path . $title . '.pdf';

			// Array um Wochentage in spalten zu mappen
			$assign = array(
					'monday' => 1,
					'tuesday' => 2,
					'wednesday' => 3,
					'thursday' => 4,
					'friday' => 5
			);

			// Erstellt Blanko Tabelle als Vorlage (sonst sind rahmen ungleich dick)
			$dummy = array_fill(0, 7, array());
			$sched = array_fill(0, 7, $dummy);

			// Zeitspalte definieren
			$sched[0][0]["TEXT"] = "8:00\n-\n9:30";
			$sched[1][0]["TEXT"] = "9:50\n-\n11:20";
			$sched[2][0]["TEXT"] = "11:30\n-\n13:00";
			$sched[3][0]["TEXT"] = " ";
			$sched[4][0]["TEXT"] = "14:00\n-\n15:30";
			$sched[5][0]["TEXT"] = "15:45\n-\n17:15";
			$sched[6][0]["TEXT"] = "17:30\n-\n19:00";
						
			if (isset($arr[0]->htmlView))
			{
				$lessons = $arr[0]->htmlView;
				foreach ($lessons as $block => $event)
				{
					foreach ($event as $day => $html)
					{
						foreach ($html as $value)
						{
							$cell = "";
							$cell = str_replace('<br/>', "\n", $value);
							$cell = str_replace('<br>', "\n", $cell);
							$cell = strip_tags($cell, "<b><i><small>");
							$cell = preg_replace("/class=\"lecturename_dis\s*\"/", "", $cell);
							$cell = preg_replace("/class=\"lecturename\s*\"/", "", $cell);
							$cell = preg_replace("/class=\"\"\s*/", "", $cell);
							$cell = preg_replace("/class=\"roomshortname\s*\"/", "", $cell);
							$cell = preg_replace("/class=\"oldroom\s*\"/", "", $cell);

							if (is_int($assign[$day]))
							{
								if ($block > 2)
								{
									$sched[$block + 1][$assign[$day]][] = $cell;
								}
							}
							else
							{
								$sched[$block][$assign[$day]][] = $cell;
							}
						}
					}
				}
			}
			else
			{
				$lessons = $arr;
				
// 				var_dump($lessons);
				
				foreach ($lessons as $k => $l)
				{
					if (isset($l->cell))
					{
						$l->cell = str_replace('<br/>', "\n", $l->cell);
						$l->cell = str_replace('<br>', "\n", $l->cell);
						$l->cell = strip_tags($l->cell, "<b><i><small>");
						$l->cell = preg_replace("/class=\"lecturename_dis\s*\"/", "", $l->cell);
						$l->cell = preg_replace("/class=\"lecturename\s*\"/", "", $l->cell);
						$l->cell = preg_replace("/class=\"\"\s*/", "", $l->cell);
						$l->cell = preg_replace("/class=\"roomshortname\s*\"/", "", $l->cell);
						$l->cell = preg_replace("/class=\"oldroom\s*\"/", "", $l->cell);
						
						if (($l->block) > 3)
						{
							$sched[$l->block][$l->dow][] = $l->cell;
						}
						else
						{
							$sched[$l->block - 1][$l->dow][] = $l->cell;
						}
					}
					else
					{

					}
				}
			}
			
			var_dump($sched);
			
			echo print_r($sched, true);

			// PDF Anlegen
			$pdf = new MySchedPdf;
			$pdf->SetAutoPageBreak(true, 13);
			$pdf->SetTopMargin(13);
			$pdf->AddPage('L');
			$columns = 6;

			// Styles fuer die Formatierung-Tags setzten
			$pdf->SetStyle("b", "arial", "b", 10, "0, 0, 0");
			$pdf->SetStyle("i", "arial", "I", 10, "0, 0, 0");
			$pdf->SetStyle("small", "arial", "", 8, "0, 0, 0");

			// Tabelle initialisieren mit 6 Spalten
			$pdf->Table_Init($columns, true, true);

			// Formatierung fuer die Tabelle setzen
			$pdf->Set_Table_Type($table_default_table_type);

			// Default-Formatierung fuer den Header setzen
			$header_subtype = $table_default_header_type;
			for ($i = 0; $i < $columns; $i++)
			{
				$header_type[$i] = $table_default_header_type;
			}

			// Breite und Text des Headers setzten
			$header_type[0]['WIDTH'] = 20;
			$header_type[1]['WIDTH'] = $header_type[2]['WIDTH'] = $header_type[3]['WIDTH'] = $header_type[4]['WIDTH'] = $header_type[5]['WIDTH'] = 50;
			$header_type[0]['TEXT']  = JText::_("COM_THM_ORGANIZER_SCHEDULER_TIME");
			$header_type[1]['TEXT']  = JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_MONDAY");
			$header_type[2]['TEXT']  = JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_TUESDAY");
			$header_type[3]['TEXT']  = JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_WEDNESDAY");
			$header_type[4]['TEXT']  = JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_THURSDAY");
			$header_type[5]['TEXT']  = JText::_("COM_THM_ORGANIZER_SCHEDULER_DAY_FRIDAY");
			$pdf->Set_Header_Type($header_type);
			$pdf->Draw_Header();

			// Default-Formatierung fuer die Daten Zellen setzen
			$data_subtype = $table_default_data_type;

			// Reset the array
			$data_type    = Array();
			for ($i = 0; $i < $columns; $i++)
			{
				$data_type[$i] = $data_subtype;
			}

			// Spezielle eigenschaften fuer die Zeitspalte setzen
			$data_type[0]['V_ALIGN']  = 'M';
			$data_type[0]['T_ALIGN']  = 'C';
			$data_type[0]['T_SIZE']   = '11';
			$data_type[0]['LN_SIZE']  = '5';
			$data_type[0]['BRD_TYPE'] = "LR";
			$pdf->Set_Data_Type($data_type);

			// Definition einer leeren Zeile mit dickerem Rand zum Blocktrennen
			$blankLine = array_fill(
					0, 6, array(
							'LN_SIZE' => 0.1,
							'TEXT' => ' ',
							'BRD_SIZE' => 0.7,
							'BRD_TYPE' => 'T'
					)
			);
			$counter = 0;

			// Daten in Tabelle einfuegen
			ksort($sched);
			foreach ($sched as $line)
			{
				$counter++;

				// Maximale Eintraege pro Zeile ermitteln
				$max = 1;
				foreach ($line as $col)
				{
					if (isset($col['TEXT']))
					{
						continue;
					}
					else
					{

					}

					if (count($col) > $max)
					{
						$max = count($col);
					}
					else
					{

					}
				}

				// Zeichnet abstandslinie
				$pdf->Draw_Data($blankLine);

				// Zellen definieren und fuellen
				for ($i = 0; $i < $max; $i++)
				{
					$data = array();
					foreach ($line as $k => $col)
					{
						if ($counter == 4)
						{
							if (is_int($k))
							{
								$data[$k]['TEXT']    = JText::_("COM_THM_ORGANIZER_SCHEDULER_LUNCHTIME");
								$data[$k]['COLSPAN'] = 7;
							}
						}
						else
						{
							// Textfeld in der Zeitspalte wird besonders behandelt
							if ($i == 0 && $k == 0)
							{
								// Standardbelegung mit einer Lecture
								$data[$k]               = $col;
								$data[$k]['BRD_TYPE'] = "LR";
							}
							elseif (isset($col[$i]))
							{
								$data[$k]['TEXT'] = $col[$i];

								// Wenn nur ein eintrag existiert hat er weder oben noch unten rand
								if ($i == 0 && !isset($col[$i + 1]))
								{
									$data[$k]['BRD_TYPE'] = "LR";
								}
								elseif ($i == 0) // Der erste Eintrag eines Blocks hat oben keinen Rand
								{
									$data[$k]['BRD_TYPE'] = "BLR";
								}
								elseif (!isset($col[$i + 1])) // Die letze Lecture eines Blocks hat keinen Rand unten
								{
									$data[$k]['BRD_TYPE'] = "TLR";
								}
							}
							else // Leeres feld - Simuliertes RowSpanning
							{
								$data[$k]['TEXT']     = ' ';
								$data[$k]['BRD_TYPE'] = 'LR';
							}
						}
					}
					$pdf->Draw_Data($data);
				}
			}

			$pdf->Draw_Table_Border();

			$sporadic = array();

			// Sporadische Veranstaltungen werden
			// als Liste darunter angezeigt
			if (count($sporadic) > 0)
			{
				$pdf->Ln(10);
				$pdf->SetFont('Arial', 'B', 12);
				$pdf->Cell(70, 6, JText::_("COM_THM_ORGANIZER_SCHEDULER_SPORADIC_LESSONS") . ':', 'B', 2);
				$pdf->SetFont('Arial', '', 10);
				$pdf->Ln(3);
				foreach ($sporadic as $l)
				{
					$pdf->Cell(0, 5, $l, 0, 2);
				}
			}

			// Dokument wird lokal gespeichern
			@$pdf->Output($pdfLink, 'F');

			if (is_file($pdfLink))
			{
				return array("success" => true, "data" => JText::_("COM_THM_ORGANIZER_SCHEDULER_FILE_CREATED"));
			}
			else
			{
				return array("success" => false, "data" => JText::_("COM_THM_ORGANIZER_SCHEDULER_NO_FILE_CREATED"));
			}
		}
	}
	
	private function getLessonData()
	{
		if(is_string($this->startdate) && is_string($this->enddate))
		{
			$startDate = new DateTime($this->startdate);
			$endDate = new DateTime($this->enddate);
			$currentDate = $startDate;
			$lessonDates = array();
		
			if($startDate > $endDate)
			{
				return JError::raiseWarning(404, JText::_('Das Enddatum muss größer als das Startdatum sein'));
			}
		
			$calendar = $activeScheduleData->calendar;
		
			while($currentDate <= $endDate)
			{
				$date = $currentDate->format('Y-m-d');
				if(isset($calendar->{$date}))
				{
					$lessonDates[$date] = $calendar->{$date};
				}
				$currentDate->add(new DateInterval('P1D'));
			}
		}
		else
		{
			return JError::raiseWarning(404, JText::_('Kein gültiges Datum'));
		}
	}
	
	/**
	 * Method to get schedule by scheduleID
	 *
	 * @param  Integer  $scheduleID  The schedule id  (Default: null)
	 *
	 * @return mixed  The active schedule as object or false
	 */
	private function getSchedule($scheduleID = null)
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);
		$query->select('*');
		$query->from('#__thm_organizer_schedules');
		if ($scheduleID == null || !is_int($scheduleID))
		{
			$query->where('active = 1');
		}
		else
		{
			$query->where('active = ' . $scheduleID);
		}
		$dbo->setQuery($query);
	
		if ($error = $dbo->getErrorMsg())
		{
			return false;
		}
	
		$result = $dbo->loadObject();
	
		if ($result === null)
		{
			return false;
		}
		return $result;
	}
}
