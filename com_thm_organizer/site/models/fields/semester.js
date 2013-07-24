/*globals ShowSubSelect */
/**
 * @version		$Id: menuitemselect.js 229 2009-02-02 23:14:17Z kernelkiller $
 * @package		Joomla
 * @subpackage	GiessenLatestNews
 * @author		Frithjof Kloes
 * @copyright	Copyright (C) 2008 FH Giessen-Friedberg / University of Applied Sciences
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */


// change the sort of the roles, selected role one position higher
function roleup() {
    "use strict";
    var role = document.getElementById('jformparamssemesters');

    // If no Param is selected------------------------------------
    if (role.selectedIndex === -1)
    {
        alert("Bitte Semester auswaehlen");
    }
    else
    {
        var selected = role.selectedIndex;
        var tmpvalue = role.options[selected].value;
        var tmptext = role.options[selected].text;
        document.getElementById('jformparamssemesters').options[selected].value = role.options[selected-1].value;
        document.getElementById('jformparamssemesters').options[selected].text = role.options[selected-1].text;
        document.getElementById('jformparamssemesters').options[selected-1].value = tmpvalue;
        document.getElementById('jformparamssemesters').options[selected-1].text = tmptext;
        document.getElementById('jformparamssemesters').options[selected-1].selected=true;
        document.getElementById('jformparamssemesters').options[selected].selected=false;

        // Write new sorted Roles into hidden paramsfield-------------
        var temp="";
        for(i=0;i<document.getElementById('jformparamssemesters').length;i++) {
            temp += document.getElementById('jformparamssemesters').options[i].value + ',';
        }
        // remove the last char (,) from the string
        temp = temp.substr(0, temp.length-1);
    }
}
// change the sort of the roles, selected role one position down
function roledown()
{
    "use strict";
    var role = document.getElementById('jformparamssemesters');
    // If no Param is selected------------------------------------
    if (role.selectedIndex === -1)
    {
        alert("Bitte Rolle auswaehlen");
    }
    else
    {
        // Change Roles down------------------------------------------
        var selected = role.selectedIndex;
        var tmpvalue = role.options[selected].value;
        //alert(role.value);
        var tmptext = role.options[selected].text;
        document.getElementById('jformparamssemesters').options[selected].value = role.options[selected+1].value
        document.getElementById('jformparamssemesters').options[selected].text = role.options[selected+1].text
        document.getElementById('jformparamssemesters').options[selected+1].value = tmpvalue;
        document.getElementById('jformparamssemesters').options[selected+1].text = tmptext;
        document.getElementById('jformparamssemesters').options[selected+1].selected=true;
        document.getElementById('jformparamssemesters').options[selected].selected=false;
        //------------------------------------------------------------

        // Write new sorted Roles into hidden paramsfield-------------
        var temp="", i;
        for(i = 0; i < document.getElementById('jformparamssemesters').length; i++)
        {
            temp += document.getElementById('jformparamssemesters').options[i].value + ',';
        }
        // remove the last char (,) from the string
        temp = temp.substr(0, temp.length-1);
    }
}

function InitSubSelect()
{
    "use strict";
    // leeres sub-<select> mit mygroup[0] füllen
    ShowSubSelect(document.forms["myform"].elements["myselect"], "mysubselect");
}
