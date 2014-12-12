/*global MySchedLanguage: false */
/*jshint strict: false */
var prediv = document.createElement("div");
prediv.setAttribute("id", "preloadMessage");
var prespan = document.createElement("span");
prespan.setAttribute("id", "preloadMessagetext");
var pretext = document.createTextNode(MySchedLanguage.COM_THM_ORGANIZER_SCHEDULER_PAGE_LOADING + "...");
prespan.appendChild(pretext);
prediv.appendChild(prespan);
document.getElementById("MySchedMainW").appendChild(prediv);


// Created by: Simon Willison | http://simon.incutio.com/

/**
 * TODO I think it is not in use anymore
 * @param func
 */
function addLoadEvent(func)
{
    console.log("preLoadingMessage addLoadEvent: is it in use anymore?");
    var oldonload = window.onload;
    if (typeof window.onload !== 'function ')
    {
        window.onload = func;
    }
    else
    {
        window.onload = function ()
        {
            if (oldonload) {
                oldonload();
            }
            func();
        };
    }
}

addLoadEvent(function ()
{
    console.log("preLoadingMessage addLoadEvent: is it in use anymore?");
    document.getElementById("preloadMessage").style.display = "none";
});