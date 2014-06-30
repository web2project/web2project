<?php
/**
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

/**
 * Corrects the charset name if needed be
 * TODO:  Remove for v4.0 - dkc 08 May 2011
 *
 * @deprecated
 */
function w2PcheckCharset()
{
    trigger_error("The w2PcheckCharset function has been deprecated and will be removed in v4.0.", E_USER_NOTICE );

    return 'utf-8';
}

/**
 * Convert string char (ref : Vbulletin #3987)
 * TODO:  Remove for v4.0 - caseydk 20 September 2012
 *
 * @deprecated
 */
function strJpGraph($text)
{
    global $locale_char_set;
    trigger_error("The strJpGraph function has been deprecated and will be removed in v4.0.", E_USER_NOTICE );
    if ( $locale_char_set=='utf-8' && function_exists("utf8_decode") ) {
        return utf8_decode($text);
    } else {
        return $text;
    }
}

/**
 * Casts the $a parameter to an integer
 * TODO:  Remove for v4.0 - caseydk 26 August 2011
 *
 * @deprecated
 */
function atoi($a)
{
    trigger_error("The atoi function has been deprecated and will be removed in v4.0. Please use (int) instead.", E_USER_NOTICE );

    return $a + 0;
}

/**
 * This was used to check if a $link was a URL. Since some users use local
 *   network resources, this was failing miserably and making our lives difficult.
 * TODO:  Remove for v4.0 - caseydk 01 September 2011
 *
 * @deprecated
*/
function w2p_check_url()
{
    trigger_error("The w2p_check_url function has been deprecated and will be removed in v4.0. There is no replacement.", E_USER_NOTICE );

    return true;
}

/**
 * This was used to remove zero length strings from the contacts array in
 *   modules/public/contact_selector.php but can be replaced with array_filter.
 * TODO:  Remove for v4.0 - caseydk 28 December 2011
 *
 * @deprecated
 *
 */
function remove_invalid($arr)
{
    trigger_error("The remove_invalid function has been deprecated and will be removed in v4.0. Please use array_filter instead.", E_USER_NOTICE );

    return array_filter($arr);
}

/**
 * This was a recursive function to generate the task list tree used in the
 *   Project Designer module. It was the exact duplciate of the findchild_pr,
 *   so this version has been deprecated.
 * Original location: modules/projectdesigner/projectdesigner.class.php
 * TODO:  Remove for v4.0 - caseydk 21 March 2012
 *
 * @deprecated
 *
 */
function findchild_pr(&$tarr, $parent, $level = 0)
{
    trigger_error("The findchild_pr function has been deprecated and will be removed in v4.0. Please use findchild_new instead.", E_USER_NOTICE );

    findchild_new($tarr, $parent, $level = 0);
}

/**
 * This was a function that simply did a str_repeat.. no clue why it didn't
 *   just use the regular str_repeat.
 * TODO:  Remove for v4.0 - caseydk 22 March 2012
 *
 * @deprecated
 */
// from modules/tasks/addedit.php and modules/projectdesigners/vw_actions.php
function getSpaces($amount)
{
    trigger_error("The getSpaces function has been deprecated and will be removed in v4.0. Please use str_repeat instead.", E_USER_NOTICE );

    return str_repeat('&nbsp;', $amount);
}
/**
 * This was used to retrieve and display the child departments starting from
 *   any ancestor. More importantly, it displays the relationship visually
 *   with little icons. There are a couple other variations of this function.
 * TODO:  Remove for v4.0 - caseydk 13 Feb 2012
 *
 * @deprecated
 */
// From:  modules/companies/vw_depts.php
function findchilddept_comp(&$tarr, $parent, $level = 0)
{
    trigger_error("The findchilddept_comp function has been deprecated and will be removed in v4.0. There is no replacement.", E_USER_NOTICE );

    $level++;
    $n = count($tarr);
    for ($x = 0; $x < $n; $x++) {
        if ($tarr[$x]['dept_parent'] == $parent && $tarr[$x]['dept_parent'] != $tarr[$x]['dept_id']) {
            echo showchilddept_comp($tarr[$x], $level);
            findchilddept_comp($tarr, $tarr[$x]['dept_id'], $level);
        }
    }
}

