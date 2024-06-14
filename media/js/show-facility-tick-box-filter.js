function showfilter() {
    var x = document.form.filteroption.selectedIndex;
    if (document.form.filteroption.options[x].value == "ALL") {
        document.getElementById("mooringfilter").style.display = 'block';
        document.getElementById("hazardsfilter").style.display = 'block';
    }
    if (document.form.filteroption.options[x].value == "M") {
        document.getElementById("mooringfilter").style.display = 'block';
        document.getElementById("hazardsfilter").style.display = 'none';
    }
    if (document.form.filteroption.options[x].value == "H") {
        document.getElementById("mooringfilter").style.display = 'none';
        document.getElementById("hazardsfilter").style.display = 'block';
    }

}


function changemooringcode(cbname, code) {

    var cur_str = document.form.GuideMooringCodes.value;
    var state = cbname.checked;
    var str_search = "|" + code + "|";
    if (state == 0) {
        //remove it
        if (str_search == cur_str) {
            //only one so make blank
            var new_str = cur_str.replace(str_search, '');
        } else {
            var new_str = cur_str.replace(str_search, '|');
        }
    } else {
        //add it
        if (cur_str) {
            //already some data so add on end
            var new_str = cur_str + code + "|";
        } else {
            var new_str = "|" + code + "|";
        }
    }

    //alert(cur_str+" - "+new_str);
    document.form.GuideMooringCodes.value = new_str;
}

function changehazardcode(cbname, code) {

    var cur_str = document.form.GuideHazardCodes.value;
    var state = cbname.checked;
    var str_search = "|" + code + "|";
    if (state == 0) {
        //remove it
        if (str_search == cur_str) {
            //only one so make blank
            var new_str = cur_str.replace(str_search, '');
        } else {
            var new_str = cur_str.replace(str_search, '|');
        }
    } else {
        //add it
        if (cur_str) {
            //already some data so add on end
            var new_str = cur_str + code + "|";
        } else {
            var new_str = "|" + code + "|";
        }
    }

    //alert(cur_str+" - "+new_str);
    document.form.GuideHazardCodes.value = new_str;
}
