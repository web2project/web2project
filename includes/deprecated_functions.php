<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
/*
* This file exists in order to identify individual functions which will be
*   deprecated in coming releases.  In the documentation for each function,
*   you must describe two things:
*
*    * the specific version of web2project where the behavior will change; and
*    * a reference to the new/proper way of performing the same functionality.
*
* During Minor releases, this file will grow only to shrink as Major releases
*   allow us to delete functions.
*
* WARNING: This file does not identify class-level method deprecations.
*   In order to find those, you'll have to explore the individual classes.
*/

//There is an issue with international UTF characters, when stored in the database an accented letter
//actually takes up two letters per say in the field length, this is a problem with costcodes since
//they are limited in size so saving a costcode as REDACI�N would actually save REDACI� since the accent takes
//two characters, so lets unaccent them, other languages should add to the replacements array too...
function cleanText($text) {
	//This text file is not utf, its iso so we have to decode/encode
	$text = utf8_decode($text);
	$trade = array('�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'a', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'A', '�' => 'e', '�' => 'e', '�' => 'e', '�' => 'e', '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'E', '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'i', '�' => 'I', '�' => 'I', '�' => 'I', '�' => 'I', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'o', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'O', '�' => 'u', '�' => 'u', '�' => 'u', '�' => 'u', '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'U', '�' => 'N', '�' => 'n');
	$text = strtr($text, $trade);
	$text = utf8_encode($text);

	return $text;
}

//This kludgy function echos children tasks as threads
function showgtask(&$a, $level = 0, $project_id = 0) {
    /* Add tasks to gantt chart */
    global $gantt_arr;
    if ($project_id) {
        $gantt_arr[$project_id][] = array($a, $level);
    } else {
        $gantt_arr[] = array($a, $level);
    }
}

function findgchild(&$tarr, $parent, $level = 0) {
    global $projects;
    $level = $level + 1;
    $n = count($tarr);
    for ($x = 0; $x < $n; $x++) {
        if ($tarr[$x]['task_parent'] == $parent && $tarr[$x]['task_parent'] != $tarr[$x]['task_id']) {
            showgtask($tarr[$x], $level, $tarr[$x]['project_id']);
            findgchild($tarr, $tarr[$x]['task_id'], $level, $tarr[$x]['project_id']);
        }
    }
}
