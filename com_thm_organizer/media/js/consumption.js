/* global $*/

/**
 * Code i part from
 * http://www.kubilayerdogan.net/javascript-export-html-table-to-excel-with-custom-file-name/
 *
 **/



jQuery(document).ready(function ()
{
    // After testing this solution works only with Chrome - tested with Firefox, IE, Chrome
    var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    if (isChrome) {
        fixHeader();
    }

    function fixHeader(){
        jQuery('.consumption-table').scroll(function(){

            if (jQuery('.consumption-table').scrollTop() >= 2) {
                jQuery('.consumption-table').addClass('fixedTop');
                var _top = jQuery(this).scrollTop();
                jQuery('.fixedTop thead th').css('top', _top);
            }
            else {
                jQuery('.consumption-table').removeClass('fixedTop');
            }
            if (jQuery('.consumption-table').scrollLeft() >= 2) {
                jQuery('.consumption-table').addClass('fixedLeft');
                var _left = jQuery(this).scrollLeft();
                jQuery('.fixedLeft tbody tr th:first-child').css('left', _left);
            }
            else {
                jQuery('.consumption-table').removeClass('fixedLeft');
            }
        });

    //create div to hide overlapping at first cell while scrolling and add height and width to this
        jQuery('<div></div>').attr('class', 'whiteSpace').appendTo('body');
        var whiteSpace =  jQuery('.whiteSpace');

        var theadHeight = jQuery('.consumption-table thead').height() - 2;
        whiteSpace.css('height',theadHeight);

        var tbodyFirstWidth = jQuery('.consumption-table tbody tr th').width() + 13;
        whiteSpace.css('width',tbodyFirstWidth);
        positionWhiteSpace();
    }



    "use strict";
    jQuery("#export").click(function(e)
    {
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
            created = day + "-" + month + "-" + year + "_" + hour + "-" + mins,
            divID = 'thm_organizer-consumption-table',
            sheetName = 'ressourcen-stunden-' + created;

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
                if (!table.nodeType) {
                    table = document.getElementById(table);
                }
                var ctx = { worksheet: name || 'Worksheet', table: table.innerHTML };

                if (jQuery.isFunction(window.navigator.msSaveOrOpenBlob)) {
                    // IE
                    var fileData = [format(template, ctx)];
                    var blobObject = new Blob(fileData);
                    window.navigator.msSaveOrOpenBlob(blobObject, filename);
                }
                else if (navigator.userAgent.indexOf("Safari") >= 0 && navigator.userAgent.indexOf("OPR") === -1 && navigator.userAgent.indexOf("Chrome") === -1) {
                    // Safari
                    // No possibility to define the file name :(
                    window.location.href = uri + base64(format(template, ctx));
                }
                else {
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

    jQuery('#consumption').keypress(function(e)
    {
        var form = jQuery('#statistic-form');
        if (e.keyCode === 13)
        {
            form.submit();
        }
    });
});


function toggle()
{
    "use strict";
    var toggleSpan = jQuery("#filter-toggle-image");
    if (toggleSpan.hasClass('toggle-closed'))
    {
        toggleSpan.removeClass('toggle-closed');
        toggleSpan.addClass('toggle-open');
    }
    else
    {
        toggleSpan.removeClass('toggle-open');
        toggleSpan.addClass('toggle-closed');
    }
    jQuery("#filter-resource").toggle();
}


jQuery(document).click(function(e)
{
    if (jQuery('#filter-toggle-image').hasClass("toggle-open")) {
        positionWhiteSpace();
    }
    if (jQuery('#filter-toggle-image').hasClass("toggle-closed")) {
        positionWhiteSpace();
    }
});

function positionWhiteSpace()
{
    if(jQuery('.consumption-table').length)
    {
        var p = jQuery(".consumption-table");
        var offset = p.offset();
        jQuery('.whiteSpace').offset({top: offset.top +1, left: offset.left + 1});
    }
}