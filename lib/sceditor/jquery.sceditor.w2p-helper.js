$(function() {
	$.sceditor.plugins.bbcode.bbcode
		.set("h1", {
			html: "<h1>{0}</h1>",
			tags: { "h1": null, "H1": null },
			allowedChildren: ['#'],
			format: "[h1]{0}[/h1]",
			breakStart: false,
			breakAfter: true,
			isInline: false,
			skipLastLineBreak: true
		})
		.set("h2", {
			html: "<h2>{0}</h2>",
			tags: { "h2": null, "H2": null },
			allowedChildren: ['#'],
			format: "[h2]{0}[/h2]",
			breakStart: false,
			breakAfter: true,
			isInline: false,
			skipLastLineBreak: true
		})
		.set("h3", {
			html: "<h3>{0}</h3>",
			tags: { "h3": null, "H3": null },
			allowedChildren: ['#'],
			format: "[h3]{0}[/h3]",
			breakStart: false,
			breakAfter: true,
			isInline: false,
			skipLastLineBreak: true
		})
		.set("h4", {
			html: "<h4>{0}</h4>",
			tags: { "h4": null, "H4": null },
			allowedChildren: ['#'],
			format: "[h4]{0}[/h4]",
			breakStart: false,
			breakAfter: true,
			isInline: false,
			skipLastLineBreak: true
		})
		.set("list", {
			html: "<ol>{0}</ol>",
			format: "[list]{0}[/list]",
			breakStart: true,
			breakAfter: true,
			isInline: false,
			skipLastLineBreak: true
		})
		.set("ulist", {
			html: "<ul>{0}</ul>",
			format: "[ulist]{0}[/ulist]",
			breakStart: true,
			breakAfter: true,
			isInline: false,
			skipLastLineBreak: true
		})
		.set("ul", { format: "[ulist]{0}[/ulist]" })
		.set("ol", { format: "[list]{0}[/list]" })
		.set("align", {
			html: function(element, attrs, content) {
				return '<div align="' + (attrs.defaultattr || 'left') + '">' + content + '</div>';
			},
			isInline: false
		})
		.set("center", { format: "[align=center]{0}[/align]" })
		.set("left", { format: "[align=left]{0}[/align]" })
		.set("right", { format: "[align=right]{0}[/align]" })
		.set("justify", { format: "[align=justify]{0}[/align]" })
		.remove("table").remove("th").remove("td").remove("tr")
		.remove('emoticon').remove('hr').remove('rtl').remove('youtube');

	$.sceditor.command
		.set("bulletlist", { txtExec: ["[ulist]\n[*]", "\n[/ulist]"] })
		.set("orderedlist", { txtExec: ["[list]\n[*]", "\n[/list]"] })
		.set("undo", { exec: "undo", tooltip: "Undo" })
		.set("redo", { exec: "redo", tooltip: "Redo" })
		.set("h1", {
			exec: function(){ this.execCommand('formatBlock', '<h1>'); },
			txtExec: ["[h1]", "[/h1]"],
			tooltip: "Heading 1"
		})
		.set("h2", {
			exec: function(){ this.execCommand('formatBlock', '<h2>'); },
			txtExec: ["[h2]", "[/h2]"],
			tooltip: "Heading 2"
		})
		.set("h3", {
			exec: function(){ this.execCommand('formatBlock', '<h3>'); },
			txtExec: ["[h3]", "[/h3]"],
			tooltip: "Heading 3"
		})
		.set("h4", {
			exec: function(){ this.execCommand('formatBlock', '<h4>'); },
			txtExec: ["[h4]", "[/h4]"],
			tooltip: "Heading 4"
		})
		.set("center", { txtExec: ["[align=center]", "[/align]"] })
		.set("left", { txtExec: ["[align=left]", "[/align]"] })
		.set("right", { txtExec: ["[align=right]", "[/align]"] })
		.set("justify", { txtExec: ["[align=justify]", "[/align]"] })
		.remove('table').remove('horizontalrule').remove('emoticon').remove('youtube');

	$.sceditor.defaultOptions.plugins = 'bbcode';
	$.sceditor.defaultOptions.emoticonsEnabled = false;
	$.sceditor.defaultOptions.fonts = 'sans-serif,serif,monospace,Arial Black,Impact,Comic Sans MS';
	$.sceditor.defaultOptions.autoUpdate = true;
	$.sceditor.defaultOptions.toolbar = 'h1,h2,h3,h4,bulletlist,orderedlist,code,quote|undo,redo|cut,copy,paste,pastetext|bold,italic,underline,strike,subscript,superscript,color,removeformat|left,center,right|email,link,unlink';
	$.sceditor.defaultOptions.dropDownCss = { "z-index": 202 };

});

