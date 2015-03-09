/*
Initial code by mirage335, derived from unlicensed documentation found on the following sites. Under same licese as web2project unless stated otherwise.

https://www.zoho.com/creator/help/url-pattern/functionality-based-urls.html#To_set_default_values_for_form_fields
http://stackoverflow.com/questions/14070105/pre-fill-form-field-via-url-in-html
http://help.formassembly.com/knowledgebase/articles/340353-prefill-through-the-url
http://www.laserfiche.com/ecmblog/article/tech-tip-using-url-parameters-to-pre-fill-form-fields
http://www.willmaster.com/library/manage-forms/pre-fill_forms_with_javascript.php
http://stackoverflow.com/questions/11203321/javascript-bookmarklet-how-to-autofill-a-field-with-current-page-url
http://stackoverflow.com/questions/11393486/how-to-set-the-value-of-a-form-element-using-javascript
http://stackoverflow.com/questions/3301688/how-do-you-get-the-currently-selected-option-in-a-select-via-javascript
*/

function match_drop(p) {
	for(var i=0; i < document.getElementsByName(p[0])[0].options.length; i++) {
		if (document.getElementsByName(p[0])[0].options[i].text == decodeURIComponent(p[1])) {
			document.getElementsByName(p[0])[0].value = document.getElementsByName(p[0])[0].options[i].value;
		}
	}
}

var hashParams = window.location.hash.substr(1).split('&'); // substr(1) to remove the `#`
for(var i = 0; i < hashParams.length; i++){
	var p = hashParams[i].split('=');
	
	
	if ( document.getElementsByName(p[0])[0].options ) {
		match_drop(p);
	}
	else {
		document.getElementsByName(p[0])[0].value = decodeURIComponent(p[1]);
	}
}