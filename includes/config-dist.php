<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

/*
    Copyright (c) 2007-2014 The web2Project Development Team <w2p-developers@web2project.net>
    Copyright (c) 2003-2007 The dotProject Development Team <core-developers@dotproject.net>

    * *	 * INSTALLATION INSTRUCTIONS * * *
    Point your browser to install/index.php and follow the prompts. It is no
    longer necessary to manually create config.php unless the web server cannot
    write to the includes directory.

    Regardless, this file is used as a template for the autogeneration of the
    config.php during the installation process and should not by modified by hand.
*/

$w2Pconfig['dbtype'] = '[DBTYPE]'; // ONLY MySQL is supported at present
$w2Pconfig['dbchar'] = '[DBCHAR]'; // The character set used in the database
$w2Pconfig['dbhost'] = '[DBHOST]';
$w2Pconfig['dbname'] = '[DBNAME]'; // Your web2Project Database Name
$w2Pconfig['dbuser'] = '[DBUSER]'; // Your MySQL Username
$w2Pconfig['dbpass'] = '[DBPASS]'; // Your MySQL Password
$w2Pconfig['dbprefix'] = ''; // Table prefixes are not supported: leave this empty

// set this value to true to use persistent database connections
$w2Pconfig['dbpersist'] = false;

// Gantt charts draw Chinese, Japanese and Korean text with this font file
// (.ttf or .ttc). If unset, common system CJK fonts such as Noto Sans CJK
// (the fonts-noto-cjk package) are used when installed.
// $w2Pconfig['gantt_cjk_font'] = '/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc';

/***************** Configuration for DEVELOPERS use only! ******/
// Root directory and base_url are automatically set to avoid
// getting them wrong.  They are not the same.
$w2Pconfig['root_dir'] = W2P_BASE_DIR;
$w2Pconfig['base_url'] = W2P_BASE_URL;
