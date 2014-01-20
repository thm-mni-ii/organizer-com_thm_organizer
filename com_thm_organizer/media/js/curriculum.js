/* global XMLHttpRequest, $, document */
/* exported Curriculum */
function Curriculum(parameters) {
    'use strict';

    var self = this;

    self.getData = function () {
        var requestURL = parameters.baseURL, xmlhttp = new XMLHttpRequest();
        requestURL += 'index.php?option=com_thm_organizer&view=curriculum_ajax&task=getCurriculum&format=raw';
        requestURL += '&programID=' + parameters.programID;
        requestURL += '&poolIDs=' + parameters.poolIDs;
        requestURL += '&languageTag=' + parameters.languageTag;

        xmlhttp.open("GET", requestURL, false);
        xmlhttp.send();
        self.data = $.parseJSON(xmlhttp.responseText);
    };

    function setSemesterContainer(pool) {
        var html, CrP;
        if (pool.minCrP !== undefined && pool.minCrP !== pool.maxCrP)
        {
            CrP = ' (' + pool.minCrP + '-' + pool.maxCrP + ' CrP)';
        }
        else
        {
            if (pool.minCrP === pool.maxCrP)
            {
                CrP = ' (' + pool.maxCrP + ' CrP)';
            }
            else
            {
                CrP = '';
            }
        }
        html = '<h3>' + pool.name + CrP + '</h3>';
        $(html).appendTo('#accordion');
        html = '<div class="gridster">';
        html += '<ul id="pool' + pool.id + '"></ul></div>';
        $(html).appendTo('#accordion');
    }

    function getColorClass(colorCode)
    {
        var brightness, red, blue, green;
        red = parseInt(colorCode.substring(0, 2), 16);
        green = parseInt(colorCode.substring(2, 4), 16);
        blue = parseInt(colorCode.substring(4), 16);
        brightness = (red * 299) + (green * 587) + (blue * 114);
        brightness = brightness / 255000;
        if (brightness >= 0.5)
        {
            return "dark-text";
        }
        else 
        {
            return "light-text";
        }
    }

    function addItemToPool(semester) {
        var html, rowNumber = 1, columnNumber = 1, gridster, lastKey = 1,
            teacherPicture, teacherImage;

        if (semester === null) {
            return;
        }

        gridster = $("#pool" + semester.id).gridster({
            avoid_overlapped_widgets: true,
            widget_margins: [parameters.horizontalSpacing, parameters.verticalSpacing],
            widget_base_dimensions: [parameters.itemWidth, parameters.itemHeight]
        }).data('gridster').disable();
        gridster.cols = parameters.rowItems;

        $.each(semester.children, function (key, value) {
            var colorClass;

            // Ignore inherited 'children'
            if (!semester.children.hasOwnProperty(key)) {
                return true;
            }

            while (lastKey < (parseInt(key, 10) - 1)) {
                if (lastKey % parameters.rowItems === 0) {
                    rowNumber = rowNumber + 1;
                }
                columnNumber = lastKey % parameters.rowItems;
                html = '<li class="icon_container empty" data-sizex="1" data-sizey="1" ';
                html += 'data-col="' + columnNumber + '" data-row="' + rowNumber + '">';
                html += '<div class="icon_container_head"></div>';
                html += '<div class="icon_container_body"></div></li>';
                gridster.add_widget(html);
                lastKey = lastKey + 1;
            }

            if (parseInt(key, 10) % parameters.rowItems === 0) {
                rowNumber = rowNumber + 1;
            }
            columnNumber = parseInt(key, 10) % parameters.rowItems;

            colorClass = getColorClass(value.color);
            html = '<li class="icon_container" data-sizex="1" data-sizey="1" ';
            html += 'data-col="' + columnNumber + '" data-row="' + rowNumber + '">';
            html += '<div class="icon_container_head ' + colorClass + '" ';
            html += 'style="background-color: #' + value.color + '">';
            html += '<span>' + value.externalID + ' (' + value.maxCrP + ' CrP)</span>';
            html += '</div>';
            html += '<div class="icon_container_body">';
            if (value.link !== undefined) {
                html += '<a class="curriculumLink" href=' + value.link + ' target="_blank">' + value.name + '</a>';
            } else {
                html +=  value.name;
            }
            html += '</div><div class="itemTools">';
            if (value.teacherName !== undefined) {
                teacherPicture = value.teacherPicture !== undefined && value.teacherPicture !== '' ?
                        value.teacherPicture : parameters.teacherIcon;
                teacherImage = '<img id="teacher' + value.mappingID + '" class="teacherImage" ';
                teacherImage += 'src="' + teacherPicture + '" title="' + value.teacherName + '">';
                if (value.teacherProfileLink !== undefined) {
                    html += '<a href="' + value.teacherProfileLink + '" target="_blank">';
                    html += teacherImage + '</a>';
                } else {
                    html += teacherImage;
                }
            }
            if (value.hasOwnProperty('children')) {
                html += '<a href="#" id="pool' + value.mappingID + 'Link" >';
                html += '<img width="20px" height="20px" src="' + parameters.poolIcon + '"></a>';
                html += '<div id="pool' + value.mappingID + '" class="poolDialog">';
                html += '<ul id="pool' + value.id + '"></ul></div>';
            }
            html += '</div></li>';
            gridster.add_widget(html);
            $('#teacher' + value.mappingID).tooltip();
            if (value.hasOwnProperty('children')) {
                $('#pool' + value.mappingID).dialog({
                    autoOpen: false,
                    resizable: false,
                    draggable: false,
                    title: value.name,
                    center: true
                });
                $('#pool' + value.mappingID + 'Link').click(function (event) {
                    $('#pool' + value.mappingID).dialog("open");
                });
                addItemToPool(value);
            }
            lastKey = parseInt(key, 10);
        });
    }

    function buildPoolList(key, semester) {
        setSemesterContainer(semester);
        addItemToPool(semester);
    }

    self.render = function () {
        $('<span>' + self.data.name + '</span>').appendTo('#programName');
        $('<div id="accordion" class="curriuculum_container"></div>').appendTo('#contentwrapper');
        $.each(self.data.children, buildPoolList);
        $("#accordion").accordion(
            {
                collapsible: true,
                heightStyle: "content"
            });
    };

    function openDialog() {
        var curricula_dialog = document.getElementById('curriculum_dialog-modal');

        if (curricula_dialog) {
            $(curricula_dialog).dialog().open();
        } else {
            // if it does not exist
            $('<div id="curriculum_dialog-modal"><ul id="curriculum_dialog_list"></ul></div>').dialog().open();
        }
    }

    function putPoolIntoDialog(semesterID, poolKey) {
        var index, semester, pool, html;
        $('#curriculum_dialog_list').children().remove();

        $.each(self.data.children, function (key, semester) {
            index = key;
            if (semester.id === semesterID) {
                return;
            }
        });

        if (index === null) {
            return;
        }

        semester = self.data.children[index - 1];
        pool = semester.children[poolKey];

        $.each(pool.children, function (key, value) {
            html = '<li class="icon_container">';
            html += '<div class="icon_container_head" style="background-color: #' + value.color + '">';
            html += '<span>' + value.abbreviation + ' (' + value.creditpoints + ' CrP)</span></div>';
            html += '<div class="icon_container_body">';
            html += '<span>' + value.name + '</span><br/></div></li>';
            $(html).appendTo('#curriculum_dialog_list');
        });
    }

    self.showPool = function (semesterID, poolID) {
        openDialog();
        putPoolIntoDialog(semesterID, poolID);
    };
}
