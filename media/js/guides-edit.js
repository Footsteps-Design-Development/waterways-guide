function ValidateForm() {
    var errors = "";
    if (document.form.GuideCountry.value == "All") {
        errors += '- Country\n';
        document.form.GuideCountry.style.backgroundColor = "#ffff00";
    } else {
        document.form.GuideCountry.style.backgroundColor = "#ffffff";
    }
    if (document.form.GuideWaterway.value == "All") {
        errors += '- Waterway\n';
        document.form.GuideWaterway.style.backgroundColor = "#ffff00";
    } else {
        document.form.GuideWaterway.style.backgroundColor = "#ffffff";
    }
    //check waterway for illegal chars
    myStr = document.form.GuideWaterway.value;
    if (myStr.match(/[\<\>!@#\$%^&\*]+/i)) {
        errors += "- Waterway '" + myStr + "' must only contain alpha numeric characters or , . and space\n";
        document.form.GuideWaterway.style.backgroundColor = "#ffff00";
    } else {
        document.form.GuideWaterway.style.backgroundColor = "#ffffff";
    }
    if (document.form.GuideName.value == "") {
        errors += '- Town or locality name\n';
        document.form.GuideName.style.backgroundColor = "#ffff00";
    } else {
        document.form.GuideName.style.backgroundColor = "#ffffff";
    }
    if (document.form.GuideLocation.value == "") {
        errors += '- Location\n';
        document.form.GuideLocation.style.backgroundColor = "#ffff00";
    } else {
        document.form.GuideLocation.style.backgroundColor = "#ffffff";
    }
    if (document.form.GuideRemarks.value == "") {
        errors += '- Remarks\n';
        document.form.GuideRemarks.style.backgroundColor = "#ffff00";
    } else {
        document.form.GuideRemarks.style.backgroundColor = "#ffffff";
    }
    if (errors) {
        alert('Please add some detail to the highlighted entries and try again:\n' + errors);
    } else {
        document.form.submit()
    }
}


function changecode(cbname, code) {


    var cur_str = document.form.GuideCodes.value;
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
    document.form.GuideCodes.value = new_str;
}

function Help(Subject) {
    var mypage = Subject;
    var myname = "help";
    //var w = (screen.width - 100);
    //var h = (screen.height - 100);
    var w = 530;
    var h = 300;
    var scroll = "yes";
    var winl = (screen.width - w) / 2;
    var wint = (screen.height - h) / 2;
    winprops = 'height=' + h + ',width=' + w + ',top=' + wint + ',left=' + winl + ',scrollbars=' + scroll + ',resizable'
    mypage += (mypage.indexOf('?') != -1 ? '&' : '?') + 'nocache=' + Date.now();
    win = window.open(mypage, myname, winprops)
    if (parseInt(navigator.appVersion) >= 4) {
        win.window.focus();
    }
}

function insertcountry(text) {
    var txtarea = document.form.GuideCountry;
    //text = ' ' + text + ' ';
    txtarea.value = text;
    document.form.keywords.options["0"].selected = true;
    txtarea.focus();
}

function insertwaterway(text) {
    var txtarea = document.form.GuideWaterway;
    //text = ' ' + text + ' ';
    txtarea.value = text;
    document.form.keywords.options["0"].selected = true;
    txtarea.focus();
}

function storeCaret(textEl) {
    if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}

function SubmitContent() {
    document.form.upload.src = "/Images/common/livinga22.gif";
    document.form.save.value = 'Please Wait . . . . Updating . .';
    document.form.submit();
}