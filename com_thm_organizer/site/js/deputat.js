jQuery(document).ready(function () {
    'use strict';
    jQuery('#export').click(function (e) {
        downloadTable();
        //just in case, prevent default behaviour
        e.preventDefault();
    });

    function downloadTable()
    {
        var tableToExcel;
        var dt = new Date(),
            day = dt.getDate(),
            month = dt.getMonth() + 1,
            year = dt.getFullYear(),
            hour = dt.getHours(),
            mins = dt.getMinutes(),
            created = day + '-' + month + '-' + year + '_' + hour + '-' + mins,
            divID = 'thm_organizer-consumption-table',
            sheetName = 'deputat-' + created;

        // From http://stackoverflow.com/questions/17126453/html-table-to-excel-javascript (changed slightly)
        tableToExcel = (function () {
            var uri = 'data:application/vnd.ms-excel;base64,',
                template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
                base64 = function (s) {
                    return window.btoa(decodeURIComponent(encodeURIComponent(s)))
                },
                format = function (s, c) {
                    return s.replace(/{(\w+)}/g, function (m, p) {
                        return c[p];
                    })
                };
            return function (table, name, filename) {
                if (!table.nodeType)
                {
                    table = document.getElementById(table);
                }
                var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML};

                if (jQuery.isFunction(window.navigator.msSaveOrOpenBlob))
                {
                    // IE
                    var fileData = [format(template, ctx)];
                    var blobObject = new Blob(fileData);
                    window.navigator.msSaveOrOpenBlob(blobObject, filename);
                }
                else if (navigator.userAgent.indexOf('Safari') >= 0 && navigator.userAgent.indexOf('OPR') === -1 && navigator.userAgent.indexOf('Chrome') === -1)
                {
                    // Safari
                    // No possibility to define the file name :(
                    window.location.href = uri + base64(format(template, ctx));
                }
                else
                {
                    // Other Browsers
                    var downloadLink = document.getElementById('dLink');
                    downloadLink.href = uri + base64(format(template, ctx));
                    downloadLink.download = filename;
                    downloadLink.click();
                }
            };
        })();

        tableToExcel(divID, type, sheetName + '.xls');
    }

    jQuery('#deputat').keypress(function (e) {
        var form = jQuery('#statistic-form');
        if (e.keyCode === 13)
        {
            form.submit();
        }
    });
});

function removeRow(link)
{
    var personID = link.id.split('-')[3], rowNumber = link.id.split('-')[4], rowSum, rowTotal, swsSum, swsSumValue,
        swsTotal, swsTotalValue, tallyBodyExists;

    rowSum = parseInt(jQuery('#row-sws-' + personID + '-' + rowNumber).text());
    rowTotal = parseInt(jQuery('#row-total-' + personID + '-' + rowNumber).text());

    swsSum = jQuery('#sum-sws-' + personID);
    swsSumValue = parseInt(swsSum.text()) - rowSum;

    // The removal does not close out the sum rows
    if (swsSumValue > 0)
    {
        jQuery('#data-row-' + personID + '-' + rowNumber).remove();
        swsSum.text(swsSumValue);

        swsTotal = jQuery('#sum-total-' + personID);
        swsTotalValue = parseInt(swsTotal.text()) - rowTotal;
        swsTotal.text(swsTotalValue);
        return;
    }

    tallyBodyExists = jQuery('#deputat-table-body-tally-' + personID).length;
    if (tallyBodyExists)
    {
        jQuery('#deputat-table-body-sum-' + personID).remove();
        return;
    }

    jQuery('#deputat-table-' + personID).remove();
}