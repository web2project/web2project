
var calendarField = '';
var calWin = null;

function setContacts(contact_id_string) {
	if(!contact_id_string) {
		contact_id_string = '';
	}
	document.getElementById('task_contacts').value = contact_id_string;
}

function submitIt(form){
	// Check the sub forms
	for (var i = 0, i_cmp = subForm.length; i < i_cmp; i++) {
		if (!subForm[i].check())
			return false;
		// Save the subform, this may involve seeding this form
		// with data
		subForm[i].save();
	}
	form.submit();
}

function addUser(form) {
	var fl = form.resources.length -1;
	var au = form.assigned.length -1;
	//gets value of percentage assignment of selected resource
	var perc = form.percentage_assignment.options[form.percentage_assignment.selectedIndex].value;

	var users = 'x';

	//build array of assiged users
	for (au; au > -1; au--) {
		users = users + ',' + form.assigned.options[au].value + ','
	}

	//Pull selected resources and add them to list
	for (fl; fl > -1; fl--) {
		if (form.resources.options[fl].selected && users.indexOf( ',' + form.resources.options[fl].value + ',' ) == -1) {
			t = form.assigned.length;
			opt = new Option( form.resources.options[fl].text+' ['+perc+'%]', form.resources.options[fl].value);
			form.hperc_assign.value += form.resources.options[fl].value+'='+perc+';';
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
			var re = '.*('+selValue+'=[0-9]*;).*';
			var hiddenValue = form.hperc_assign.value;
			if (hiddenValue) {
				var b = hiddenValue.match(re);
				if (b[1]) {
					hiddenValue = hiddenValue.replace(b[1], '');
				}
				form.hperc_assign.value = hiddenValue;
				form.assigned.options[fl] = null;
			}
		}
	}
}

//Check to see if None has been selected.
function checkForTaskDependencyNone(obj){
	var td = obj.length -1;
	for (td; td > -1; td--) {
		if(obj.options[td].value==task_id){
			clearExceptFor(obj, task_id);
			break;
		}
	}
}

//If None has been selected, remove the existing entries.
function clearExceptFor(obj, id){
	var td = obj.length -1;
	for (td; td > -1; td--) {
		if(obj.options[td].value != id){
			obj.options[td]=null;
		}
	}
}

function addTaskDependency(form, datesForm) {
	var at = form.all_tasks.length -1;
	var td = form.task_dependencies.length -1;
	var tasks = 'x';

	//Check to see if None is currently in the dependencies list, and if so, remove it.

	if(td>=0 && form.task_dependencies.options[0].value==task_id) {
		form.task_dependencies.options[0] = null;
		td = form.task_dependencies.length -1;
	}

	//build array of task dependencies
	for (td; td > -1; td--) {
		tasks = tasks + ',' + form.task_dependencies.options[td].value + ',';
	}

	//Pull selected resources and add them to list
	for (at; at > -1; at--) {
		if (form.all_tasks.options[at].selected && tasks.indexOf( ',' + form.all_tasks.options[at].value + ',' ) == -1) {
			t = form.task_dependencies.length;
			opt = new Option( form.all_tasks.options[at].text, form.all_tasks.options[at].value );
			form.task_dependencies.options[t] = opt;
		}
	}
	
	checkForTaskDependencyNone(form.task_dependencies);
}

function removeTaskDependency(form, datesForm) {
	td = form.task_dependencies.length -1;

	for (td; td > -1; td--) {
		if (form.task_dependencies.options[td].selected) {
			form.task_dependencies.options[td] = null;
		}
	}
}

function setAMPM( field) {
	ampm_field = document.getElementById(field.name + '_ampm');
	if (ampm_field) {
		if ( field.value > 11 ){
			ampm_field.value = 'pm';
		} else {
			ampm_field.value = 'am';
		}
	}
}

var hourMSecs = 3600*1000;

/**
* no comment needed
*/
function isInArray(myArray, intValue) {

	for (var i = 0, i_cmp = myArray.length; i < i_cmp; i++) {
		if (myArray[i] == intValue) {
			return true;
		}
	}		
	return false;
}

/**
 * This function turns on/off the End Date field based on the milestone status.
 *   This came about as a result of http://bugs.web2project.net/view.php?id=328
 */
