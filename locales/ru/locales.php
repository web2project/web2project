<?php /* $Id: locales.php 38 2008-02-11 11:38:51Z pedroix $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/locales/de/locales.php $ */
//$locale_char_set = 'iso-8859-15';
$locale_char_set = 'utf-8'; // must be lower-case! because dP doesn't check case-insensitively against this!
// 0 = sunday, 1 = monday
define('LOCALE_FIRST_DAY', 1);
define('LOCALE_TIME_FORMAT', '%H:%M');
define('LOCALE_DATE_FORMAT', '%d.%m.%y');