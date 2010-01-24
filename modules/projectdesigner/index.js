/* $Id$ $URL$ */
function update_workspace(id) {
	var tr = document.getElementById(id);
	(tr.style.display == "none") ? eval('document.frmWorkspace.opt_view_'+id+'.value=0') : eval('document.frmWorkspace.opt_view_'+id+'.value=1');      
	(tr.style.display == "none") ? eval('document.editFrm.opt_view_'+id+'.value=0') : eval('document.editFrm.opt_view_'+id+'.value=1');      
	(tr.style.display == "none") ? eval('document.frm_bulk.opt_view_'+id+'.value=0') : eval('document.frm_bulk.opt_view_'+id+'.value=1');      
}

function expandAll() {
      expand_collapse('project', 'tblProjects', 'expand');
      expand_collapse('gantt', 'tblProjects', 'expand');
      expand_collapse('tasks', 'tblProjects', 'expand');
      expand_collapse('actions', 'tblProjects', 'expand');
      expand_collapse('addtsks', 'tblProjects', 'expand');
      expand_collapse('files', 'tblProjects', 'expand');
      update_workspace('project');
      update_workspace('gantt');
      update_workspace('tasks');
      update_workspace('actions');
      update_workspace('addtsks');
      update_workspace('files');
}

function collapseAll() {
      expand_collapse('project', 'tblProjects', 'collapse');
      expand_collapse('gantt', 'tblProjects', 'collapse');
      expand_collapse('tasks', 'tblProjects', 'collapse');
      expand_collapse('actions', 'tblProjects', 'collapse');
      expand_collapse('addtsks', 'tblProjects', 'collapse');
      expand_collapse('files', 'tblProjects', 'collapse');
      update_workspace('project');
      update_workspace('gantt');
      update_workspace('tasks');
      update_workspace('actions');
      update_workspace('addtsks');
      update_workspace('files');
}

/**
* @modify_reason calculating duration does not include time information and cal_working_days stored in config.php
*/
function calcDuration(form,start_date,end_date,duration_fld,durntype_fld) {

	var int_st_date = new String(start_date.value);
	var int_en_date = new String(end_date.value);

	var sDate = new Date(int_st_date.substring(0,4),(int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10), int_st_date.substring(10,12));
	var eDate = new Date(int_en_date.substring(0,4),(int_en_date.substring(4,6)-1),int_en_date.substring(6,8), int_en_date.substring(8,10), int_en_date.substring(10,12));
	var s = Date.UTC(int_st_date.substring(0,4),(int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10), int_st_date.substring(10,12));
	var e = Date.UTC(int_en_date.substring(0,4),(int_en_date.substring(4,6)-1),int_en_date.substring(6,8), int_en_date.substring(8,10), int_en_date.substring(10,12));
	var durn = (e - s) / hourMSecs; //hours absolute diff start and end
	var durn_abs = durn;	

	//now we should subtract non-working days from durn variable
	var duration = durn  / 24;
	var weekendDays = 0;
	var myDate = new Date(int_st_date.substring(0,4), (int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10));
	for (var i = 0; i < duration; i++) {
		//var myDate = new Date(int_st_date.substring(0,4), (int_st_date.substring(4,6)-1),int_st_date.substring(6,8), int_st_date.substring(8,10));
		var myDay = myDate.getDate();
		if ( !isInArray(working_days, myDate.getDay()) ) {
			weekendDays++;
		}
		myDate.setDate(myDay + 1);
	}
	
	//calculating correct durn value
	durn = durn - weekendDays*24;	// total hours minus non-working days (work day hours)

	// check if the last day is a weekendDay
	// if so we subtracted some hours too much before, 
	// we have to fill up the last working day until cal_day_start + daily_working_hours
	if ( !isInArray(working_days, eDate.getDay()) && eDate.getHours() != cal_day_start) {
		durn = durn + Math.max(0, (cal_day_start + daily_working_hours - eDate.getHours()));
	}
	
	//could be 1 or 24 (based on TaskDurationType value) - We'll consider it 1 = hours
	//var durnType = parseFloat(f.task_duration_type.value);	
	var durnType = parseFloat(durntype_fld.value);	
	durn /= durnType;
	//alert(durn);
	if (durnType == 1){
		// durn is absolute weekday hours
		
		//if first day equals last day we're already done
		if( durn_abs < daily_working_hours ) {

			durn = durn_abs;

		} else { //otherwise we need to process first and end day different;
	
			// Hours worked on the first day
			var first_day_hours = cal_day_end - sDate.getHours();
			if (first_day_hours > daily_working_hours)
				first_day_hours = daily_working_hours;

			// Hours worked on the last day
			var last_day_hours = eDate.getHours() - cal_day_start;
			if (last_day_hours > daily_working_hours)
				last_day_hours = daily_working_hours;

			// Total partial day hours
			var partial_day_hours = first_day_hours + last_day_hours;

			// Full work days
			var full_work_days = (durn - partial_day_hours) / 24;

			// Total working hours
			durn = Math.floor(full_work_days) * daily_working_hours + partial_day_hours;
			
			// check if the last day is a weekendDay
			// if so we subtracted some hours too much before, 
			// we have to fill up the last working day until cal_day_start + daily_working_hours
			if ( !isInArray(working_days, eDate.getDay()) && eDate.getHours() != cal_day_start) {
				durn = durn + Math.max(0, (cal_day_start + daily_working_hours - eDate.getHours()));
			}
		}

	} else if (durnType == 24 ) {
		//we should talk about working days so a task duration of 41 hrs means 6 (NOT 5) days!!!
		if (durn > Math.round(durn)) {
			durn++;
		}
	}

	if ( s > e ) {
		//alert( 'End date is before start date!');
	} else {
		duration_fld.value = Math.round(durn);
	}
}
/**
* Get the end of the previous working day 
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
*/
function next_working_day( dateObj ) {
	while ( ! isInArray(working_days, dateObj.getDay()) || dateObj.getHours() >= cal_day_end ) {
		dateObj.setDate(dateObj.getDate()+1);
		dateObj.setHours( cal_day_start );
		dateObj.setMinutes(0);
	}

	return dateObj;
}
	
