/* 
 * This is refactoring all the javascript from each of the Reports module into a
 *   single place.
 */

function setDate( frm_name, f_date ) {
	fld_date = eval( 'document.' + frm_name + '.' + f_date );
	fld_real_date = eval( 'document.' + frm_name + '.' + 'log_' + f_date );
	if (fld_date.value.length>0) {
      if ((parseDate(fld_date.value))==null) {
            alert(format_error_msg);
            fld_real_date.value = '';
            fld_date.style.backgroundColor = 'red';
        } else {
        	fld_real_date.value = formatDate(parseDate(fld_date.value), 'yyyyMMdd');
            /*
             * Warning: This cal_sdf is set in reports/index.php because we
             *   can't use PHP to echo here.
             */
        	fld_date.value = formatDate(parseDate(fld_date.value), cal_sdf);
            fld_date.style.backgroundColor = '';
  		}
	} else {
      	fld_real_date.value = '';
	}
}