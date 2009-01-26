<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

/*
	Copyright (c) 2007-2008 The web2Project Development Team <w2p-developers@web2project.net>
	Copyright (c) 2003-2005 The dotProject Development Team <core-developers@dotproject.net>

	* *	 * INSTALLATION INSTRUCTIONS * * *
	Point your browser to install/index.php and follow the prompts. It is no 
	longer necessary to manually create config.php unless the web server cannot 
	write to the includes directory.
		
	Regardless, this file is used as a template for the autogeneration of the 
	config.php during the installation process and should not by modified by hand.
*/

$w2Pconfig['dbtype'] = '[DBTYPE]'; // ONLY MySQL is supported at present
$w2Pconfig['dbhost'] = '[DBHOST]';
$w2Pconfig['dbname'] = '[DBNAME]'; // Change to match your web2Project Database Name
$w2Pconfig['dbuser'] = '[DBUSER]'; // Change to match your MySQL Username
$w2Pconfig['dbpass'] = '[DBPASS]'; // Change to match your MySQL Password
$w2Pconfig['dbprefix'] = ''; // Change to match the prefix used for db table names, example: $w2Pconfig['dbprefix'] = 'w2p_prefix_';

// set this value to true to use persistent database connections
$w2Pconfig['dbpersist'] = false;

/***************** Configuration for DEVELOPERS use only! ******/
// Root directory and base_url are automatically set to avoid
// getting them wrong.  They are not the same.
$w2Pconfig['root_dir'] = W2P_BASE_DIR;
$w2Pconfig['base_url'] = W2P_BASE_URL;