/**
 * This was used to display the child departments one row at a time. More
 *   importantly, it displays the relationship visually with little icons.
 *   There are a couple other variations of this function.
 * TODO:  Remove for v4.0 - caseydk 13 Feb 2012
 *
 * @deprecated
 */
// From:  modules/companies/vw_depts.php
function showchilddept_comp(&$a, $level = 0)
{
    trigger_error("The showchilddept_comp function has been deprecated and will be removed in v4.0. There is no replacement.", E_USER_NOTICE );

    global $AppUI;
    $s = '
    <td>
        <a href="./index.php?m=departments&amp;a=addedit&amp;dept_id=' . $a["dept_id"] . '" title="' . $AppUI->_('edit') . '">
            ' . w2PshowImage('icons/stock_edit-16.png', 16, 16, '') . '
    </td>
    <td>';

    for ($y = 0; $y < $level; $y++) {
        if ($y + 1 == $level) {
            $s .= '<img src="' . w2PfindImage('corner-dots.gif') . '" width="16" height="12" border="0" alt="">';
        } else {
            $s .= '<img src="' . w2PfindImage('shim.gif') . '" width="16" height="12" border="0" alt="">';
        }
    }

    $s .= '<a href="./index.php?m=departments&a=view&dept_id=' . $a['dept_id'] . '">' . $a['dept_name'] . '</a>';
    $s .= '</td>';
    $s .= '<td align="center">' . ($a['dept_users'] ? $a['dept_users'] : '') . '</td>';

    return '<tr>' . $s . '</tr>';
}

/**
 * This was used to designate if a task was on not started, late, on time, or
 *   some other combination thereof.
 * TODO:  Remove for v4.0 - caseydk 04 Mar 2012
 *
 * @deprecated
 */
function taskstyle_pd($task)
{
    trigger_error("The taskstyle_pd function has been deprecated and will be removed in v4.0. Use w2pFindTaskComplete() instead.", E_USER_NOTICE );

    $style = w2pFindTaskComplete($task['task_start_date'], $task['task_end_date'], $task['task_percent_complete']);

    switch ($style) {
        case 'done':
        case 'late':
        case 'notstarted':
            $style = 'task_'.$style;
            break;
        case 'active':
            $style = 'task_started';
            break;
        default:
            $style = 'task_future';
            break;
    }
}

/**
 * TODO:  Remove for v4.0 - caseydk 20 September 2012
 *
 * @deprecated
 */
function getStructuredProjects($original_project_id = 0, $project_status = -1, $active_only = false)
{
    trigger_error("getStructuredProjects has been deprecated in v3.0 and will be removed in v4.0. Please use CProject->getStructuredProjects() instead.", E_USER_NOTICE);

    $project = new CProject();
    $project->project_original_parent = $original_project_id;
    $project->project_status = $project_status;

    return $project->getStructuredProjects($active_only);
}

/**
 * Deprecated in favor of buildTaskTree which doesn't use any globals.
 * TODO:  Remove for v4.0 - caseydk 20 September 2012
 *
 * @deprecated
 */
function constructTaskTree($task_data, $depth = 0)
{
    global $projTasks, $all_tasks, $parents, $task_parent, $task_id;
    trigger_error("The constructTaskTree function has been deprecated and will be removed in v4.0. Use buildTaskTree() instead.", E_USER_NOTICE );

    return buildTaskTree($task_data, $depth, $projTasks, $all_tasks, $parents, $task_parent, $task_id);
}
/**
 * Deprecated in favor of buildTaskTree which doesn't use any globals.
 * TODO:  Remove for v4.0 - caseydk 20 September 2012
 *
 * @deprecated
 */
function constructTaskTree_pd($task_data, $parents, $all_tasks, $depth = 0)
{
    global $projTasks, $all_tasks, $task_parent, $task_id;
    trigger_error("The constructTaskTree_pd function has been deprecated and will be removed in v4.0. Use buildTaskTree() instead.", E_USER_NOTICE );

    return buildTaskTree($task_data, $depth, $projTasks, $all_tasks, $parents, $task_parent, $task_id);
}