function addBulkComponent(li) {
//IE
  if (document.all || navigator.appName == "Microsoft Internet Explorer") {
	var form = document.frm_bulk;
      var ni = document.getElementById('tbl_bulk');
      var newitem = document.createElement('input');
      var htmltxt = "";
      newitem.id = 'bulk_selected_task['+li+']';
      newitem.name = 'bulk_selected_task['+li+']';
      newitem.type = 'hidden';
      ni.appendChild(newitem);
  } else {
//Non IE
	var form = document.frm_bulk;
      var ni = document.getElementById('tbl_bulk');
      var newitem = document.createElement('input');
      newitem.setAttribute("id",'bulk_selected_task['+li+']');
      newitem.setAttribute("name",'bulk_selected_task['+li+']');
      newitem.setAttribute("type",'hidden');
      ni.appendChild(newitem);
  }
}

function removeBulkComponent(li) {
      var t = document.getElementById('tbl_bulk');
      var old = document.getElementById('bulk_selected_task['+li+']');
      t.removeChild(old);
}


function getStyle(nodeName, sStyle, iStyle) {
      var element = document.getElementById(nodeName);
      if (window.getComputedStyle) {
            var style=document.defaultView.getComputedStyle(element,null);
      	var value = style.getPropertyValue(sStyle);
      } else {
            var value = eval("element.currentStyle." + iStyle);
      }
      return value;      
}

function mult_sel(cmbObj, box_name, form_name) {
	var f = eval('document.'+form_name);
	var check = cmbObj.checked;

      for (var i=0, i_cmp=f.length;i < i_cmp;i++) {
	      fldObj = f.elements[i];
	      var field_name = fldObj.id;
	      if (fldObj.type == 'checkbox' && field_name.indexOf(box_name) >= 0) {
	            id = field_name.replace('selected_task_','');
	            //lets find the right row before continuing
      			var trs = document.getElementsByTagName('tr');
      			for (var ri=0, ri_cmp=trs.length;ri<ri_cmp;ri++) {
			        rowObj = trs[ri];
			        var row_id = rowObj.id;
	      			if (row_id.indexOf('task_'+id+'_') >= 0) {
	            		row = document.getElementById(row_id);
	            	}
	            }
		        var oldcheck = fldObj.checked;
	            fldObj.checked = (check) ? true : false;
	            if (check) {
	                  highlight_tds(row, 2, id);
	                  //Only add the component if it didn't exist or else we get JS trouble
	                  if (!oldcheck) {
	                        addBulkComponent(id);
	                  }
	            } else {
	                  highlight_tds(row, 0, id);
	                  //Only remove the component if it exists or else we get JS trouble
	                  if (oldcheck) {
	                        removeBulkComponent(id);
	                  }
	            }
	      }
      }
}

function highlight_tds(row, high, id) {
//high = 0 or false => remove highlight
//high = 1 or true => highlight
//high = 2 => select
//high = 3 => deselect
      if (document.getElementsByTagName) {
            var tcs = row.getElementsByTagName('td');
            var cell_name = '';
            if (!id) {
                  check = false;
            } else {
                  var f = eval('document.frm_tasks');
                  var check = eval('f.selected_task_'+id+'.checked');
            }
            for (var j = 0, j_cmp=tcs.length; j < j_cmp; j+=1) {
                  cell_name = eval('tcs['+j+'].id');
                  if(!(cell_name.indexOf('ignore_td_') >= 0)) {
                        if (high == 3) {
                              tcs[j].style.background = '#FFFFCC';
                        } else if (high == 2 || check) {
                              tcs[j].style.background = '#FFCCCC';
                        } else if (high == 1) {
                              tcs[j].style.background = '#FFFFCC';
                        } else {
                              tcs[j].style.background = original_bgc;
                        }
                  }
            }
      }
}

