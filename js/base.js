/* Copyright 2003,2004 Adam Donnison <adam@saki.com.au>

    This file is part of the collected works of Adam Donnison.

    This file is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This file is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

*/

// If we are an IE window, set undefined to null.  This is an ECMAscript
// standard, but then MS makes its own standards.
if (navigator.userAgent.indexOf('MSIE') != -1) {
	var undefined = null;
}

navigator.family='ie';
if (window.navigator.userAgent.toLowerCase().match(/gecko/)) {navigator.family = 'gecko'}
if (navigator.userAgent.toLowerCase().indexOf('opera') + 1 || window.opera) {navigator.family = 'opera'}

/* function center_window
 * Create the window options clause required to ensure a window is
 * centered over the calling window.  A width or height of 0 or less
 * results in using the corresponding value for the parent window.
 * e.g. 0,0 results in a window exactly the same size and overlapping
 * the parent exactly.
 */
function center_window(width, height) {
	var ix = window.outerWidth;
	var iy = window.outerHeight;
	var mx = window.screenX;
	var my = window.screenY;
	var result;
	
	var cx;
	var cy;

	if (width <= 0) {
		width = ix;
		cx = mx;
	} else {
		mx += ( ix / 2 );
		mx -= ( width / 2 );
		cx = Math.round(mx);
	}
	if (height <= 0) {
		cy = my;
		height = iy;
	} else {
		my += ( iy / 2 );
		my -= ( height / 2 );
		cy = Math.round(my);
	}

	result = 'screenX=' + cx + ',screenY=' + cy + ',outerHeight=' + height + ',outerWidth=' + width;
	return result;
}

// Class Comparable
// Define new Comparable object capable of being used to store
// data in an array.

// constructor CompItem
function CompItem(key, data) {
	this.key = key;
	this.data = data;
	this.compare = comp_keys;
	this.equals = comp_equal;
}
//

// function comp_keys
// Compare function to compare two Comparable objects
function comp_keys(target) {
	if (this.key == target.key) return 0;
	if (this.key < target.key) return -1;
	return 1;
}

// function comp_equal
function comp_equal(target) {
	if (this.key == target) return true;
	return false;
}

// Comparison array class constructor
function Comparable() {
	this.list = new Array();
	this.add = ca_add;
	this.find = ca_find;
	this.length = ca_length;
	this.get = ca_get;
	this.search = ca_search;
	this.count = 0;
}

// function ca_add
function ca_add(key, data) {
	var last_id = this.search(key);
	if (last_id != -1) {
		this.list[last_id] = new CompItem(key, data);
	} else {
		this.list[this.count] = new CompItem(key, data);
		this.count++;
	}
// this.list.push(new CompItem(key, data));
}

// function ca_find
function ca_find(key) {
	var end = this.list.length;
	for ( var i = 0; i < end; i++) {
		cp = this.list[i];
		if (cp.equals(key)) {
		  return cp.data;
		}
	}
	return undefined;
}

// function ca_search
function ca_search(key) {
	var end = this.list.length;
	for ( var i = 0; i < end; i++) {
		cp = this.list[i];
		if (cp.equals(key)) {
		  return i;
		}
	}
	return -1;
}

// function ca_length
function ca_length() {
	return this.list.length;
}

// function ca_get
function ca_get(id) {
	return this.list[id];
}

// Class HTMLex
// Constructor HTMLex
function HTMLex() {
	this.addTable = _HTMLaddTable;
	this.addRow = _HTMLaddRow;
	this.addHeader = _HTMLaddHeader;
	this.addHeaderNode = _HTMLaddHeaderNode;
	this.addCell = _HTMLaddCell;
	this.addCellNode = _HTMLaddCellNode;
	this.addTextInput = _HTMLaddTextInput;
	this.addHidden = _HTMLaddHidden;
	this.addTextNode = _HTMLaddTextNode;
	this.addNode = _HTMLaddNode;
	this.addSpan = _HTMLaddSpan;
	this.addSelect = _HTMLaddSelect;
	this.addOption = _HTMLaddOption;
}

