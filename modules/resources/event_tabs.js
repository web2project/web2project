// Javascript for handling the tabs used for tasks.


function checkOther(form)
{
  return true;
}

function saveOther(form)
{
	var editFrm=document.editFrm;
	var resources = "";
	var len = form.assigned.length;
	for (var i = 0; i < len; i++) {
		resources +=(i?',':'')+form.assigned.options[i].value;
	}
	var h=new HTMLex();
	editFrm.appendChild(h.addHidden('other_resource',resources));
}


function addResource(form)
{
	var fl = form.resources.length -1;
	var au = form.assigned.length -1;
	//gets value of percentage assignment of selected resource
//	var perc = form.resource_assignment.options[form.resource_assignment.selectedIndex].value;

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
			form.hresource_assign.value += form.resources.options[fl].value+',';
			form.assigned.options[t] = opt
		}
	}
}

function removeResource(form)
{
	fl = form.assigned.length -1;
	for (fl; fl > -1; fl--) {
		if (form.assigned.options[fl].selected) {
			//remove from hperc_assign
			var selValue = form.assigned.options[fl].value;			
			var re = ".*("+selValue+",).*";
			var hiddenValue = form.hresource_assign.value;
			if (hiddenValue) {
				var b = hiddenValue.match(re);
				if (b[1]) {
					hiddenValue = hiddenValue.replace(b[1], '');
				}
				form.hresource_assign.value = hiddenValue;
				form.assigned.options[fl] = null;
			}
		}
	}
}