function toggleMilestone() {
    var milestone = document.getElementById('task_milestone').checked;
    if (milestone) {
        //set finish date
        document.getElementById('end_date').value = document.getElementById('start_date').value;
        document.getElementById('task_end_date').value = document.getElementById('task_start_date').value;
        document.getElementById('end_date').disabled = true;
    } else {
        document.getElementById('end_date').disabled = false;
    }
}

/**
* Get the end of the previous working day
*
* @deprecated (not used)
*/
function prev_working_day( dateObj ) {
	while ( ! isInArray(working_days, dateObj.getDay()) || dateObj.getHours() < cal_day_start ||
	      (	dateObj.getHours() == cal_day_start && dateObj.getMinutes() == 0 ) ){

		dateObj.setDate(dateObj.getDate()-1);
		dateObj.setHours( cal_day_end );
		dateObj.setMinutes( 0 );
	}

	return dateObj;
}
/**
* Get the start of the next working day
*
* @deprecated (not used)
*/
function next_working_day( dateObj ) {
	while ( ! isInArray(working_days, dateObj.getDay()) || dateObj.getHours() >= cal_day_end ) {
		dateObj.setDate(dateObj.getDate()+1);
		dateObj.setHours( cal_day_start );
		dateObj.setMinutes( 0 );
	}

	return dateObj;
}
/**
* @modify reason calcFinish does not use time info and working_days array
*
* @deprecated in favor of includes/ajax_functions.php which calculates the finish
*   date on the backend and passes it back via the xajax library.
*/
function calcFinish(f) {
	//var int_st_date = new String(f.task_start_date.value);
	var int_st_date_time = new String(f.task_start_date.value + f.start_hour.value + f.start_minute.value);	
	var int_st_date = int_st_date_time;
	var e = new Date(int_st_date_time.substring(0,4),(int_st_date_time.substring(4,6)-1),int_st_date_time.substring(6,8), int_st_date_time.substring(8,10), int_st_date_time.substring(10,12));

	// The task duration
	var durn = parseFloat(f.task_duration.value);//hours
	var durnType = parseFloat(f.task_duration_type.value); //1 or 24

	//temporary variables
	var inc = Math.floor(durn);
	var hoursToAddToLastDay = 0;
	var hoursToAddToFirstDay = durn;
	var fullWorkingDays = 0;
	var int_st_hour = e.getHours();
	//catch the gap between the working hours and the open hours (like lunch periods)
	var workGap = cal_day_end - cal_day_start - workHours;

	// calculate the number of non-working days
	var k = 7 - working_days.length;

	var durnMins = (durn - inc) * 60;
	if ((e.getMinutes() + durnMins) >= 60) {
		inc++;
	}

	var mins = ( e.getMinutes() + durnMins ) % 60;
	if (mins > 38) {
		e.setMinutes( 45 );
	} else if (mins > 23) {
		e.setMinutes( 30 );
	} else if (mins > 8) {
		e.setMinutes( 15 );
	} else {
		e.setMinutes( 0 );
	}
	
	// jump over to the first working day
	for (var i = 0; i < k; i++){
		if ( !isInArray(working_days, e.getDay()) ) {
			e.setDate(e.getDate() + 1);
		}
	}

    switch(durnType) {
        case 1:         //this handles hours as duration
            hoursToAddToFirstDay = inc;
            if ( e.getHours() + inc > (cal_day_end-workGap) ) {
                hoursToAddToFirstDay = (cal_day_end-workGap) - e.getHours();
            }
            if ( hoursToAddToFirstDay > workHours ) {
                hoursToAddToFirstDay = workHours;
            }
            inc -= hoursToAddToFirstDay;
            hoursToAddToLastDay = inc % workHours;
            fullWorkingDays = Math.floor((inc - hoursToAddToLastDay) / workHours);

            if (hoursToAddToLastDay <= 0 && !(hoursToAddToFirstDay==workHours)) {
                e.setHours(e.getHours()+hoursToAddToFirstDay);
            } else if (hoursToAddToLastDay == 0) {
                e.setHours(e.getHours()+hoursToAddToFirstDay+workGap);
            } else {
                e.setHours(cal_day_start+hoursToAddToLastDay);
                e.setDate(e.getDate() + 1);
            }


            if ((e.getHours() == cal_day_end || (e.getHours() - int_st_hour) == (workHours+workGap)) && mins > 0) {
                e.setDate(e.getDate() + 1);
                e.setHours(cal_day_start);
            }

            f.end_minute.value = (e.getMinutes() < 10 ? '0'+e.getMinutes() : e.getMinutes());

            // boolean for setting later if we just found a non-working day
            // and therefore do not have to add a day in the next loop
            // (which would have caused to not respecting multiple non-working days after each other)
            var g = false;
            for (var i = 0, i_cmp = Math.ceil(fullWorkingDays); i < i_cmp; i++){
                if (!g) {
                    e.setDate(e.getDate() + 1);
                }
                g = false;
                // calculate overriden non-working days
                if ( !isInArray(working_days, e.getDay()) ) {
                    e.setDate(e.getDate() + 1);
                    i--;
                    g = true;
                }
            }
            f.end_hour.value = (e.getHours() < 10 ? '0'+e.getHours() : e.getHours());
            break;
        case 168:
            inc = inc * working_days.length;
            /*
             * This falls through on purpose because it's all the same logic
             *   after the weeks->days conversion immediately above.
             */
        case 24:
            if (e.getHours() == cal_day_start && e.getMinutes() == 0) {
                fullWorkingDays = Math.ceil(inc);
                e.setMinutes( 0 );
            } else {
                fullWorkingDays = Math.ceil(inc)+1;
            }
            // Include start day as a working day (if it is one)
            if ( isInArray(working_days, e.getDay()) ) fullWorkingDays--;

            for (var i = 0; i < fullWorkingDays; i++)
            {
                e.setDate(e.getDate() + 1);
                if ( !isInArray(working_days, e.getDay()) ) i--;
            }

            if (e.getHours() == cal_day_start && e.getMinutes() == 0) {
                e.setHours(cal_day_end);
                f.end_hour.value = cal_day_end;
                f.end_minute.value = '00';
            } else {
                f.end_hour.value = f.start_hour.value;
                f.end_minute.value = f.start_minute.value;
            }
            break;
        case 730:
            var fullWorkingMonths = Math.ceil(inc);
            e.setMinutes( 0 );
            e.setMonth(e.getMonth() + fullWorkingMonths);

            if (e.getHours() == cal_day_start && e.getMinutes() == 0) {
                e.setHours(cal_day_end);
                f.end_hour.value = cal_day_end;
                f.end_minute.value = '00';
            } else {
                f.end_hour.value = f.start_hour.value;
                f.end_minute.value = f.start_minute.value;
            }
            break;
        default:
            alert('This duration type is not recognized.');
    }

	var tz1 = '';
	var tz2 = '';

	// if there was no fullworkingday we have to check whether the end day is a working day 
	// and in the negative case postpone the end date by appropriate days
	for (var i = 0, i_cmp = 7-working_days.length; i < i_cmp; i++){
		// override  possible non-working enddays
		if ( !isInArray(working_days, e.getDay()) ) {
			e.setDate(e.getDate() + 1);
		}
	}

	if ( e.getDate() < 10 ) tz1 = '0';
	if ( (e.getMonth()+1) < 10 ) tz2 = '0';

	f.task_end_date.value = e.getUTCFullYear()+tz2+(e.getMonth()+1)+tz1+e.getDate();
	var url = 'index.php?m=public&a=date_format&dialog=1&field='+f.name+'.end_date&date=' + f.task_end_date.value;
	thread = window.frames['thread']; //document.getElementById('thread');
	thread.location = url;
	setAMPM(f.end_hour);
}