// function _HTMLaddTable
function _HTMLaddTable(id, width, border) {
	var c = new Comparable;
	if (width) {
		c.add('width', width);
	}
	if (border) {
		c.add('border', border);
	}
	if (id) {
		c.add('id', id);
	}
	return this.addNode('table', false, c);
}

// function _HTMLaddRow
function _HTMLaddRow(id) {
	var tr = document.createElement('tr');
	if (id) {
		tr.setAttribute('id', id);
	}
	return tr;
}

// function _HTMLaddHeaderNode
function _HTMLaddHeaderNode(node, id, width) {
	var c = new Comparable;
	if (id) {
		c.add('id', id);
	}
	if (width) {
		c.add('width', width);
	}
	return this.addNode('th', node, c);
}

// function _HTMLaddHeader
function _HTMLaddHeader(text, id, width) {
	var c = new Comparable;
	if (id) {
		c.add('id', id);
	}
	if (width) {
		c.add('width', width);
	}
	return this.addTextNode('th', text, c);
}

// function _HTMLaddCell
function _HTMLaddCell(text, id, width, bold) {
	var c = new Comparable;
	if (id) {
		c.add('id', id);
	}
	if (width) {
		c.add('width', width);
	}
	return this.addTextNode('td', text, c, bold);
}

// function _HTMLaddSpan
function _HTMLaddSpan(text, id) {
	var c = new Comparable;
	if (id) {
		c.add('id', id);
	}
	return this.addTextNode('span', text, c);
}

// function _HTMLaddCellNode
function _HTMLaddCellNode(node, id, width) {
	var c = new Comparable;
	if (id) {
		c.add('id', id);
	}
	if (width) {
		c.add('width', width);
	}
	return this.addNode('td', node, c);
}

// function _HTMLaddTextNode
function _HTMLaddTextNode(type, text, args, bold) {
	var node = document.createElement(type);
	if (bold) {
		var b = node.appendChild(document.createElement('b'));
		if (text) {
			b.appendChild(document.createTextNode(text));
		}
	} else {
		if (text) {
			node.appendChild(document.createTextNode(text));
		}
	}
	var i;
	if (args) {
		for (i = args.length() -1; i >=0; i--) {
		  var elem = args.get(i);
		  node.setAttribute(elem.key, elem.data);
		}
	}
	return node;
}

// function _HTMLaddNode
function _HTMLaddNode(type, child, args) {
	var node = document.createElement(type);
	if (child) {
		node.appendChild(child);
	}
	var i;
	for (i = args.length() -1; i >=0; i--) {
		var elem = args.get(i);
		node.setAttribute(elem.key, elem.data);
	}
	return node;
}

// function _HTMLaddTextInput
function _HTMLaddTextInput(id, value, size, maxlength) {
	var c = new Comparable;
	c.add('id', id);
	c.add('name', id);
	c.add('type', 'text');
	if (size) {
		c.add('size', size);
	}
	if (maxlength) {
		c.add('maxlength', maxlength);
	}
	if (value) {
		c.add('value', value);
	}
	return this.addNode('input', false, c);
}

// function _HTMLaddHidden
function _HTMLaddHidden(id, value) {
	if (window.navigator.userAgent.toLowerCase().match(/gecko/)) {navigator.family = "gecko"}
	if (navigator.userAgent.toLowerCase().indexOf('opera') + 1 || window.opera) {navigator.family = "opera"}
	var c = new Comparable
	c.add('id', id);
	c.add('name', id);
	if (navigator.family == "gecko" || navigator.family == "opera"){
		c.add('type', 'hidden');
		type = 'input';
	} else {
		type = 'textarea';
		c.add('className', 'hidden');
	}
	c.add('value', value);
	return this.addNode(type, false, c);
}

// function _HTMLaddSelect
function _HTMLaddSelect(id, cls, multi) {
	var c = new Comparable;
	c.add('id', id);
	c.add('name', id);
	if (cls) {
		c.add('class', cls);
	}
	if (multi) {
		c.add('multiple', 'multiple');
	}
	return this.addNode('select', false, c);
}

// function _HTMLaddOption
function _HTMLaddOption(value, text, selected) {
	var c = new Comparable;
	c.add('value', value);
	if (selected) {
		c.add('selected', 'selected');
	}
	return this.addTextNode('option', text, c);
}

