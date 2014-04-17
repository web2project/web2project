function submitIt() {
    var form = document.changeclient;
    if (form.company_name.value.length < 3) {
        alert( "<?php echo $AppUI->_('companyValidName', UI_OUTPUT_JS); ?>" );
        form.company_name.focus();
    } else {
        form.submit();
    }
}

function testURL( x ) {
    var test = document.changeclient.company_primary_url.value;
    if (test.length > 6) {
        newwin = window.open( 'http://' + test, 'newwin', '' );
    }
}