function changeRecordType(value){
	// if the record type is changed, then hide everything
	hideAllRows();
	// and how only those fields needed for the current type
	eval('show'+task_types[value]+'();');
}

var subForm = new Array();

function FormDefinition(id, form, check, save) {
	this.id = id;
	this.form = form;
	this.checkHandler = check;
	this.saveHandler = save;
	this.check = fd_check;
	this.save = fd_save;
	this.submit = fd_submit;
	this.seed = fd_seed;
}

function fd_check()
{
	if (this.checkHandler) {
		return this.checkHandler(this.form);
	} else {
		return true;
	}
}

function fd_save()
{
	if (this.saveHandler) {
		var copy_list = this.saveHandler(this.form);
		return copyForm(this.form, document.getElementById('hiddenSubforms'), copy_list);
	} else {
		return this.form.submit();
	}
}

function fd_submit()
{
	if (this.saveHandler) {
		this.saveHandler(this.form);
	}
	return this.form.submit();
}

function fd_seed()
{
	return copyForm(document.editFrm, this.form);
}

// Sub-form specific functions.
function checkDates(form) {
	if (can_edit_time_information) {
		if (check_task_dates) {
			if (!form.task_start_date.value) {
				alert( task_start_msg );
				form.task_start_date.focus();
				return false;
			}
			if (!form.task_end_date.value) {
				alert( task_end_msg );
				form.task_end_date.focus();
				return false;
			}
		}
		//check if the start date is > then end date
		var int_st_date = new String(form.task_start_date.value + form.start_hour.value + form.start_minute.value);
		var int_en_date = new String(form.task_end_date.value + form.end_hour.value + form.end_minute.value);

		var s = Date.UTC(int_st_date.substring(0,4),(int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10), int_st_date.substring(10,12));
		var e = Date.UTC(int_en_date.substring(0,4),(int_en_date.substring(4,6)-1),int_en_date.substring(6,8), int_en_date.substring(8,10), int_en_date.substring(10,12));
		if ( s > e ) {
			if (form.task_start_date.value && form.task_end_date.value) {
				alert( 'End date is before start date!');
				return false;
			}
		}
	}
	return true;
}

