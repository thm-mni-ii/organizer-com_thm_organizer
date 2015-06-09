/**
 * Created by Lavi on 04.03.2015.
 */
jQuery(document).ready(function ()
{
    getColorAndChangeCSS();

    //Change width of Item if wanted or needed
/*
    var itemWidth = "170px";
    changeWidthOfItem(itemWidth);
*/
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

function changeWidthOfItem(itemWidth)
{
    var x = document.getElementsByClassName("item");
    var i;
    for (i = 0; i < x.length; i++)
    {
        x[i].style.width = (itemWidth);
    }
}