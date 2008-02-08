<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
/*
Copyright (c) 2007-2008 The web2Project Development Team <w2p-developers@web2project.net>
Copyright (c) 2003-2005 The dotProject Development Team <core-developers@dotproject.net>

This file is part of web2Project.

web2Project is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

web2Project is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with web2Project; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

The full text of the GPL is in the COPYING file.
*/

/*
* * * INSTALLATION INSTRUCTIONS * * *

Point your browser to install/index.php and follow the prompts.
It is no longer necessary to manually create this file unless
the web server cannot write to the includes directory.

*/

// DATABASE ACCESS INFORMATION [DEFAULT example]
// Modify these values to suit your local settings

$w2Pconfig['dbtype'] = 'mysql'; // ONLY MySQL is supported at present
$w2Pconfig['dbhost'] = 'localhost';
$w2Pconfig['dbname'] = 'web2project'; // Change to match your web2Project Database Name
$w2Pconfig['dbuser'] = 'w2p_user'; // Change to match your MySQL Username
$w2Pconfig['dbpass'] = 'w2p_pass'; // Change to match your MySQL Password
$w2Pconfig['dbprefix'] = 'w2p_prefix_'; // Change to match the prefix used for db table names

// set this value to true to use persistent database connections
$w2Pconfig['dbpersist'] = false;

/***************** Configuration for DEVELOPERS use only! ******/
// Root directory is now automatically set to avoid
// getting it wrong. It is also deprecated as $baseDir
// is now set in top-level files index.php and fileviewer.php.
// All code should start to use $baseDir instead of root_dir.
$w2Pconfig['root_dir'] = $baseDir;

// Base Url is now automatically set to avoid
// getting it wrong. It is also deprecated as $baseUrl
// is now set in top-level files index.php and fileviewer.php.
// All code should start to use $baseUrl instead of base_url.
$w2Pconfig['base_url'] = $baseUrl;
?>