function copyForm(form, to, extras) {
	// Grab all of the elements in the form, and copy them
	// to the main form.  Do not copy hidden fields.
	var h = new HTMLex;
	for (var i = 0, i_cmp = form.elements.length; i < i_cmp; i++) {
		var elem = form.elements[i];
		if (elem.type == 'hidden') {
			// If we have anything in the extras array we check to see if we
			// need to copy it across
			if (!extras) {
				continue;
			}
			var found = false;
			for (var j = 0, j_cmp = extras.length; j < j_cmp; j++) {
				if (extras[j] == elem.name) {
				  found = true;
					break;
				}
			}
			if (! found) {
				continue;
			}
		}
		// Determine the node type, and determine the current value
		switch (elem.type) {
			case 'text':
			case 'hidden':
				to.appendChild(h.addHidden(elem.name, elem.value, elem.type));
				break;
            case 'textarea':
                to.appendChild(h.addHidden(elem.name, elem.value, elem.type));
                var newHidden = document.getElementById(elem.name);
                newHidden.value = elem.value;
                break;
			case 'select-one':
				if (elem.options.length > 0) {
					to.appendChild(h.addHidden(elem.name, elem.options[elem.selectedIndex].value));
				}
				break;
			case 'select-multiple':
				var sel = to.appendChild(h.addSelect(elem.name, false, true));
				for (var x = 0, x_cmp = elem.options.length; x < x_cmp; x++) {
					if (elem.options[x].selected) {
						sel.appendChild(h.addOption(elem.options[x].value, '', true));
					}
				}
				break;
			case 'radio':
			case 'checkbox':
				if (elem.checked) {
					to.appendChild(h.addHidden(elem.name, elem.value));
				}
				break;
		}
	}
	return true;
}

function saveDates(form) {
	if (can_edit_time_information) {
		if ( form.task_start_date.value.length > 0 ) {
			form.task_start_date.value += form.start_hour.value + form.start_minute.value;
		}
		if ( form.task_end_date.value.length > 0 ) {
			form.task_end_date.value += form.end_hour.value + form.end_minute.value;
		}
	}
	

	return new Array('task_start_date', 'task_end_date');
}

function saveDepend(form) {
	var dl = form.task_dependencies.length -1;
    hd = form.hdependencies;
	hd.value = '';
	for (dl; dl > -1; dl--){
		hd.value += form.task_dependencies.options[dl].value + ((dl == 0) ? '' : ',');
	}
    return new Array('hdependencies');;
}

function checkDetail(form) {
	return true;
}

function saveDetail(form) {
	return null;
}

function checkResource(form) {
	return true;
}

function checkDepend(form) {
	return true;
}

function saveResource(form) {
	var fl = form.assigned.length -1;
	ha = form.hassign;
	ha.value = '';
	for (fl; fl > -1; fl--){
		ha.value += form.assigned.options[fl].value + ((fl == 0) ? '' : ',');
	}
	return new Array('hassign', 'hperc_assign');
}