// class CommonEvent {{{
function CommonEvent(e) {
	// Handle IE, standard Javascript, and passable fields for non-events.
	// Tuned to run with NS 4 and above and IE 4 and above.
	// Tested with Mozilla 1.7, Firefox 0.8, and IE 5
	var target = null;
	var x = 0;
	var y = 0;
	var type = null;
	var button = null;
	var keycode = null;
	var altKey = false;
	var shiftKey = false;
	var ctrlKey = false;
	var metaKey = false;
	
	if (e) {
		if (e.target) {
			this.target = e.target;
			this.type = e.type;
			this.x = e.x;
			this.y = e.y;
		  	if (e.modifiers) {
				this.altKey = (e.modifiers & ALT_MASK) ? true : false;
				this.ctrlKey = (e.modifiers & CONTROL_MASK) ? true : false;
				this.shiftKey = (e.modifiers & SHIFT_MASK) ? true : false;
				this.metaKey = (e.modifiers & META_MASK) ? true : false;
		  	} else {
				if (e.altKey) this.altKey = true;
				if (e.shiftKey) this.shiftKey = true;
				if (e.ctrlKey) this.ctrlKey = true;
				if (e.metaKey) this.metaKey = true;
		  	}
		  	if (e.type.substr(0,3).toLowerCase() == 'key') {
				this.keycode = e.which;
		  	} else {
				this.button = e.which;
		  	}
		} else {
			this.target = e;
			this.type = 'field';
		}
	} else if (event) {
		this.target = event.srcElement;
		this.type = event.type;
		this.x = event.x;
		this.y = event.y;
		this.button = event.button;
		this.keycode = event.keyCode;
		this.altKey = event.altKey;
		this.shiftKey = event.shiftKey;
		this.ctrlKey = event.ctrlKey;
	}
}

// function ucfirst
function ucfirst(s, delim) {
	if (!delim) { 
		delim = ' ';
	}
	var a = s.split(delim);
	var res = "";
	var start = false;
	for (var i = 0; i < a.length; i++) {
		if (start) {
			res += " ";
		} else {
		  start = true;
		}
		res += a[i].substr(0, 1).toUpperCase() + a[i].substr(1);
	}
	return res;
}

/** function clear_span
 * Removes any children of an element by ID.
 */
function clear_span(id) {
	var span = document.getElementById(id);
	if (span) {
		if (span.hasChildNodes()) {
			for (var i = span.childNodes.length - 1; i >= 0; i--) {
				span.removeChild(span.childNodes.item(i));
			}
		}
	}
	return span;
}

// function show_message
function show_message(fname, txt) {
	display_message(txt, fname + '_message');
}

// function show_instruction
function show_instruction(txt) {
	display_message(txt, 'instruct');
}

/** function display_message
 * Generic message display.  This looks for the required element on
 * the page and if found it adds a text node, (or changes an existing
 * one) to the text required.  Used by show_message and show_instruction.
 * The element name is supplied with the 'id=' attribute of the
 * HTML.
 */
function display_message(txt, elem) {
	var span = document.getElementById(elem);
	if (span == null) {
		return;
	}
	
	var text;
	if (span.hasChildNodes()) {
		text = span.childNodes.item(0);
		text.nodeValue = txt;
	} else {
		text = span.appendChild(document.createTextNode(txt));
	}
}

// clear_message, reset_message, default_instruction
function clear_message(fname) {
	reset_message( fname + '_message');
}

function clear_instruction() {
	reset_message('instruct');
}


/** function reset_message
 * Function to clear the text node associated with an element.
 * The element name is supplied with the 'id=' attribute of the
 * HTML.  This can be used to remove text on any node that supports it.
 */
function reset_message(elem) {
	var span = document.getElementById(elem);
	if (span == null) {
		return;
	}
	
	var text;
	if (span.hasChildNodes()) {
		text = span.childNodes.item(0);
		text.nodeValue = '';
	} else {
		text = span.appendChild(document.createTextNode(''));
	}
}

// function find_anchor {{{
// The find_anchor function is usually called when the browser
// is IE based and therefore doesn't use the name to provide
// an index into document.anchors.  Should probably be replaced
// to use getElementById or getElementsByTagName instead of
// relying on browser-specific extensions.
function find_anchor(a) {
	for (var i = 0; i < document.anchors.length; i++) {
		if (document.anchors[i].name == a) {
			return true;
		}
	}
	return false;
}