// from modules/tasks/tasks.class.php
/**
 * Used to check if a user has task_access to see the task in task list context
 * (This function was optimized to try to use the DB the least possible)
 * TODO:  Remove for v4.0 - caseydk 20 September 2012
 *
 * @param mixed $task_id
 * @param mixed $task_access
 * @param mixed $task_owner
 * @return true if user has task access to it, or false if he doesn't
 *
 * @deprecated
 */
function canTaskAccess($task_id)
{
    trigger_error("canTaskAccess has been deprecated in v3.0 and will be removed by v4.0. Please use CTask->canAccess() instead.", E_USER_NOTICE);

    global $AppUI;

    $task = new CTask();
    $task->load($task_id);

    return $task->canAccess($AppUI->user_id);
}

/**
 * TODO:  Remove for v4.0 - caseydk 20 September 2012
 *
 * @deprecated
 */
function getProjects()
{
    trigger_error("getProjects() has been deprecated in v3.0 and will be removed in v4.0. Please use CProject->getProjects() instead.", E_USER_NOTICE);

    $project = new CProject();

    return $project->getProjects();
}

/**
 * TODO:  Remove for v4.0 - caseydk 20 September 2012
 *
 * @deprecated
 */
function show_st_project(&$a, $level = 0)
{
    trigger_error("show_st_project() has been deprecated in v3.0 and will be removed in v4.0. There is no replacement.", E_USER_NOTICE);

    global $st_projects_arr;
    $st_projects_arr[] = array($a, $level);
}

/**
 * TODO:  Remove for v4.0 - caseydk 20 September 2012
 *
 * @deprecated
 */
function find_proj_child(&$tarr, $parent, $level = 0)
{
    trigger_error("find_proj_child() has been deprecated in v3.0 and will be removed in v4.0. There is no replacement.", E_USER_NOTICE);

    $level++;
    $n = count($tarr);
    for ($x = 0; $x < $n; $x++) {
        if ($tarr[$x]['project_parent'] == $parent && $tarr[$x]['project_parent'] != $tarr[$x]['project_id']) {
            show_st_project($tarr[$x], $level);
            find_proj_child($tarr, $tarr[$x]['project_id'], $level);
        }
    }
}

/**
 * TODO:  Remove for v4.0 - caseydk 22 February 2013
 *
 * @deprecated
 */
function makePass()
{
    trigger_error("makePass() has been deprecated in v3.0 and will be removed in v4.0. Use w2p_Authenticators_SQL->createNewPassword instead.", E_USER_NOTICE);

    $auth = new w2p_Authenticators_SQL();

    return $auth->createNewPassword();
}

/**
 * The original method didn't return anything..
 *
 * @todo Remove for v4.0 - caseydk 06 March 2013
 * @deprecated
 */
function get_dependencies_pd($task_id)
{
    trigger_error("get_dependencies_pd() has been deprecated in v3.0 and will be removed in v4.0. Use CTask->getDependencyList instead.", E_USER_NOTICE);

    $task = new CTask();

    return $task->getDependencyList($task_id);
}

/**
 * Rebuilt this as a class method to override the basic layout, etc information.
 *   While this is overkill for the core system, it's absolutely necessary for
 *   good custom theming.
 *
 * @param type $AppUI
 * @param type $rootTag
 * @param type $innerTag
 * @param type $dividingToken
 * @param type $m
 * @return type
 */
function buildHeaderNavigation($AppUI, $rootTag = '', $innerTag = '', $dividingToken = '', $m = '')
{
    trigger_error("The buildHeaderNavigation function has been deprecated in v3.1 and will be removed by v5.0. Please use w2p_Theme_Base->buildHeaderNavigation() instead.", E_USER_NOTICE );

    $uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : w2PgetConfig('host_style');
    $style = 'style_' . str_replace('-', '', $uistyle);
    $theme = new $style($AppUI, $m);

    return $theme->buildHeaderNavigation($rootTag, $innerTag, $dividingToken);
}

