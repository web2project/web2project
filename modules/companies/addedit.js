

function testURL( x ) {
    var test = document.editFrm.company_primary_url.value;
    if (test.length > 6) {
        newwin = window.open( 'http://' + test, 'newwin', '' );
    }
}