// function getInnerHeight
function getInnerHeight(win) {
	var winHeight;
	if (win.innerHeight) {
		winHeight = win.innerHeight;
	} else if (win.document.documentElement && win.document.documentElement.clientHeight) {
		winHeight = win.document.documentElement.clientHeight;
	} else if (win.document.body) {
		winHeight = win.document.body.clientHeight;
	} else {
		winHeight = 0; // This should never happens
	}
	return winHeight;
}

function expand_collapse(id, table_name, option, opt_level, root) {      
      var expand = (option == 'expand' ? 1 : 0);
      var collapse = (option == 'collapse' ? 1 : 0);
      var level = (opt_level == 0 ? 0 : (opt_level > 0 ? opt_level : -1));
//root can be used in two ways:
//1 = root (level 0) is to be shown/hidden just like an ordinary row
//2 = root (level 0) is not to be hidden meaning the only thing that shifts is the state of the auxiliary images 
      var include_root = (root ? root : 0);      
//done controls if the sublevels have been treated already so that we do not treat another level again
	  var done = false;
//found controls if the root level has been found so that the following rows are treated
	  var found = false;

//catch all the rows of the table
      var trs = document.getElementsByTagName('tr');

      for (var i=0;i < trs.length;i++) {
	      var tr_name = trs.item(i).id;
//First lets handle non level situations
	      if ((tr_name.indexOf(id) >= 0) && level<0) {
	            var tr = document.getElementById(tr_name);
	            if (collapse || expand) {
	                  if (collapse) {
	                        if (navigator.family == "gecko" || navigator.family == "opera"){            
	                              tr.style.visibility = "collapse";
	                              tr.style.display = "none";
	                              var img_expand = document.getElementById(tr_name+'_expand');
	                              var img_collapse = document.getElementById(tr_name+'_collapse');
	                              if (img_expand==null) {
	                              		var img_expand = document.getElementById(id+'_expand');
	                              }
	                              if (img_collapse==null) {
	                              		var img_collapse = document.getElementById(id+'_collapse');
	                              }
	                              img_collapse.style.display = "none";
	                              img_expand.style.display = "inline";
	                        } else {
	                              tr.style.display = "none";
	                              var img_expand = document.getElementById(tr_name+'_expand');
	                              var img_collapse = document.getElementById(tr_name+'_collapse');
	                              if (img_expand==null) {
	                              		var img_expand = document.getElementById(id+'_expand');
	                              }
	                              if (img_collapse==null) {
	                              		var img_collapse = document.getElementById(id+'_collapse');
	                              }
	                              img_collapse.style.display = "none";
	                              img_expand.style.display = "inline";
	                        }
	                  } else {
	                        if (navigator.family == "gecko" || navigator.family == "opera"){            
	                              tr.style.visibility = "visible";
	                              tr.style.display = "";
	                              var img_expand = document.getElementById(tr_name+'_expand');
	                              var img_collapse = document.getElementById(tr_name+'_collapse');
	                              if (img_expand==null) {
	                              		var img_expand = document.getElementById(id+'_expand');
	                              }
	                              if (img_collapse==null) {
	                              		var img_collapse = document.getElementById(id+'_collapse');
	                              }
	                              img_collapse.style.display = "inline";
	                              img_expand.style.display = "none";
	                        } else {
	                              tr.style.display = "";
	                              var img_expand = document.getElementById(tr_name+'_expand');
	                              var img_collapse = document.getElementById(tr_name+'_collapse');
	                              if (img_expand==null) {
	                              		var img_expand = document.getElementById(id+'_expand');
	                              }
	                              if (img_collapse==null) {
	                              		var img_collapse = document.getElementById(id+'_collapse');
	                              }
	                              img_collapse.style.display = "inline";
	                              img_expand.style.display = "none";
	                        }
	                  }
	            } else {
	                  if (navigator.family == "gecko" || navigator.family == "opera"){            
	                        tr.style.visibility = (tr.style.visibility == '' || tr.style.visibility == "collapse") ? "visible" : "collapse";
	                        tr.style.display = (tr.style.display == "none") ? "" : "none";
	                        var img_expand = document.getElementById(tr_name+'_expand');
	                        var img_collapse = document.getElementById(tr_name+'_collapse');
	                        if (img_expand==null) {
                          		var img_expand = document.getElementById(id+'_expand');
                            }
	                        if (img_collapse==null) {
                          		var img_collapse = document.getElementById(id+'_collapse');
                            }
	                        img_collapse.style.display = (tr.style.visibility == 'visible') ? "inline" : "none";
	                        img_expand.style.display = (tr.style.visibility == '' || tr.style.visibility == "collapse") ? "inline" : "none";
	                  } else {
	                        tr.style.display = (tr.style.display == "none") ? "" : "none";
	                        var img_expand = document.getElementById(tr_name+'_expand');
	                        var img_collapse = document.getElementById(tr_name+'_collapse');
	                        if (img_expand==null) {
                          		var img_expand = document.getElementById(id+'_expand');
                            }
	                        if (img_collapse==null) {
                          		var img_collapse = document.getElementById(id+'_collapse');
                            }
	                        img_collapse.style.display = (tr.style.display == '') ? "inline" : "none";
	                        img_expand.style.display = (tr.style.display == 'none') ? "inline" : "none";
	                  }
	            }      
//lets handle expand collapses of leveled rows (like tasks dynamics/parents) - THIS "ELSEIF" HANDLES THE PARENT TASK ROW ITSELF
//Here we don't show/hide the row itself, we only handle the +/- image
	      } else if((tr_name.indexOf(id) >= 0) && level>=0 && !done && !found && !include_root) {
//So we found the root row lets record that:
					found = true;
	              	var tr = document.getElementById(tr_name);
					var img_expand = document.getElementById(tr_name+'_expand');
					var img_collapse = document.getElementById(tr_name+'_collapse');
					if (img_expand==null) {
						var img_expand = document.getElementById(id+'_expand');
					}
					if (img_collapse==null) {
						var img_collapse = document.getElementById(id+'_collapse');
					}
					if (!(img_collapse==null)) {
						img_collapse.style.display = (img_collapse.style.display == 'none') ? "inline" : "none";
					}
					if (!(img_expand==null)) {
						img_expand.style.display = (img_expand.style.display == 'none') ? "inline" : "none";
						//define what we will be doing with the rows below this one
						opt = (img_expand.style.display == "inline") ? "collapse" : "expand";
      					collapse = (opt == 'collapse' ? 1 : 0);
      					expand = (opt == 'expand' ? 1 : 0);
					}
//If we included the root for collapsing/expand lets do it:
	      } else if((tr_name.indexOf(id) >= 0) && level>=0 && include_root) {
					found = true;
	            	var tr = document.getElementById(tr_name);
				    current_level = parseInt(tr_name.substr(tr_name.indexOf('>')+1,tr_name.indexOf('<')-tr_name.indexOf('>')-1));

                    if (collapse) {
                        if (navigator.family == "gecko" || navigator.family == "opera"){
                        	  //if root mode is 1 hide, if not then don't do a thing
							  if ((include_root == 1 && level == 0) || (current_level > 0)) {            
	                              tr.style.visibility = "collapse";
	                              tr.style.display = "none";
	                          }
                              var img_expand = document.getElementById(tr_name+'_expand');
                              var img_collapse = document.getElementById(tr_name+'_collapse');
                              if (img_expand==null) {
                              		var img_expand = document.getElementById(id+'_expand');
                              }
                              if (img_collapse==null) {
                              		var img_collapse = document.getElementById(id+'_collapse');
                              }
							  if (!(img_collapse==null)) {
                              		img_collapse.style.display = "none";
                              }
							  if (!(img_expand==null)) {
                              		img_expand.style.display = "inline";
                              }
                    	} else {
                        	  //if root mode is 1 hide, if not then don't do a thing
							  if ((include_root == 1 && level == 0) || (current_level > 0)) {            
                              		tr.style.display = "none";
                              }
                              var img_expand = document.getElementById(tr_name+'_expand');
                              var img_collapse = document.getElementById(tr_name+'_collapse');
                              if (img_expand==null) {
                              		var img_expand = document.getElementById(id+'_expand');
                              }
                              if (img_collapse==null) {
                              		var img_collapse = document.getElementById(id+'_collapse');
                              }
							  if (!(img_collapse==null)) {
                              		img_collapse.style.display = "none";
                              }
							  if (!(img_expand==null)) {
                              		img_expand.style.display = "inline";
                              }
                        }
                  } else {
                        if (navigator.family == "gecko" || navigator.family == "opera"){            
                        	  //if root mode is 1 hide, if not then don't do a thing
							  if ((include_root == 1 && level == 0) || (current_level > 0)) {            
	                              tr.style.visibility = "visible";
	                              tr.style.display = "";
	                          }
                              var img_expand = document.getElementById(tr_name+'_expand');
                              var img_collapse = document.getElementById(tr_name+'_collapse');
                              if (img_expand==null) {
                              		var img_expand = document.getElementById(id+'_expand');
                              }
                              if (img_collapse==null) {
                              		var img_collapse = document.getElementById(id+'_collapse');
                              }
							  if (!(img_collapse==null)) {
                              		img_collapse.style.display = "inline";
                              }
							  if (!(img_expand==null)) {
                              		img_expand.style.display = "none";
                              }
                        } else {
                        	  //if root mode is 1 hide, if not then don't do a thing
							  if ((include_root == 1 && level == 0) || (current_level > 0)) {            
                              		tr.style.display = "";
                              }
                              var img_expand = document.getElementById(tr_name+'_expand');
                              var img_collapse = document.getElementById(tr_name+'_collapse');
                              if (img_expand==null) {
                              		var img_expand = document.getElementById(id+'_expand');
                              }
                              if (img_collapse==null) {
                              		var img_collapse = document.getElementById(id+'_collapse');
                              }
							  if (!(img_collapse==null)) {
                              		img_collapse.style.display = "inline";
                              }
							  if (!(img_expand==null)) {
                              		img_expand.style.display = "none";
                              }
                        }
                  }
//Now that we found the right root or we want to act on every row (collapse all/expand all) then
//lets handle expand collapses of leveled rows (like tasks dynamics) - THIS "ELSEIF" HANDLES THE ROWS THEMSELVES
	      } else if(level>0 && !done && (found || level==0)) {
	      		  //Lets catch the level
				  current_level = parseInt(tr_name.substr(tr_name.indexOf('>')+1,tr_name.indexOf('<')-tr_name.indexOf('>')-1));
				  //If the current_level is equal or lower then we are done and we are already on another tree.
				  if (current_level < level) {
				  		done = true;
				  		//And don't waste more time on this function and get back to the application
				  		return;
				  }	else {
		              var tr = document.getElementById(tr_name);
	                  if (collapse) {
	                        if (navigator.family == "gecko" || navigator.family == "opera"){            
	                              tr.style.visibility = "collapse";
	                              tr.style.display = "none";
	                              var img_expand = document.getElementById(tr_name+'_expand');
	                              var img_collapse = document.getElementById(tr_name+'_collapse');
	                              if (img_expand==null) {
	                              		var img_expand = document.getElementById(id+'_expand');
	                              }
	                              if (img_collapse==null) {
	                              		var img_collapse = document.getElementById(id+'_collapse');
	                              }
								  if (!(img_collapse==null)) {
	                              		img_collapse.style.display = "none";
	                              }
								  if (!(img_expand==null)) {
	                              		img_expand.style.display = "inline";
	                              }
	                        } else {
	                              tr.style.display = "none";
	                              var img_expand = document.getElementById(tr_name+'_expand');
	                              var img_collapse = document.getElementById(tr_name+'_collapse');
	                              if (img_expand==null) {
	                              		var img_expand = document.getElementById(id+'_expand');
	                              }
	                              if (img_collapse==null) {
	                              		var img_collapse = document.getElementById(id+'_collapse');
	                              }
								  if (!(img_collapse==null)) {
	                              		img_collapse.style.display = "none";
	                              }
								  if (!(img_expand==null)) {
	                              		img_expand.style.display = "inline";
	                              }
	                        }
	                  } else {
	                        if (navigator.family == "gecko" || navigator.family == "opera"){            
	                              tr.style.visibility = "visible";
	                              tr.style.display = "";
	                              var img_expand = document.getElementById(tr_name+'_expand');
	                              var img_collapse = document.getElementById(tr_name+'_collapse');
	                              if (img_expand==null) {
	                              		var img_expand = document.getElementById(id+'_expand');
	                              }
	                              if (img_collapse==null) {
	                              		var img_collapse = document.getElementById(id+'_collapse');
	                              }
								  if (!(img_collapse==null)) {
	                              		img_collapse.style.display = "inline";
	                              }
								  if (!(img_expand==null)) {
	                              		img_expand.style.display = "none";
	                              }
	                        } else {
	                              tr.style.display = "";
	                              var img_expand = document.getElementById(tr_name+'_expand');
	                              var img_collapse = document.getElementById(tr_name+'_collapse');
	                              if (img_expand==null) {
	                              		var img_expand = document.getElementById(id+'_expand');
	                              }
	                              if (img_collapse==null) {
	                              		var img_collapse = document.getElementById(id+'_collapse');
	                              }
								  if (!(img_collapse==null)) {
	                              		img_collapse.style.display = "inline";
	                              }
								  if (!(img_expand==null)) {
	                              		img_expand.style.display = "none";
	                              }
	                        }
	                  }
	            }
		  }		    
      }
}

