jQuery(document).ready(function ()
{
    getColorAndChangeCSS();
    positionPopup();
});

// Schlie�e Popup (".modal-panel"), wenn au�erhalb geklickt wird
// Gefundene L�sung: http://stackoverflow.com/a/7385673
jQuery(document).mouseup(function (e)
{
    var modalID = "#" + jQuery('.modal-panel.shown').attr('id');
    if (!jQuery(modalID).is(e.target) // if the target of the click isn't the container...
        && jQuery(modalID).has(e.target).length === 0) // ... nor a descendant of the container
    {
        toggleGroupDisplay(modalID);    //used function of container.js in lib
    }
});

// get background-color of .item-head, get 20% darker color of this and change css properties for boxShadow
function getColorAndChangeCSS()
{
    var x = document.getElementsByClassName("item-head");
    var i;
    for (i = 0; i < x.length; i++)
    {
        var bgColor = jQuery(x[i]).css("background-color");
        var darkerColor = shadeRGBColor(bgColor, -0.2);
        x[i].style.boxShadow = ('inset 0px 2px 10px ' + darkerColor);
    }
}

function shadeRGBColor(color, percent)
{
    var f = color.split(","), t = percent < 0 ? 0 : 255, p = percent < 0 ? percent * -1 : percent, R = parseInt(f[0].slice(4)), G = parseInt(f[1]), B = parseInt(f[2]);
    return "rgb(" + (Math.round((t - R) * p) + R) + "," + (Math.round((t - G) * p) + G) + "," + (Math.round((t - B) * p) + B) + ")";
}

// Position the popup elemts ".modal-panel"
function positionPopup()
{
    var modalpanelClass = document.getElementsByClassName("modal-panel");    //items to position
    var curriculumClass = document.getElementsByClassName("curriculum"); //to position popup over this class "curriculum"
    var i;

    for (i = 0; i < modalpanelClass.length; i++)
    {
        jQuery(modalpanelClass[i]).prependTo(jQuery(curriculumClass[2]));
    }
}