if (!function_exists('styleRenderBoxTop')) {
    /**
     * @deprecated
     */
    function styleRenderBoxTop()
    {
        trigger_error("styleRenderBoxTop() has been deprecated in v3.1 and will be removed by v5.0. Use AppUI->getTheme()->styleRenderBoxTop instead.", E_USER_NOTICE);

        global $AppUI;
        echo $AppUI->getTheme()->styleRenderBoxTop();
    }
}

if (!function_exists('styleRenderBoxBottom')) {
    /**
     * @deprecated
     */
    function styleRenderBoxBottom()
    {
        trigger_error("styleRenderBoxBottom() has been deprecated in v3.1 and will be removed by v5.0. Use AppUI->getTheme()->styleRenderBoxBottom instead.", E_USER_NOTICE);

        global $AppUI;
        echo $AppUI->getTheme()->styleRenderBoxBottom();
    }
}

/** @deprecated */
function w2PgetCleanParam(&$arr, $name, $def = null)
{
    trigger_error("w2PgetCleanParam() has been deprecated in v3.1 and will be removed in v4.0. Use w2PgetParam() instead.", E_USER_NOTICE);

    return w2PgetParam($arr, $name, $def);
}

/** @deprecated */
function contextHelp($title, $link = '')
{
    trigger_error("contextHelp() has been deprecated in v3.1 and will be removed by v5.0", E_USER_NOTICE);

    return w2PcontextHelp($title, $link);
}

function w2PcontextHelp($title, $link = '')
{
    global $AppUI;
    trigger_error("w2PcontextHelp() has been deprecated in v3.1 and will be removed by v5.0", E_USER_NOTICE);

    return '<a href="#' . $link . '" onclick="javascript:window.open(\'?m=help&amp;dialog=1&amp;hid=' . $link . '\', \'contexthelp\', \'width=400, height=400, left=50, top=50, scrollbars=yes, resizable=yes\')">' . $AppUI->_($title) . '</a>';
}


function w2PgetUsername($username)
{
    trigger_error("w2PgetUsername() has been deprecated in v3.1 and will be removed by v5.0. Please use CContact::getContactByUsername() instead.", E_USER_NOTICE);

    return CContact::getContactByUsername($username);
}

function w2PgetUsernameFromID($userId)
{
    trigger_error("w2PcontextHelp() has been deprecated in v3.1 and will be removed by v5.0. Please use CContact::getContactByUserid() instead.", E_USER_NOTICE);

    return CContact::getContactByUserid($userId);
}

/** @deprecated */
function showtask_pd(&$arr, $level = 0, $today_view = false)
{
    trigger_error("showtask_pd() has been deprecated in v3.1 and will be removed by v5.0. Please use showtask_new() instead.", E_USER_NOTICE);

    return showtask_new($arr, $level, $today_view);
}

/** @deprecated */
function showtask_pr(&$arr, $level = 0, $today_view = false)
{
    trigger_error("showtask_pr() has been deprecated in v3.1 and will be removed by v5.0. Please use showtask_new() instead.", E_USER_NOTICE);

    return showtask_new($arr, $level, $today_view);
}

/** @deprecated */
function showtask(&$arr, $level = 0, $notUsed = true, $today_view = false)
{
    trigger_error("showtask() has been deprecated in v3.1 and will be removed by v5.0. Please use showtask_new() instead.", E_USER_NOTICE);

    return showtask_new($arr, $level, $today_view);
}

function findchild(&$tarr, $parent, $level = 0)
{
    trigger_error("findchild() has been deprecated in v3.1 and will be removed by v5.0. Please use findchild_new() instead.", E_USER_NOTICE);

    findchild_new($tarr, $parent, $level);
}

function findchild_pd(&$tarr, $parent, $level = 0)
{
    trigger_error("findchild_pd() has been deprecated in v3.1 and will be removed by v5.0. Please use findchild_new() instead.", E_USER_NOTICE);

    findchild_new($tarr, $parent, $level);
}

/**
 * @deprecated
 */
