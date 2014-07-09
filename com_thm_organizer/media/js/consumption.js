/* global $*/

/**
 * Code i part from
 * http://www.kubilayerdogan.net/javascript-export-html-table-to-excel-with-custom-file-name/
 *
 **/

$(document).ready(function ()
{
    "use strict";
    $("#roomsExport").click(function(e)
    {
        downloadTable('rooms');
    });

    $("#teachersExport").click(function(e)
    {
        downloadTable('teachers');
    });

    function downloadTable(type)
    {
        var dt = new Date(),
            day = dt.getDate(),
            month = dt.getMonth() + 1,
            year = dt.getFullYear(),
            hour = dt.getHours(),
            mins = dt.getMinutes(),
            created = day + "." + month + "." + year + "_" + hour + ":" + mins,
            a = document.createElement('a'),
            data_type = 'data:application/vnd.ms-excel',
            divID = 'thm_organizer_' + type + '_consumption_table',
            table_div = document.getElementById(divID),
            table_html = table_div.outerHTML.replace(/ /g, '%20').replace(/ä/g, '&auml;').replace(/Ä/g, '&Auml;').replace(/ö/g, '&ouml;').replace(/Ö/g, '&Ouml;').replace(/ü/g, '&uuml;').replace(/Ü/g, '&uuml;').replace(/ß/g, '&szlig;');

        a.href = data_type + ', ' + table_html;
        a.download = type + '_table_' + created + '.xls';
        a.click();
    }
});