function expandAll(id, table_name) {
	expand_colapse(id, table_name, 'expand');
}

function collapseAll(id, table_name) {
	expand_colapse(id, table_name, 'collapse');
}

// Ajax support functions
function addOption(selectId, val, txt) {
      var objOption = new Option(txt, val);
      document.getElementById(selectId).options.add(objOption);
}

/*
xajax.loadingFunction = 
    function(){
               xajax.$('loadingMessage').style.display='block';
               if (navigator.userAgent.indexOf('MSIE') != -1) {
                   xajax.$('w2PfadeDIV').style['filter'] = 'alpha(opacity=30)';
               } else {
                   xajax.$('w2PfadeDIV').style.opacity=.3;
               }
    };
    
function hideLoadingMessage()
{
    xajax.$('loadingMessage').style.display = 'none';
    if (navigator.userAgent.indexOf('MSIE') != -1) {
       xajax.$('w2PfadeDIV').style['filter'] = 'alpha(opacity=100)';
    } else {
       xajax.$('w2PfadeDIV').style.opacity=1;        
    }
}
xajax.doneLoadingFunction = hideLoadingMessage;
*/
if (typeof(xajax) != 'undefined') {
	xajax.callback.global.onRequest = 
	    function(){
	               if (navigator.userAgent.indexOf('MSIE') != -1) {
	                  AllByTag.hide("SELECT");   
	               }
	               xajax.$('loadingMessage').style.display='block';
	               if (navigator.userAgent.indexOf('MSIE') != -1) {
	                   xajax.$('w2PfadeDIV').style['filter'] = 'alpha(opacity=30)';
	               } else {
	                   xajax.$('w2PfadeDIV').style.opacity=.3;
	               }
	    };
	    
	function hideLoadingMessage() {
	    xajax.$('loadingMessage').style.display = 'none';
	    if (navigator.userAgent.indexOf('MSIE') != -1) {
	       xajax.$('w2PfadeDIV').style['filter'] = 'alpha(opacity=100)';
	    } else {
	       xajax.$('w2PfadeDIV').style.opacity=1;        
	    }
	    if (navigator.userAgent.indexOf('MSIE') != -1) {
	      AllByTag.show("SELECT");   
	    }
	}
	xajax.callback.global.onComplete = hideLoadingMessage;
}

function emptyCombo(combo) {
    combo.options.length = 0;
}

function AllByTag() {
  // no actions
}

AllByTag.setStyleDisplay = function(tagName,value) {
  var elements = document.getElementsByTagName(tagName);
  for (var i = 0; i < elements.length; i++) {
    elements[i].style.display = value;
  }
}

AllByTag.show = function(tagName,dispType) {
  AllByTag.setStyleDisplay(tagName, dispType ? dispType : 'inline');
}

AllByTag.hide = function(tagName,dispType) {
  AllByTag.setStyleDisplay(tagName, 'none');
}