var is_check;
function select_box(box, id, row_id, form_name){
	var f = eval('document.'+form_name);
	var check = eval('f.'+box+'_'+id+'.checked');
      boxObj = eval('f.elements["'+box+'_'+id+'"]');
      if ((is_check && boxObj.checked && !boxObj.disabled) || (!is_check && !boxObj.checked && !boxObj.disabled)) {
            row = document.getElementById(row_id);
            boxObj.checked = true;
            highlight_tds(row, 2, id);
            addBulkComponent(id);
      } else if ((is_check && !boxObj.checked && !boxObj.disabled) || (!is_check && boxObj.checked && !boxObj.disabled)) {
            row = document.getElementById(row_id);
            highlight_tds(row, 3, id);
            boxObj.checked = false;      
            removeBulkComponent(id);
      }
}

function toggle_users(id){
  var element = document.getElementById(id);
  element.style.display = (element.style.display == '' || element.style.display == "none") ? "inline" : "none";
}

function addUser(form) {
	var fl = form.bulk_task_user;
	var pc = form.bulk_task_assign_perc;
	var au = form.bulk_task_assign.length -1;
	//gets value of percentage assignment of selected resource
	var perc = pc.options[pc.selectedIndex].value;

	var users = "x";

	//build array of assiged users
	for (au; au > -1; au--) {
		users = users + "," + form.bulk_task_assign.options[au].value + ",";
	}

	//Pull selected resources and add them to list
	if (fl.value > 0 && pc.value > 0) {
		if (fl.options[fl.selectedIndex].selected && users.indexOf( "," + fl.options[fl.selectedIndex].value + "," ) == -1) {
			t = form.bulk_task_assign.length;
			opt = new Option( fl.options[fl.selectedIndex].text+" ["+perc+"%]", fl.options[fl.selectedIndex].value);
			form.bulk_task_hperc_assign.value += fl.options[fl.selectedIndex].value+"="+perc+";";
			form.bulk_task_assign.options[t] = opt;
		}
	}
}

function removeUser(form) {
	var fl = form.bulk_task_assign.length -1;
	var au = form.bulk_task_assign.length -1;

	var users = "x";

	//build array of assiged users
	for (au; au > -1; au--) {
		users = users + "," + form.bulk_task_assign.options[au].value + ",";
	}
	
	for (fl; fl > -1; fl--) {
		if (form.bulk_task_assign.options[fl].selected && users.indexOf( "," + form.bulk_task_assign.options[fl].value + "," ) > -1) {
			var selValue = form.bulk_task_assign.options[fl].value;			
			var re = ".*("+selValue+"=[0-9]*;).*";
			var hiddenValue = form.bulk_task_hperc_assign.value;
			if (hiddenValue) {
				var b = hiddenValue.match(re);
				if (b[1]) {
					hiddenValue = hiddenValue.replace(b[1], '');
				}
				form.bulk_task_hperc_assign.value = hiddenValue;
			      form.bulk_task_assign.options[fl] = null;
			}
		}
	}
}

function expand_selector(id, table_name) {
      var trs = document.getElementsByTagName('tr');

      for (var i=0, i_cmp=trs.length;i < i_cmp;i++) {
	      	var tr_name = trs.item(i).id;
	
	        if (tr_name.indexOf(id) >= 0) {
                    var tr = document.getElementById(tr_name);
                    if (navigator.family == "gecko" || navigator.family == "opera"){            
                          tr.style.visibility = (tr.style.visibility == '' || tr.style.visibility == "collapse") ? "visible" : "collapse";
                          tr.style.display = (tr.style.display == "none") ? "" : "none";
                          var img_expand = document.getElementById(id+'_expand');
                          var img_collapse = document.getElementById(id+'_collapse');
                          img_collapse.style.display = (tr.style.visibility == 'visible') ? "inline" : "none";
                          img_expand.style.display = (tr.style.visibility == '' || tr.style.visibility == "collapse") ? "inline" : "none";
                    } else {
                          tr.style.display = (tr.style.display == "none") ? "" : "none";
                          var img_expand = document.getElementById(id+'_expand');
                          var img_collapse = document.getElementById(id+'_collapse');
                          img_collapse.style.display = (tr.style.display == '') ? "inline" : "none";
                          img_expand.style.display = (tr.style.display == 'none') ? "inline" : "none";
                    }
	        }
      }
}