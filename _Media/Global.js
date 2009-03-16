
var agt=navigator.userAgent.toLowerCase(); 

// Note: On IE5, these return 4, so use is_ie5up to detect IE5. 
var is_major = parseInt(navigator.appVersion); 
var is_minor = parseFloat(navigator.appVersion); 

var is_nav = ((agt.indexOf('mozilla')!=-1) && (agt.indexOf('spoofer')==-1) && (agt.indexOf('compatible') == -1) && (agt.indexOf('opera')==-1) && (agt.indexOf('webtv')==-1)); 
var is_nav2 = (is_nav && (is_major == 2)); 
var is_nav3 = (is_nav && (is_major == 3)); 
var is_nav4 = (is_nav && (is_major == 4)); 
var is_nav4up = (is_nav && (is_major >= 4)); 
var is_navonly = (is_nav && ((agt.indexOf(";nav") != -1) || (agt.indexOf("; nav") != -1))); 
var is_nav5 = (is_nav && (is_major == 5)); 
var is_nav5up = (is_nav && (is_major >= 5)); 

var is_ie  = (agt.indexOf("msie") != -1); 
var is_ie3 = (is_ie && (is_major < 4)); 
var is_ie4 = (is_ie && (is_major == 4) && (agt.indexOf("msie 5.0")==-1) ); 
var is_ie4up = (is_ie && (is_major >= 4)); 
var is_ie5 = (is_ie && (is_major == 4) && (agt.indexOf("msie 5.0")!=-1) ); 
var is_ie5up = (is_ie && !is_ie3 && !is_ie4);

var is_win   = ( (agt.indexOf("win")!=-1) || (agt.indexOf("16bit")!=-1) )





function setCheckboxes(state, form, prefix)
{
    for(x=0; x<form.elements.length; x++) {
        if(form.elements[x].name.indexOf(prefix)!=-1) {
            if(form.elements[x].type=='checkbox') {
                form.elements[x].checked = state;
            }
        }
    }
}


function doSubmit(form, action)
{
	eval('form.action = action');
	form.submit();
	return true;
}


function doConfirmedSubmit(form, action, msg)
{
    if(confirm(unescape(msg))) {
        form.action = action;
        form.submit();
        return true;
    }
}


function assignConfirmSubmit(form, method, action, msg)
{
    if(confirm(unescape(msg))) {
        form.action = action;
        form._method.value = method;
        form.submit();
        return true;
    }
}



function resetForm(form, submit)
{
    for(x=0; x<form.elements.length; x++) {
        switch(form.elements[x].type) {
            case 'select-one': { form.elements[x].options[0].selected = true; break; }
            case 'checkbox': { form.elements[x].checked = false; break; }
            case 'text': { form.elements[x].value = ''; break; }
        }
    }
    if(submit) { form.submit() };
}




// window object
var w;
function nw(url, title, scrollbars, width, height) {
    if(is_ie4up && is_win) { if(w) { w.close(); } }
    w =  window.open(url, title, "toolbar=0,directories=0,status=0,menubar=0,scrollbars=" + scrollbars + ",resizable=0,width=" + width + ",height=" + height);
    w.focus();
}




function closeWin()
{
    if (w) { w.close(); }
}




function schlaber(position,img)
{
    if (document.images) { document.images[position].src = img.src; }
}