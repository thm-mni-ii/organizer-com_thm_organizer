/* global $*/

/**
 * Code i part from
 * http://www.kubilayerdogan.net/javascript-export-html-table-to-excel-with-custom-file-name/
 *
 **/

$(document).ready(function ()
{
    $("#roomsExport").click(function(e)
    {
        "use strict";
        //getting values of current time for generating the file name
        var dt = new Date();
        var day = dt.getDate();
        var month = dt.getMonth() + 1;
        var year = dt.getFullYear();
        var hour = dt.getHours();
        var mins = dt.getMinutes();
        var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
        //creating a temporary HTML link element (they support setting file names)
        var a = document.createElement('a');
        //getting data from our div that contains the HTML table
        var data_type = 'data:application/vnd.ms-excel';
        var table_div = document.getElementById('thm_organizer_rooms_consumption_table');
        var table_html = table_div.outerHTML.replace(/ /g, '%20').replace(/ä/g, '&auml;').replace(/Ä/g, '&Auml;').replace(/ö/g, '&ouml;').replace(/Ö/g, '&Ouml;').replace(/ü/g, '&uuml;').replace(/Ü/g, '&uuml;').replace(/ß/g, '&szlig;');
        a.href = data_type + ', ' + table_html;
        //setting the file name
        a.download = 'room_table_' + postfix + '.xls';
        //triggering the function
        a.click();
        //just in case, prevent default behaviour
        e.preventDefault();
    });

    $("#teachersExport").click(function(e)
    {
        "use strict";
        //getting values of current time for generating the file name
        var dt = new Date();
        var day = dt.getDate();
        var month = dt.getMonth() + 1;
        var year = dt.getFullYear();
        var hour = dt.getHours();
        var mins = dt.getMinutes();
        var postfix = day + "." + month + "." + year + "_" + hour + "." + mins;
        //creating a temporary HTML link element (they support setting file names)
        var a = document.createElement('a');
        //getting data from our div that contains the HTML table
        var data_type = 'data:application/vnd.ms-excel<base64';
        var table_div = document.getElementById('thm_organizer_teachers_consumption_table');
        var table_html = table_div.outerHTML.replace(/ /g, '%20').replace(/ä/g, '&auml;').replace(/Ä/g, '&Auml;').replace(/ö/g, '&ouml;').replace(/Ö/g, '&Ouml;').replace(/ü/g, '&uuml;').replace(/Ü/g, '&uuml;').replace(/ß/g, '&szlig;');
        a.href = data_type + ', ' + table_html;
        //setting the file name
        a.download = 'teacher_table_' + postfix + '.xls';
        //triggering the function
        a.click();
        //just in case, prevent default behaviour
        e.preventDefault();
    });
});
