
function update_workspace(id) {
	var tr = document.getElementById(id);
	(tr.style.display == "none") ? eval('document.frmWorkspace.pd_option_view_'+id+'.value=0') : eval('document.frmWorkspace.pd_option_view_'+id+'.value=1');
	(tr.style.display == "none") ? eval('document.editFrm.pd_option_view_'+id+'.value=0') : eval('document.editFrm.pd_option_view_'+id+'.value=1');
	(tr.style.display == "none") ? eval('document.frm_bulk.pd_option_view_'+id+'.value=0') : eval('document.frm_bulk.pd_option_view_'+id+'.value=1');
}

function expandAll() {
      expand_collapse('project', 'tblProjects', 'expand');
      expand_collapse('gantt', 'tblProjects', 'expand');
      expand_collapse('tasks', 'tblProjects', 'expand');
      expand_collapse('actions', 'tblProjects', 'expand');
      expand_collapse('addtasks', 'tblProjects', 'expand');
      expand_collapse('files', 'tblProjects', 'expand');
      update_workspace('project');
      update_workspace('gantt');
      update_workspace('tasks');
      update_workspace('actions');
      update_workspace('addtasks');
      update_workspace('files');
}

function collapseAll() {
      expand_collapse('project', 'tblProjects', 'collapse');
      expand_collapse('gantt', 'tblProjects', 'collapse');
      expand_collapse('tasks', 'tblProjects', 'collapse');
      expand_collapse('actions', 'tblProjects', 'collapse');
      expand_collapse('addtasks', 'tblProjects', 'collapse');
      expand_collapse('files', 'tblProjects', 'collapse');
      update_workspace('project');
      update_workspace('gantt');
      update_workspace('tasks');
      update_workspace('actions');
      update_workspace('addtasks');
      update_workspace('files');
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
    if (old) {
        t.removeChild(old);
    }
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

function select_all_rows(cmbObj, elements_name) {
    var checked = cmbObj.checked;
    var checkboxes = document.getElementsByName(elements_name);

    // check all
    for (var i = 0; i < checkboxes.length; i++) {
        id = checkboxes[i].value;
        checkboxes[i].checked = checked;

        result = (checked) ? addBulkComponent(id) : removeBulkComponent(id);
    }
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

/*
 *  This has been deprecated in favor of css-based highlighting using :hover
 *    instead of Javascript.
 *
 *  @deprecated
 */
function highlight_tds(row, high, id) {
//high = 0 or false => remove highlight
//high = 1 or true => highlight
//high = 2 => select
//high = 3 => deselect
      if (document.getElementsByTagName) {
            if (row) {
				var tcs = row.getElementsByTagName('td');
				var cell_name = '';
				if (!id) {
					  check = false;
				} else {
					  var f = eval('document.frm_tasks');
					  if (eval('f.selected_task_'+id)) {
						var check = eval('f.selected_task_'+id+'.checked');
					  }
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
}

var is_check;
function select_row(box, id, form_name){
	var f = eval('document.'+form_name);
	if (eval('f.selected_task_'+id)) {
		var check = eval('f.'+box+'_'+id+'.checked');
		boxObj = eval('f.elements["'+box+'_'+id+'"]');
		if ((is_check && boxObj.checked && !boxObj.disabled) || (!is_check && !boxObj.checked && !boxObj.disabled)) {
			boxObj.checked = true;
			addBulkComponent(id);
		} else if ((is_check && !boxObj.checked && !boxObj.disabled) || (!is_check && boxObj.checked && !boxObj.disabled)) {
			boxObj.checked = false;
			removeBulkComponent(id);
		}
	}
}

function select_box(box, id, row_id, form_name){
    var f = eval('document.'+form_name);
	if (eval('f.selected_task_'+id)) {
        var prop = eval('f.elements["'+box+'_'+id+'"]');
		boxObj = (prop) ? prop : eval('f.selected_task_'+id);
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