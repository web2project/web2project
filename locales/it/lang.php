<?php /* $Id: lang.php 939 2010-01-23 06:11:13Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/tags/version1.3/locales/it/lang.php $ */
// Entries in the LANGUAGES array are elements that describe the
// countries and language variants supported by this locale pack.
// Elements are keyed by the ISO 2 character language code in lowercase
// followed by an underscore and the 2 character country code in Uppercase.
// Each array element has 4 parts:
// 1. Directory name of locale directory
// 2. English name of language
// 3. Name of language in that language
// 4. Microsoft locale code

$dir = basename(dirname(__file__));

$LANGUAGES['it'] = array($dir, 'Italiano (IT)', 'Italiano (IT)', 'Italiano_ITALIA');