<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        PDFBauer
 * @description PDFBauer file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once dirname(__FILE__) . "/AbstractBuilder.php";
require_once dirname(__FILE__) . "/mySched_pdf.php";

/**
 * Class PDFBauer for component com_thm_organizer
 * Class provides methods to create a schedule in pdf format
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class THMPDFBuilder extends THMAbstractBuilder
{
    /**
     * Config
     *
     * @var    Object
     */
    private $_cfg = null;

    /**
     * Constructor with the configuration object
     *
     * @param   Object  $cfg      An object which has configurations including
     * @param   Object  $options  An object which has options including
     */
    public function __construct($cfg, $options)
    {
        $this->_cfg = $cfg;
        $this->startdate = $options["startdate"];
        $this->enddate = $options["enddate"];
        $this->semesterID = $options["semesterID"];
    }

    /**
     * Method to create a ical schedule
     *
     * @param   Object  $scheduleData  The event object
     * @param   String  $username      The current logged in username
     * @param   String  $title         The schedule title
     *
     * @return Array An array with information about the status of the creation
     */
    public function createSchedule($scheduleData, $username, $title)
    {
        // Default angaben fuer Header, Zellen und Tabelle definieren
        $headerSettings = array(
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
        $dataSettings   = array(
             'T_COLOR' => array(
                     0,
                     0,
                     0
             ),
                'T_SIZE' => 11,
                'T_FONT' => 'Arial',
                'T_ALIGN' => 'C',
                'V_ALIGN' => 'M',
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
        $tableSettings  = array(
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
            $path = "";

            if (!$title)
            {
                $title = 'stundenplan';
            }

            if ($title == JText::_("COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE") && $username != "")
            {
                $title = $username . " - " . $title;
            }

            if ($username != "" && $this->_cfg->syncFiles == 1)
            {
                if (!is_dir($this->_cfg->pdf_downloadFolder . $path))
                {
                    // Ordner erstellen
                    @mkdir($this->_cfg->pdf_downloadFolder . $path, 0700);
                }
            }

            $pdfLink = $this->_cfg->pdf_downloadFolder . $path . $title . '.pdf';

            // Array um Wochentage in spalten zu mappen
            $assign = array(
                    'monday' => 1,
                    'tuesday' => 2,
                    'wednesday' => 3,
                    'thursday' => 4,
                    'friday' => 5,
                    'saturday' => 6
            );

            $scheduleGridLength = count((array) $scheduleData->grid);

            // +1 for the time column
            $daysPerWeek = 7;

            if ($scheduleData->daysPerWeek == "1")
            {
                $daysPerWeek = 6;
            }

            // Creates an empty table as a template, otherwise the frame border widths are inconsistent
            $dummy = array_fill(0, $daysPerWeek, array());
            $sched = array_fill(0, $scheduleGridLength, $dummy);

            for ($index = 0; $index < $scheduleGridLength; $index++)
            {
                // Create text for the time column
                $sched[$index][0]["TEXT"] = substr_replace($scheduleData->grid->{$index + 1}->starttime, ":", 2, 0);
                $sched[$index][0]["TEXT"] .= "\n-\n";
                $sched[$index][0]["TEXT"] .= substr_replace($scheduleData->grid->{$index + 1}->endtime, ":", 2, 0);
            }

            // For the lunchtime
            array_splice($sched, 3, 0, array($dummy));
            $sched[3][0]["TEXT"] = " ";

            if (isset($scheduleData->data[0]->htmlView))
            {
                $lessons = $scheduleData[0]->htmlView;
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
                $lessons = $scheduleData->data;
 
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

            // PDF Anlegen
            $pdf = new MySchedPdf($title);
            $pdf->SetAutoPageBreak(true, 13);
            $pdf->SetTopMargin(8);
            $pdf->AddPage('L');
            $columns = $daysPerWeek;

            // Styles fuer die Formatierung-Tags setzten
            $pdf->SetStyle("b", "arial", "b", 10, "0, 0, 0");
            $pdf->SetStyle("i", "arial", "I", 10, "0, 0, 0");
            $pdf->SetStyle("small", "arial", "", 8, "0, 0, 0");

            // Tabelle initialisieren
            $pdf->Table_Init($columns, true, true);

            // Formatierung fuer die Tabelle setzen
            $pdf->Set_Table_Type($tableSettings);

            // Default-Formatierung fuer den Header setzen
            for ($i = 0; $i < $columns; $i++)
            {
                $header_type[$i] = $headerSettings;
            }

            // Breite und Text des Headers setzten
            $header_type[0]['WIDTH'] = 20;

            if ($scheduleData->daysPerWeek == "0")
            {
                $header_type[1]['WIDTH'] = 45;
                $header_type[2]['WIDTH'] = 45;
                $header_type[3]['WIDTH'] = 45;
                $header_type[4]['WIDTH'] = 45;
                $header_type[5]['WIDTH'] = 45;
                $header_type[6]['WIDTH'] = 45;
                $header_type[6]['TEXT']  = JText::_("SATURDAY");
            }
            else
            {
                $header_type[1]['WIDTH'] = 50;
                $header_type[2]['WIDTH'] = 50;
                $header_type[3]['WIDTH'] = 50;
                $header_type[4]['WIDTH'] = 50;
                $header_type[5]['WIDTH'] = 50;
            }

            $header_type[0]['TEXT']  = JText::_("COM_THM_ORGANIZER_SCHEDULER_TIME");

            // These are falsely flagged in the metrics. The coding standard needs to be extended for them.
            $header_type[1]['TEXT']  = JText::_("MONDAY");
            $header_type[2]['TEXT']  = JText::_("TUESDAY");
            $header_type[3]['TEXT']  = JText::_("WEDNESDAY");
            $header_type[4]['TEXT']  = JText::_("THURSDAY");
            $header_type[5]['TEXT']  = JText::_("FRIDAY");
            $pdf->Set_Header_Type($header_type);
            $pdf->Draw_Header();

            // Default-Formatierung fuer die Daten Zellen setzen
            $data_subtype = $dataSettings;

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
                0, $columns, array(
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

            // The document will be saved locally
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
}
