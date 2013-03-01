var subForm = [];
function FormDefinition(id, form, check, save) {
	this.id = id;
	this.form = form;
	this.check = check;
	this.save = save;
/*	this.check = fd_check;
	this.save = fd_save;
	this.submit = fd_submit;
	this.seed = fd_seed;*/
}
function saveResource(form){
	var editFrm=document.editFrm;
	var users = "";
	var len = form.assigned.length;
	for (var i = 0; i < len; i++) {
		users +=(i?',':'')+form.assigned.options[i].value;
	}
	var h=new HTMLex();
	editFrm.appendChild(h.addHidden('event_assigned',users));
	if (form.mail_invited.checked) {
		editFrm.appendChild(h.addHidden('mail_invited','on'));
	}
}
function checkResource(form){
	// Ensure that the assigned values are selected before submitting.
	var result=false;
	if (form.assigned.length){
		result=true;
	}
	return result;
}
function submitIt() {
	var form = document.editFrm;
	if (form.event_name.value.length < 1) {
		alert(valid_event_title);
		form.event_title.focus();
		return;
	}
	if (form.event_start_date.value.length < 1) {
		alert('<?php echo $AppUI->_("Please enter a start date", UI_OUTPUT_JS); ?>');
		form.event_start_date.focus();
		return;
	}
	if (form.event_end_date.value.length < 1) {
		alert('<?php echo $AppUI->_("Please enter an end date", UI_OUTPUT_JS); ?>');
		form.event_end_date.focus();
		return;
	}
	if ((!(form.event_times_recuring.value>0))
		&& (form.event_recurs[0].selected!=true)) {
		alert("<?php echo $AppUI->_('Please enter number of recurrences', UI_OUTPUT_JS); ?>");
		form.event_times_recuring.value=1;
		form.event_times_recuring.focus();
		return;
	}
	for (var i = 0; i < subForm.length; i++) {
		if (!subForm[i].check(subForm[i].form))
			return false;
		// Save the subform, this may involve seeding this form
		// with data
		subForm[i].save(subForm[i].form);
	}
	form.submit();
}

var calendarField = '';

function popCalendar(field) {
	calendarField = field;
	idate = eval('document.editFrm.event_' + field + '.value');
	window.open('?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=240,scrollbars=no,status=no');
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar(idate, fdate) {
	fld_date = eval('document.editFrm.event_' + calendarField);
	fld_fdate = eval('document.editFrm.' + calendarField);
	fld_date.value = idate;
	fld_fdate.value = fdate;

	// set end date automatically with start date if start date is after end date
	if (calendarField == 'start_date') {
		if (document.editFrm.event_end_date.value < idate) {
			document.editFrm.event_end_date.value = idate;
			document.editFrm.end_date.value = fdate;
		}
	}
}

function addUser(form) {
	var fl = form.resources.length -1;
	var au = form.assigned.length -1;
	//gets value of percentage assignment of selected resource

	var users = "x";

	//build array of assiged users
	for (au; au > -1; au--) {
		users = users + "," + form.assigned.options[au].value + ","
	}

	//Pull selected resources and add them to list
	for (fl; fl > -1; fl--) {
		if (form.resources.options[fl].selected && users.indexOf("," + form.resources.options[fl].value + ",") == -1) {
			t = form.assigned.length
			opt = new Option(form.resources.options[fl].text, form.resources.options[fl].value);
			form.assigned.options[t] = opt
		}
	}

}

function removeUser(form) {
	fl = form.assigned.length -1;
	for (fl; fl > -1; fl--) {
		if (form.assigned.options[fl].selected) {
			//remove from hperc_assign
			var selValue = form.assigned.options[fl].value;
			var re = ".*("+selValue+"=[0-9]*;).*";
			form.assigned.options[fl] = null;
		}
	}
}


