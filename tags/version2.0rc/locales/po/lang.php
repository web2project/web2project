<?php /* $Id$ $URL$ */
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

$LANGUAGES['po'] = array($dir, 'Polish (Pol)', 'Polski (PL)', 'po', 'utf-8');