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
        //just in case, prevent default behaviour
        e.preventDefault();
    });

    $("#teachersExport").click(function(e)
    {
        downloadTable('teachers');
        //just in case, prevent default behaviour
        e.preventDefault();
    });

    function downloadTable(type)
    {
        var dt = new Date(),
            day = dt.getDate(),
            month = dt.getMonth() + 1,
            year = dt.getFullYear(),
            hour = dt.getHours(),
            mins = dt.getMinutes(),
            created = day + "-" + month + "-" + year + "_" + hour + "-" + mins,
            divID = 'thm_organizer-' + type + '-consumption-table',
            sheetName = type + '-' + created;

        var tableToExcel = (function () {
            var uri = 'data:application/vnd.ms-excel;base64,'
                , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
                , base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
                , format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
            return function (table, name, filename) {
                if (!table.nodeType)
                {
                    table = document.getElementById(table)
                }
                var ctx = { worksheet: name || 'Worksheet', table: table.innerHTML }

                if($.isFunction(window.navigator.msSaveOrOpenBlob))
                {
                    // IE
                    var fileData = [format(template, ctx)];
                    var blobObject = new Blob(fileData);
                    window.navigator.msSaveOrOpenBlob(blobObject, filename);
                }
                else if (navigator.userAgent.indexOf("Safari") >= 0 && navigator.userAgent.indexOf("OPR") === -1 && navigator.userAgent.indexOf("Chrome") === -1)
                {
                    // Safari
                    // No possibility to define the file name :(
                    window.location.href = uri + base64(format(template, ctx));
                }
                else
                {
                    // Other Browsers
                    document.getElementById("dlink").href = uri + base64(format(template, ctx));
                    document.getElementById("dlink").download = filename;
                    document.getElementById("dlink").click();
                }
            }
        })();

        tableToExcel(divID, type, sheetName + '.xls');
    }

    $('#consumption').keypress(function(e)
    {
        var form = $('#statistic-form');
        if (e.keyCode == 13)
        {
            form.submit();
        }
    });
});

function toggleRooms()
{
    var toggleSpan = $("#filter-room-toggle-image");
    if (toggleSpan.hasClass('toggle-closed'))
    {
        toggleSpan.switchClass('toggle-closed', 'toggle-open');
    }
    else
    {
        toggleSpan.switchClass('toggle-open', 'toggle-closed');
    }
    $("#filter-room").toggle();
}

function toggleTeachers()
{
    var toggleSpan = $("#filter-teacher-toggle-image");
    if (toggleSpan.hasClass('toggle-closed'))
    {
        toggleSpan.switchClass('toggle-closed', 'toggle-open');
    }
    else
    {
        toggleSpan.switchClass('toggle-open', 'toggle-closed');
    }
    $("#filter-teacher").toggle();
}