function cleanText($text)
{
    trigger_error("cleanText() has been deprecated in v3.2 and will be removed by v5.0. There is no replacement.", E_USER_NOTICE);

    return $text;
}

/** @deprecated since 3.2 */
function resource_presave()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed by v5.0. There is no replacement.", E_USER_NOTICE);
}

/** @deprecated since 3.2 */
function resource_postsave()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed by v5.0. There is no replacement.", E_USER_NOTICE);
}

/** @deprecated since 3.2 */
function showFVar(&$var, $title = '')
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed by v5.0. There is no replacement.", E_USER_NOTICE);
    echo '<h1>' . $title . '</h1>';
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}


function w2PsessionOpen()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0.", E_USER_NOTICE );

    return true;
}

function w2PsessionClose()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0.", E_USER_NOTICE );

    return true;
}

function w2PsessionRead($id)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_System_Session->read instead.", E_USER_NOTICE );

    $session = new w2p_System_Session();
    return $session->read($id);
}

function w2PsessionWrite($id, $data)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_System_Session->write instead.", E_USER_NOTICE );

    $session = new w2p_System_Session();
    return $session->write($id, $data);
}

function w2PsessionDestroy($id)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_System_Session->destroy instead.", E_USER_NOTICE );

    $session = new w2p_System_Session();
    return $session->destroy($id);
}

function w2PsessionGC()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_System_Session->gc instead.", E_USER_NOTICE );

    $session = new w2p_System_Session();
    return $session->gc();
}

function w2PsessionConvertTime($key)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_System_Session->convertTime instead.", E_USER_NOTICE );

    $session = new w2p_System_Session();
    return $session->convertTime($key);
}

function w2PsessionStart()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_System_Session->start instead.", E_USER_NOTICE );

    $session = new w2p_System_Session();
    return $session->start();
}


function db_connect($host = 'localhost', $dbname, $user = 'root', $passwd = '', $persist = false)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_connect instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    $connection->connect($host, $dbname, $user, $passwd, $persist);
}

function db_error()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_error instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->error();
}

function db_errno()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_errno instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->errno();
}

function db_insert_id()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_insert_id instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->insert_id();
}

function db_exec($sql)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_exec instead.", E_USER_NOTICE );

    global $db, $w2p_performance_dbtime, $w2p_performance_old_dbqueries;

    $connection = new w2p_Database_Connection($db);
    return $connection->exec($sql, $w2p_performance_dbtime, $w2p_performance_old_dbqueries);
}

function db_free_result($cur)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_free_result instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    $connection->free_result($cur);
}

function db_num_rows($qid)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_num_rows instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->num_rows($qid);
}

function db_fetch_row(&$qid)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_fetch_row instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->fetch_row($qid);
}

function db_fetch_assoc(&$qid)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_fetch_assoc instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->fetch_assoc($qid);
}

function db_fetch_array(&$qid)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_fetch_array instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->fetch_array($qid);
}

function db_fetch_object($qid)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_fetch_object instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->fetch_object($qid);
}

function db_escape($str)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_escape instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->escape($str);
}

function db_version()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_version instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->version();
}

function db_unix2dateTime($time)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_unix2dateTime instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->unix2dateTime($time);
}

function db_dateTime2unix($time)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_dateTime2unix instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->dateTime2unix($time);
}

/** @deprecated */
function clash_process(w2p_Core_CAppUI $AppUI)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $AppUI->redirect('m=events');
}

/** @deprecated */
function clash_mail(w2p_Core_CAppUI $AppUI)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $AppUI->redirect('m=events');
}

/** @deprecated */
function clash_accept(w2p_Core_CAppUI $AppUI)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $AppUI->redirect('m=events');
}

/** @deprecated */
function clear_clash()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    return false;
}

/** @deprecated */
function clash_cancel(w2p_Core_CAppUI $AppUI)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $AppUI->redirect('m=events');
}

/** @deprecated */
function smart_slice($arr, $notUsed1, $notUsed2, $notUsed3)
{
    return dumb_slice($arr);
}

/** @deprecated */
function __extract_from_tasks6($q, $history_active)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    return $q;
}