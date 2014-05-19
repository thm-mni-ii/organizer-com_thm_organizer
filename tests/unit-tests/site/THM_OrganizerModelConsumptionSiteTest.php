<?php
/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    JoomlaFramework
 */

// Complusoft JoomlaTeam - Support: JoomlaTeam@Complusoft.es
require_once JPATH_BASE . '/components/com_thm_organizer/models/consumption.php';
/**
 * Test class for THM_OrganizerModelConsumption
 *
 * @package  thm_organizer
 */
class THM_OrganizerModelConsumptionSiteTest extends TestCaseDatabase {
    /**
     *
     * @var THM_OrganizerModelConsumption
     * @access protected
     */
    protected $object;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     * 
     * @return  null
     */
    protected function setUp()
    {
        parent::setup();
        $connect = parent::getConnection();
        $assets = $this->getDataSet();
        $this->_db = JFactory::getDbo();
        $this->object = new THM_OrganizerModelConsumption;
    }
    
    /**
     * Gets the data set to be loaded into the database during setup
     *
     * @return xml dataset
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(JPATH_TEST_DATABASE . '/jos_thm_organizer_schedules.xml');
    }
    
    /**
     * Method to test the getSchedulesFromDB function
     * 
     * @return null
     */
    public function testgetSchedulesFromDB()
    {
        $expectedObject = new stdClass;
        $expected = array();
        
        $expectedObject->id = "122";
        $expectedObject->departmentname = "MNI";
        $expectedObject->semestername = "WS";
        $expectedObject->creationdate = "09.12.2013";
        $expectedObject->description = "";
        $expectedObject->active = "1";
        $expectedObject->startdate = "07.10.2013";
        $expectedObject->enddate = "05.10.2014";
        $expectedObject->creationtime = "17:00";
        
        array_push($expected, $expectedObject);
        
        $expectedObject = new stdClass;
        
        $expectedObject->id = "123";
        $expectedObject->departmentname = "W";
        $expectedObject->semestername = "SS";
        $expectedObject->creationdate = "01.04.2014";
        $expectedObject->description = "";
        $expectedObject->active = "0";
        $expectedObject->startdate = "07.10.2013";
        $expectedObject->enddate = "05.10.2014";
        $expectedObject->creationtime = "12:05";
        
        array_push($expected, $expectedObject);

        $actual = $this->object->getSchedulesFromDB();
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * Method to test the getScheduleFromDB function
     *
     * @return null
     */
    public function testgetScheduleFromDB()
    {
        $expected = JPATH_TEST_STUBS . '/MNI_WS_schedule.json';
        $actual = $this->object->getScheduleJSONFromDB(122);
        
        $this->assertJsonStringEqualsJsonFile($expected, $actual->schedule);
    }
    
    /**
     * Method to test the getRoomConsumptionFromSchedule function
     *
     * @return null
     */
    public function testgetConsumptionFromSchedule()
    {
        $expected = JPATH_TEST_STUBS . '/MNI_WS_consumption.json';
        $actual = $this->object->getConsumptionFromSchedule(json_decode(file_get_contents(JPATH_TEST_STUBS . '/MNI_WS_schedule.json')));
                
        $this->assertObjectHasAttribute('rooms', $actual);
        $this->assertObjectHasAttribute('teachers', $actual);

        $this->assertJsonStringEqualsJsonFile($expected, json_encode($actual));
    }
    
    /**
     * Method to test the getConsumptionTable function
     *
     * @return null
     */
    public function testgetConsumptionTable()
    {
        $expectedTeacherTable = file_get_contents(JPATH_TEST_STUBS . '/MNI_WS_Teacher_Table.txt');
        $expectedRoomTable = file_get_contents(JPATH_TEST_STUBS . '/MNI_WS_Room_Table.txt');
        
        $consumptions = json_decode(file_get_contents(JPATH_TEST_STUBS . '/MNI_WS_consumption.json'));
        $schedule = json_decode(file_get_contents(JPATH_TEST_STUBS . '/MNI_WS_schedule.json'));

        /**
         * Room consumption
         */
        $roomColumns = array_keys(get_object_vars($consumptions->rooms));
        
        $roomRows = array();
        
        foreach ($consumptions->rooms as $rooms)
        {
            $roomRows = array_merge($roomRows, get_object_vars($rooms));
        }
        
        $actualRoomTable = $this->object->getConsumptionTable($roomColumns, $roomRows, $consumptions, "rooms", $schedule);
                
        
        $this->assertEquals($expectedRoomTable, $actualRoomTable);
        
        /**
         * Teacher consumption
        */
        $teacherColumns = array_keys(get_object_vars($consumptions->teachers));
        
        $teacherRows = array();
        
        foreach ($consumptions->teachers as $teachers)
        {
            $teacherRows = array_merge($teacherRows, get_object_vars($teachers));
        }
        
        $actualTeacherTable = $this->object->getConsumptionTable($teacherColumns, $teacherRows, $consumptions, "teachers", $schedule);

        $this->assertEquals($expectedTeacherTable, $actualTeacherTable);
    }
    
    /**
     * Method to test the getDegreesLongname function
     *
     * @return null
     */
    public function testgetDegreesLongname()
    {
        $expected = array("BI" => "Bioinformatik (B.Sc.)",
                            "MI" => "Medizinische Informatik (B.Sc.)",
                            "I.B" => "Informatik (B.Sc.)",
                            "II" => "Ingenieur-Informatik (B.Sc.)",
                            "BI.M" => "Bioinformatik (M.Sc.)",
                            "I.M" => "Informatik (M.Sc.)",
                            "DIV" => "Diverse Veranstaltungen",
                            "TRMD" => "Technische Redaktion & Multimedia Dokumentation (M.A.)"
                    );
        $consumptions = json_decode(file_get_contents(JPATH_TEST_STUBS . '/MNI_WS_consumption.json'));
        $schedule = json_decode(file_get_contents(JPATH_TEST_STUBS . '/MNI_WS_schedule.json'));
        $roomDegrees = array_keys(get_object_vars($consumptions->rooms));
        $teacherDegrees = array_keys(get_object_vars($consumptions->teachers));
        
        $actual = $this->object->getDegreesLongname($roomDegrees, $schedule);
        
        $this->assertEquals($expected, $actual);
        
        $actual = $this->object->getDegreesLongname($teacherDegrees, $schedule);
        
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * Method to test the getDegreesLongname function
     *
     * @return null
     */
    public function testgetRoomsLongname()
    {
        $expected = array("A20.1.08" => "A20.1.08",
                            "A20.1.36" => "A20.1.36",
                            "A20.0.09" => "A20.0.09",
                            "A20.0.07" => "A20.0.07",
                            "A10.4.20" => "A.4.20",
                            "A20.2.09" => "A20.2.09",
                            "A20.1.09" => "A20.1.09",
                            "A12.2.09" => "A12.2.09",
                            "Online" => "Online",
                            "B14.3.15" => "B14.3.15",
                            "A10.2.01" => "A.2.01",
                            "A20.0.08" => "A20.0.08",
                            "B10.2.23" => "B10.2.23",
                            "A20.2.10" => "A20.2.10",
                            "B14.3.13" => "B14.3.13",
                            "A12.1.16" => "A12.1.16",
                            "A12.1.11" => "A12.1.11",
                            "A20.1.07" => "A20.1.07",
                            "B14.3.14" => "B14.3.14",
                            "A15.1.04A" => "A15.1.04A",
                            "KH" => "Klinik",
                            "A10.8.15" => "A.8.15",
                            "A12.0.11" => "A12.0.11",
                            "A20.2.08" => "A20.2.08",
                            "C13.1.12" => "C13.1.12",
                            "A12.0.12" => "A12.0.12",
                            "B14.3.05" => "B14.3.05"
                    );
        $consumptions = json_decode(file_get_contents(JPATH_TEST_STUBS . '/MNI_WS_consumption.json'));
        $schedule = json_decode(file_get_contents(JPATH_TEST_STUBS . '/MNI_WS_schedule.json'));
        
        $roomRows = array();
        
        foreach ($consumptions->rooms as $rooms)
        {
            $roomRows = array_merge($roomRows, get_object_vars($rooms));
        }
        
        $actual = $this->object->getRoomsLongname(array_keys($roomRows), $schedule);
                
        $this->assertEquals($expected, $actual);
    }
    
    /**
     * Method to test the getTeachersLongname function
     *
     * @return null
     */
    public function testgetTeachersLongname()
    {
        $expected = array("DIV" => "Diverse, ",
                            "MetzHR" => "Metz, Hans-Rudolf",
                            "BachW" => "Bachmann, Walter",
                            "ChriA" => "Christidis, Aris",
                            "MüllB" => "Müller, Bernd",
                            "FröhC" => "Fröhlich, Christine",
                            "FranB" => "Franzen, Berthold",
                            "SchmW" => "Schmitt, Wolfgang",
                            "VolkPC" => "Volkmer, Paul-Christian",
                            "HoboU" => "Hobohm, Uwe",
                            "QuibCK" => "Quibeldey-Cirkel, Klaus",
                            "HerlO" => "Herling, Otfried",
                            "CemiF" => "Cemic, Franz",
                            "OlthM" => "Olthoff, Mark",
                            "RüweD" => "Rüweler, Dörte",
                            "KrümN" => "Krümmel, Nadja",
                            "KneiP" => "Kneisel, Peter",
                            "Reck" => "Recker, Frank",
                            "LetsTK" => "Letschert, Thomas Karl",
                            "SchöC" => "Schölzel, Christopher",
                            "SchuLA" => "Schumann, Axel",
                            "RuppJ" => "Rupp, Steffen",
                            "GeisH" => "Geisse, Hellwig",
                            "LöffP" => "Löffler, Peter",
                            "RinnK" => "Rinn, Klaus",
                            "DomiA" => "Dominik, Andreas",
                            "SennI" => "Senner, Ivo",
                            "MüllF" => "Müller, Fabian",
                            "KlemV" => "Klement, Volker",
                            "SüßS" => "Süß, Sebastian",
                            "MartW" => "Martin, Wolfgang",
                            "KaufAH" => "Kaufmann, Achim Hubert",
                            "FrieT" => "Friedl, Thomas",
                            "SohrK" => "Sohrabi, Keywan",
                            "LidwM" => "Lidwin, Michael",
                            "ReitA" => "Reiter, Anne",
                            "WüstK" => "Wüst, Klaus",
                            "KaisM" => "Kaiser, Markus",
                            "SchnH" => "Schneider, Henning",
                            "SchwBH" => "Schwarz, Björn Helge",
                            "TerbM" => "Terber, Matthias",
                            "HameR" => "Hamel, Reinhard",
                            "BramM" => "Bramwell, Mark",
                            "BöhmM" => "Böhm, Matthias",
                            "AmanA" => "Amanullah, Ahsan",
                            "UlbrN" => "Ulbrich, Norman",
                            "SeitC" => "Seitz, Christian",
                            "ThurL" => "Thursar, Lars",
                            "LiebJ" => "Liebehenschel, Jens",
                            "JullN" => "Jullmann, Nicholas",
                            "SimoN" => "Simonis, Niklas",
                            "KreuM" => "Kreutzer, Michael",
                            "JustB" => "Just, Bettina",
                            "RenzB" => "Renz, Burkhardt",
                            "ScheM" => "Scheer, Manfred",
                            "ThelC" => "Thelen, Christopf",
                            "ScheT" => "Scheuerl, Thomas",
                            "GroßV" => "Groß, Volker",
                            "WaleC" => "Walesch, Christoph",
                            "EichL" => "Eichner, Lutz",
                            "WeißA" => "Weißflog, Andreas",
                            "MöllS" => "Möllenbeck, Sascha",
                            "KarrM" => "Karry, Martin",
                            "MayP" => "May, Peter",
                            "SchmV" => "Schmidt, Volker",
                            "RoosA" => "Roos, Anke",
                            "DworA" => "Dworschak, Alexander",
                            "VogeRB" => "Voges, Rainer Bernd",
                            "LidwA" => "Lidwin, Adrian"
                    );
        $consumptions = json_decode(file_get_contents(JPATH_TEST_STUBS . '/MNI_WS_consumption.json'));
        $schedule = json_decode(file_get_contents(JPATH_TEST_STUBS . '/MNI_WS_schedule.json'));
        
        $teacherRows = array();
        
        foreach ($consumptions->teachers as $teacher)
        {
            $teacherRows = array_merge($teacherRows, get_object_vars($teacher));
        }
        
        $actual = $this->object->getTeachersLongname(array_keys($teacherRows), $schedule);
        
        $this->assertEquals($expected, $actual);
    }
}