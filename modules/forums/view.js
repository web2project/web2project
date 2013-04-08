$(function() {
	$.getScript('lib/sceditor/jquery.sceditor.bbcode.min.js', function(){
		$.getScript('lib/sceditor/languages/'+w2p.lang+'.js');
		$.getScript('lib/sceditor/jquery.sceditor.w2p-helper.js', function(){
			$.sceditor.plugins.bbcode.bbcode.remove('size');
			$('textarea[name="message_body"]').sceditor();
		});
	});
	if (document.createStyleSheet) {
		document.createStyleSheet("lib/sceditor/themes/w2p.css");
	} else {
		$('head').append('<link rel="stylesheet" type="text/css" href="lib/sceditor/themes/w2p.css" media="all" charset="utf-8"/>');
	}
});
