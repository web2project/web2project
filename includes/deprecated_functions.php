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
 *
 * @codeCoverageIgnore
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
     * @codeCoverageIgnore
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
     * @codeCoverageIgnore
     */
    function styleRenderBoxBottom()
    {
        trigger_error("styleRenderBoxBottom() has been deprecated in v3.1 and will be removed by v5.0. Use AppUI->getTheme()->styleRenderBoxBottom instead.", E_USER_NOTICE);

        global $AppUI;
        echo $AppUI->getTheme()->styleRenderBoxBottom();
    }
}

/**
 * @deprecated
 * @codeCoverageIgnore
 */
function contextHelp($title, $link = '')
{
    trigger_error("contextHelp() has been deprecated in v3.1 and will be removed by v5.0", E_USER_NOTICE);

    return w2PcontextHelp($title, $link);
}
/**
 * @deprecated
 * @codeCoverageIgnore
 */
function w2PcontextHelp($title, $link = '')
{
    global $AppUI;
    trigger_error("w2PcontextHelp() has been deprecated in v3.1 and will be removed by v5.0", E_USER_NOTICE);

    return '<a href="#' . $link . '" onclick="javascript:window.open(\'?m=help&amp;dialog=1&amp;hid=' . $link . '\', \'contexthelp\', \'width=400, height=400, left=50, top=50, scrollbars=yes, resizable=yes\')">' . $AppUI->_($title) . '</a>';
}
/**
 * @deprecated
 * @codeCoverageIgnore
 */
function w2PgetUsername($username)
{
    trigger_error("w2PgetUsername() has been deprecated in v3.1 and will be removed by v5.0. Please use CContact::getContactByUsername() instead.", E_USER_NOTICE);

    return CContact::getContactByUsername($username);
}
/**
 * @deprecated
 * @codeCoverageIgnore
 */
function w2PgetUsernameFromID($userId)
{
    trigger_error("w2PcontextHelp() has been deprecated in v3.1 and will be removed by v5.0. Please use CContact::getContactByUserid() instead.", E_USER_NOTICE);

    return CContact::getContactByUserid($userId);
}
/**
 * @deprecated
 * @codeCoverageIgnore
 */
function showtask_pd(&$arr, $level = 0, $today_view = false)
{
    trigger_error("showtask_pd() has been deprecated in v3.1 and will be removed by v5.0. Please use showtask_new() instead.", E_USER_NOTICE);

    return showtask_new($arr, $level, $today_view);
}
/**
 * @deprecated
 * @codeCoverageIgnore
 */
function showtask_pr(&$arr, $level = 0, $today_view = false)
{
    trigger_error("showtask_pr() has been deprecated in v3.1 and will be removed by v5.0. Please use showtask_new() instead.", E_USER_NOTICE);

    return showtask_new($arr, $level, $today_view);
}
/**
 * @deprecated
 * @codeCoverageIgnore
 */
function showtask(&$arr, $level = 0, $notUsed = true, $today_view = false)
{
    trigger_error("showtask() has been deprecated in v3.1 and will be removed by v5.0. Please use showtask_new() instead.", E_USER_NOTICE);

    return showtask_new($arr, $level, $today_view);
}
/**
 * @deprecated
 * @codeCoverageIgnore
 */
function findchild(&$tarr, $parent, $level = 0)
{
    trigger_error("findchild() has been deprecated in v3.1 and will be removed by v5.0. Please use findchild_new() instead.", E_USER_NOTICE);

    findchild_new($tarr, $parent, $level);
}
/**
 * @deprecated
 * @codeCoverageIgnore
 */
function findchild_pd(&$tarr, $parent, $level = 0)
{
    trigger_error("findchild_pd() has been deprecated in v3.1 and will be removed by v5.0. Please use findchild_new() instead.", E_USER_NOTICE);

    findchild_new($tarr, $parent, $level);
}

/**
 * @deprecated
 * @codeCoverageIgnore
 */
function cleanText($text)
{
    trigger_error("cleanText() has been deprecated in v3.2 and will be removed by v5.0. There is no replacement.", E_USER_NOTICE);

    return $text;
}

/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function resource_presave()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed by v5.0. There is no replacement.", E_USER_NOTICE);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function resource_postsave()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed by v5.0. There is no replacement.", E_USER_NOTICE);
}

/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function showFVar(&$var, $title = '')
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed by v5.0. There is no replacement.", E_USER_NOTICE);
    echo '<h1>' . $title . '</h1>';
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function w2PsessionOpen()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0.", E_USER_NOTICE );

    return true;
}

/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function w2PsessionClose()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0.", E_USER_NOTICE );

    return true;
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function w2PsessionRead($id)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_System_Session->read instead.", E_USER_NOTICE );

    $session = new w2p_System_Session();
    return $session->read($id);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function w2PsessionWrite($id, $data)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_System_Session->write instead.", E_USER_NOTICE );

    $session = new w2p_System_Session();
    return $session->write($id, $data);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function w2PsessionDestroy($id)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_System_Session->destroy instead.", E_USER_NOTICE );

    $session = new w2p_System_Session();
    return $session->destroy($id);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function w2PsessionGC()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_System_Session->gc instead.", E_USER_NOTICE );

    $session = new w2p_System_Session();
    return $session->gc();
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function w2PsessionConvertTime($key)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_System_Session->convertTime instead.", E_USER_NOTICE );

    $session = new w2p_System_Session();
    return $session->convertTime($key);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function w2PsessionStart()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_System_Session->start instead.", E_USER_NOTICE );

    $session = new w2p_System_Session();
    return $session->start();
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_connect($host = 'localhost', $dbname, $user = 'root', $passwd = '', $persist = false)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_connect instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    $connection->connect($host, $dbname, $user, $passwd, $persist);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_error()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_error instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->error();
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_errno()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_errno instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->errno();
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_insert_id()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_insert_id instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->insert_id();
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_exec($sql)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_exec instead.", E_USER_NOTICE );

    global $db, $w2p_performance_dbtime, $w2p_performance_old_dbqueries;

    $connection = new w2p_Database_Connection($db);
    return $connection->exec($sql, $w2p_performance_dbtime, $w2p_performance_old_dbqueries);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_free_result($cur)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_free_result instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    $connection->free_result($cur);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_num_rows($qid)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_num_rows instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->num_rows($qid);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_fetch_row(&$qid)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_fetch_row instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->fetch_row($qid);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_fetch_assoc(&$qid)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_fetch_assoc instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->fetch_assoc($qid);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_fetch_array(&$qid)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_fetch_array instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->fetch_array($qid);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_fetch_object($qid)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_fetch_object instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->fetch_object($qid);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_escape($str)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_escape instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->escape($str);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_version()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_version instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->version();
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_unix2dateTime($time)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_unix2dateTime instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->unix2dateTime($time);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function db_dateTime2unix($time)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. Please use w2p_Database_Connection->db_dateTime2unix instead.", E_USER_NOTICE );

    global $db;

    $connection = new w2p_Database_Connection($db);
    return $connection->dateTime2unix($time);
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function clash_process(w2p_Core_CAppUI $AppUI)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $AppUI->redirect('m=events');
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function clash_mail(w2p_Core_CAppUI $AppUI)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $AppUI->redirect('m=events');
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function clash_accept(w2p_Core_CAppUI $AppUI)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $AppUI->redirect('m=events');
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function clear_clash()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    return false;
}
/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function clash_cancel(w2p_Core_CAppUI $AppUI)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $AppUI->redirect('m=events');
}

/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function smart_slice($arr, $notUsed1, $notUsed2, $notUsed3)
{
    return dumb_slice($arr);
}

/**
 * @deprecated @since 3.2
 * @codeCoverageIgnore
 */
function __extract_from_tasks6($q, $history_active)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v3.2 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    return $q;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function __extract_from_tasks($min_view, $currentTabId, $project_id, $currentTabName, $AppUI)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

//TODO: This whole structure is hard-coded based on the TaskStatus SelectList.
    $task_status = 0;
    if ($min_view && isset($_GET['task_status'])) {
        $task_status = (int)w2PgetParam($_GET, 'task_status', null);
        return $task_status;
    } elseif ($currentTabId == 1 && $project_id) {
        $task_status = -1;
        return $task_status;
    } elseif ($currentTabId > 1 && $project_id) {
        $task_status = $currentTabId - 1;
        return $task_status;
    } elseif (!$currentTabName) {
        // If we aren't tabbed we are in the tasks list.
        $task_status = (int)$AppUI->getState('inactive');
        return $task_status;
    }
    return $task_status;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function __extract_from_tasks1()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

//subquery the parent state
    $sq = new w2p_Database_Query;
    $sq->addTable('tasks', 'stasks');
    $sq->addQuery('COUNT(stasks.task_id)');
    $sq->addWhere('stasks.task_id <> tasks.task_id AND stasks.task_parent = tasks.task_id');
    $subquery = $sq->prepare();

    return $subquery;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function __extract_from_tasks5($q, $subquery)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $q->addQuery('tasks.task_id, task_parent, task_name');
    $q->addQuery('task_start_date, task_end_date, task_dynamic');
    $q->addQuery('task_pinned, pin.user_id as pin_user');
    $q->addQuery('ut.user_task_priority');
    $q->addQuery('task_priority, task_percent_complete');
    $q->addQuery('task_duration, task_duration_type');
    $q->addQuery('task_project, task_represents_project');
    $q->addQuery('task_description, task_owner, task_status');
    $q->addQuery('usernames.user_username, usernames.user_id');
    $q->addQuery('assignees.user_username as assignee_username');
    $q->addQuery('count(distinct assignees.user_id) as assignee_count');
    $q->addQuery('co.contact_first_name, co.contact_last_name');
    $q->addQuery('contact_display_name AS contact_name');
    $q->addQuery('contact_display_name AS owner');
    $q->addQuery('task_milestone');
    $q->addQuery('count(distinct f.file_task) as file_count');
    $q->addQuery('tlog.task_log_problem');
    $q->addQuery('task_access');
    $q->addQuery('(' . $subquery . ') AS task_nr_of_children');
    $q->addTable('tasks');

    return $q;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function __extract_from_tasks3($f, $q, $user_id, $task_id, $AppUI)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $f = (($f) ? $f : '');
    if ($task_id) {
        //if we are on a task context make sure we show ALL the children tasks
        $f = 'deepchildren';
    }

    switch ($f) {
        case 'myfinished7days':
            $q->addWhere('ut.user_id = ' . (int) $user_id);
        case 'allfinished7days':
            $q->addTable('user_tasks');
            $q->addWhere('user_tasks.user_id = ' . (int) $user_id);
            $q->addWhere('user_tasks.task_id = tasks.task_id');

            $q->addWhere('task_percent_complete = 100');
            //TODO: use date class to construct date.
            $q->addWhere('task_end_date >= \'' . date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d') - 7, date('Y'))) . '\'');
            break;
        case 'children':
            $q->addWhere('task_parent = ' . (int) $task_id);
            $q->addWhere('tasks.task_id <> ' . $task_id);
            break;
        case 'deepchildren':
            $taskobj = new CTask;
            $taskobj->load((int) $task_id);
            $deepchildren = $taskobj->getDeepChildren();
            $q->addWhere('tasks.task_id IN (' . implode(',', $deepchildren) . ')');
            $q->addWhere('tasks.task_id <> ' . $task_id);
            break;
        case 'myproj':
            $q->addWhere('project_owner = ' . (int) $user_id);
            break;
        case 'mycomp':
            if (!$AppUI->user_company) {
                $AppUI->user_company = 0;
            }
            $q->addWhere('project_company = ' . (int) $AppUI->user_company);
            break;
        case 'myunfinished':
            $q->addTable('user_tasks');
            $q->addWhere('user_tasks.user_id = ' . (int) $user_id);
            $q->addWhere('user_tasks.task_id = tasks.task_id');
            $q->addWhere('(task_percent_complete < 100 OR task_end_date = \'\')');
            break;
        case 'allunfinished':
            $q->addWhere('(task_percent_complete < 100 OR task_end_date = \'\')');
            break;
        case 'unassigned':
            $q->leftJoin('user_tasks', 'ut_empty', 'tasks.task_id = ut_empty.task_id');
            $q->addWhere('ut_empty.task_id IS NULL');
            break;
        case 'taskcreated':
            $q->addWhere('task_creator = ' . (int) $user_id);
            break;
        case 'taskowned':
            $q->addWhere('task_owner = ' . (int) $user_id);
            break;
        case 'all':
            //break;
        default:
            if ($user_id) {
                $q->addTable('user_tasks');
                $q->addWhere('user_tasks.user_id = ' . (int) $user_id);
                $q->addWhere('user_tasks.task_id = tasks.task_id');
            }
            break;
    }

    return $q;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function checkFlag($flag, $perm_type, $old_flag)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    if ($old_flag) {
        return (($flag == PERM_DENY) || // permission denied
            ($perm_type == PERM_EDIT && $flag == PERM_READ) // we ask for editing, but are only allowed to read
        ) ? 0 : 1;
    } else {
        if ($perm_type == PERM_READ) {
            return ($flag != PERM_DENY) ? 1 : 0;
        } else {
            // => $perm_type == PERM_EDIT
            return ($flag == $perm_type) ? 1 : 0;
        }
    }
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function isAllowed($perm_type, $mod, $item_id = 0)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $invert = false;
    switch ($perm_type) {
        case PERM_READ:
            $perm_type = 'view';
            break;
        case PERM_EDIT:
            $perm_type = 'edit';
            break;
        case PERM_ALL:
            $perm_type = 'edit';
            break;
        case PERM_DENY:
            $perm_type = 'view';
            $invert = true;
            break;
    }
    $allowed = getPermission($mod, $perm_type, $item_id);
    if ($invert) {
        return !$allowed;
    }

    return $allowed;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function __extract_from_showtask(&$arr, $level, $today_view, $listTable, $fields = array())
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    return '';
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function __extract_from_showtask2($arr, $level, $today_view, $s, $m, $jsTaskId, $expanded)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $s .= '<td style="width: ' . (($today_view) ? '50%' : '90%') . '" class="data _name">';
    //level
    if ($level == -1) {
        $s .= '...';
    }
    for ($y = 0; $y < $level; $y++) {
        if ($y + 1 == $level) {
            $image = w2PfindImage('corner-dots.gif', $m);
        } else {
            $image = w2PfindImage('shim.gif', $m);
        }
        $s .= '<img src="' . $image . '" width="16" height="12"  border="0" alt=""/>';
    }
    if ($arr['task_description'] && !$arr['task_milestone']) {
        $s .= w2PtoolTip('Task Description', substr($arr['task_description'], 0, 1000), true);
    }

    if (isset($arr['task_nr_of_children']) && $arr['task_nr_of_children']) {
        $is_parent = true;
    } else {
        $is_parent = false;
    }
    if ($arr['task_milestone'] > 0) {
        $s .= '&nbsp;<a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '" ><b>' . $arr['task_name'] . '</b></a>&nbsp;<img src="' . w2PfindImage('icons/milestone.gif') . '" />';
    } elseif ($arr['task_dynamic'] == '1' || $is_parent) {
        $open_link = '<a href="javascript: void(0);"><img onclick="expand_collapse(\'' . $jsTaskId . '\', \'tblProjects\',\'\',' . ($level++) . ');" id="' . $jsTaskId . '_collapse" src="' . w2PfindImage('icons/collapse.gif') . '" class="center" ' . (!$expanded ? 'style="display:none"' : '') . ' /><img onclick="expand_collapse(\'' . $jsTaskId . '\', \'tblProjects\',\'\',' . ($level++) . ');" id="' . $jsTaskId . '_expand" src="' . w2PfindImage('icons/expand.gif') . '" class="center" ' . ($expanded ? 'style="display:none"' : '') . ' /></a>';
        $s .= $open_link;

        if ($arr['task_dynamic'] == '1') {
            $s .= '&nbsp;<a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '" ><b><i>' . $arr['task_name'] . '</i></b></a>';
        } else {
            $s .= '&nbsp;<a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '" >' . $arr['task_name'] . '</a>';
        }
    } else {
        $s .= '&nbsp;<a href="./index.php?m=tasks&amp;a=view&amp;task_id=' . $arr['task_id'] . '" >' . $arr['task_name'] . '</a>';
    }
    if ($arr['task_description'] && !$arr['task_milestone']) {
        $s .= w2PendTip();
    }
    $s .= '</td>';

    return $s;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function showtask_new(&$arr, $level = 0, $today_view = false, $listTable = null, $fields = array())
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    return '';
}
/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function array_csort()
{ //coded by Ichier2003
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $args = func_get_args();
    $marray = array_shift($args);

    if (empty($marray)) {
        return array();
    }

    $i = 0;
    $msortline = 'return(array_multisort(';
    $sortarr = array();
    foreach ($args as $arg) {
        $i++;
        if (is_string($arg)) {
            for ($j = 0, $j_cmp = count($marray); $j < $j_cmp; $j++) {

                /* we have to calculate the end_date via start_date+duration for
                ** end='0000-00-00 00:00:00' before sorting, see mantis #1509:

                ** Task definition writes the following to the DB:
                ** A without start date: start = end = NULL
                ** B with start date and empty end date: start = startdate,
                end = '0000-00-00 00:00:00'
                ** C start + end date: start= startdate, end = end date

                ** A the end_date for the middle task (B) is ('dynamically') calculated on display
                ** via start_date+duration, it may be that the order gets wrong due to the fact
                ** that sorting has taken place _before_.
                */
                if ($marray[$j]['task_end_date'] == '0000-00-00 00:00:00') {
                    $marray[$j]['task_end_date'] = calcEndByStartAndDuration($marray[$j]);
                }

                if ('' == $arg) { continue; }

                $sortarr[$i][] = $marray[$j][$arg];
            }
        } else {
            $sortarr[$i] = $arg;
        }
        if (!is_array($sortarr[$i])) {
            continue;
        }
        $msortline .= '$sortarr[' . $i . '],';
    }
    $msortline .= '$marray));';

    eval($msortline);

    return $marray;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function sort_by_item_title($title, $item_name, $item_type, $a = '')
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    global $AppUI, $project_id, $task_id, $m;
    global $task_sort_item1, $task_sort_type1, $task_sort_order1;
    global $task_sort_item2, $task_sort_type2, $task_sort_order2;

    if ($task_sort_item2 == $item_name) {
        $item_order = $task_sort_order2;
    }
    if ($task_sort_item1 == $item_name) {
        $item_order = $task_sort_order1;
    }

    $s = '';

    if (isset($item_order)) {
        $show_icon = true;
    } else {
        $show_icon = false;
        $item_order = SORT_DESC;
    }

    /* flip the sort order for the link */
    $item_order = ($item_order == SORT_ASC) ? SORT_DESC : SORT_ASC;
    if ($m == 'tasks') {
        $s .= '<a href="./index.php?m=tasks' . (($task_id > 0) ? ('&amp;a=view&amp;task_id=' . $task_id) : $a);
    } elseif ($m == 'calendar') {
        $s .= '<a href="./index.php?m=events&amp;a=day_view';
    } else {
        $s .= '<a href="./index.php?m=projects&amp;bypass=1' . (($project_id > 0) ? ('&amp;a=view&amp;project_id=' . $project_id) : '');
    }
    $s .= '&amp;task_sort_item1=' . $item_name;
    $s .= '&amp;task_sort_type1=' . $item_type;
    $s .= '&amp;task_sort_order1=' . $item_order;
    if ($task_sort_item1 == $item_name) {
        $s .= '&amp;task_sort_item2=' . $task_sort_item2;
        $s .= '&amp;task_sort_type2=' . $task_sort_type2;
        $s .= '&amp;task_sort_order2=' . $task_sort_order2;
    } else {
        $s .= '&amp;task_sort_item2=' . $task_sort_item1;
        $s .= '&amp;task_sort_type2=' . $task_sort_type1;
        $s .= '&amp;task_sort_order2=' . $task_sort_order1;
    }
    $s .= '" class="hdr">' . $AppUI->_($title);
    if ($show_icon) {
        $s .= '&nbsp;<img src="' . w2PfindImage('arrow-' . (($item_order == SORT_ASC) ? 'up' : 'down') . '.gif') . '" />';
    }

    return $s.'</a>';
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function weekDates($display_allocated_hours, $fromPeriod, $toPeriod)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    if ($fromPeriod == -1) {
        return '';
    }
    if (!$display_allocated_hours) {
        return '';
    }

    $s = new w2p_Utilities_Date($fromPeriod);
    $e = new w2p_Utilities_Date($toPeriod);
    $sw = getBeginWeek($s);
    $dw = ceil($e->dateDiff($s) / 7);
    $ew = $sw + $dw;
    $row = '';
    for ($i = $sw; $i <= $ew; $i++) {
        $wn = $s->getWeekofYear() % 52;
        $wn = ($wn != 0) ? $wn : 52;

        $row .= '<th title="' . $s->getYear() . '" nowrap="nowrap">' . $wn . '</th>';
        $s->addSeconds(168 * 3600); // + one week
    }

    return $row;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function showcodes(&$a)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    global $AppUI, $company_id;

    $s = '
<tr>
    <td width=40>
        <a href="?m=system&amp;a=billingcode&amp;company_id=' . $company_id . '&amp;billingcode_id=' . $a['billingcode_id'] . '" title="' . $AppUI->_('edit') . '">
            <img src="' . w2PfindImage('icons/stock_edit-16.png') . '" alt="Edit" /></a>';

    if ($a['billingcode_status'] == 0)
        $s .= '<a href="javascript:delIt2(' . $a['billingcode_id'] . ');" title="' . $AppUI->_('delete') . '">
            <img src="' . w2PfindImage('icons/stock_delete-16.png') . '" alt="Delete" /></a>';

    $s .= '
    </td>
    <td align="left">&nbsp;' . $a['billingcode_name'] . ($a['billingcode_status'] == 1 ? ' (deleted)' : '') . '</td>
    <td nowrap="nowrap" align="center">' . $a['billingcode_value'] . '</td>
    <td nowrap="nowrap">' . $a['billingcode_desc'] . '</td>
</tr>';

    return $s;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function showchilddept(&$a, $level = 1)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    global $department;
    $s = '<option value="' . $a['dept_id'] . '"' . (isset($department) && $department == $a['dept_id'] ? 'selected="selected"' : '') . '>';

    for ($y = 0; $y < $level; $y++) {
        if ($y + 1 == $level) {
            $s .= '';
        } else {
            $s .= '&nbsp;&nbsp;';
        }
    }

    $s .= '&nbsp;&nbsp;' . $a['dept_name'] . '</option>';

    return $s;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function last_file($file_versions, $file_name, $file_project)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $latest = null;

    if (isset($file_versions))
        foreach ($file_versions as $file_version)
            if ($file_version['file_name'] == $file_name && $file_version['file_project'] == $file_project)
                if ($latest == null || $latest['file_version'] < $file_version['file_version'])
                    $latest = $file_version;

    return $latest;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function getHelpdeskFolder()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $q = new w2p_Database_Query();
    $q->addTable('file_folders', 'ff');
    $q->addQuery('file_folder_id');
    $q->addWhere('ff.file_folder_name = \'Helpdesk\'');
    $ffid = $q->loadResult();

    return (int) $ffid;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function get_actual_end_date_pd($task_id, $task)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    global $AppUI;

    $q = new w2p_Database_Query();
    $mods = $AppUI->getActiveModules();

    if (!empty($mods['history']) && canView('history')) {
        $q->addQuery('MAX(history_date) as actual_end_date');
        $q->addTable('history');
        $q->addWhere('history_table=\'tasks\' AND history_item=' . $task_id);
    } else {
        $q->addQuery('MAX(task_log_date) AS actual_end_date');
        $q->addTable('task_log');
        $q->addWhere('task_log_task = ' . (int) $task_id);
    }

    $task_log_end_date = $q->loadResult();

    $edate = $task_log_end_date;

    $edate = ($edate > $task->task_end_date || $task->task_percent_complete == 100) ? $edate : $task->task_end_date;

    return $edate;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function formatTime($uts)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    global $AppUI;
    $date = new w2p_Utilities_Date();
    $date->setDate($uts, DATE_FORMAT_UNIXTIME);

    return $date->format($AppUI->getPref('SHDATEFORMAT'));
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function findTabModules($module, $file = null)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $modlist = array();
    if (!isset($_SESSION['all_tabs']) || !isset($_SESSION['all_tabs'][$module])) {
        return $modlist;
    }

    if (isset($file)) {
        if (isset($_SESSION['all_tabs'][$module][$file]) && is_array($_SESSION['all_tabs'][$module][$file])) {
            $tabs_array = &$_SESSION['all_tabs'][$module][$file];
        } else {
            return $modlist;
        }
    } else {
        $tabs_array = &$_SESSION['all_tabs'][$module];
    }
    foreach ($tabs_array as $tab) {
        if (isset($tab['module'])) {
            $modlist[] = $tab['module'];
        }
    }

    return array_unique($modlist);
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function findCrumbModules($module, $file = null)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $modlist = array();
    if (!isset($_SESSION['all_crumbs']) || !isset($_SESSION['all_crumbs'][$module])) {
        return $modlist;
    }

    if (isset($file)) {
        if (isset($_SESSION['all_crumbs'][$module][$file]) && is_array($_SESSION['all_crumbs'][$module][$file])) {
            $crumbs_array = &$_SESSION['all_crumbs'][$module][$file];
        } else {
            return $modlist;
        }
    } else {
        $crumbs_array = &$_SESSION['all_crumbs'][$module];
    }
    foreach ($crumbs_array as $crumb) {
        if (isset($crumb['module'])) {
            $modlist[] = $crumb['module'];
        }
    }

    return array_unique($modlist);
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function formatHours($hours)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    global $AppUI;

    $hours = (int) $hours;
    $working_hours = w2PgetConfig('daily_working_hours');

    if ($hours < $working_hours) {
        if ($hours == 1) {
            return '1 ' . $AppUI->_('hour');
        } else {
            return $hours . ' ' . $AppUI->_('hours');
        }
    }

    $hoursPart = $hours % $working_hours;
    $daysPart = (int) ($hours / $working_hours);
    if ($hoursPart == 0) {
        if ($daysPart == 1) {
            return '1 ' . $AppUI->_('day');
        } else {
            return $daysPart . ' ' . $AppUI->_('days');
        }
    }

    if ($daysPart == 1) {
        return '1 ' . $AppUI->_('day') . ' ' . $hoursPart . ' ' . $AppUI->_('hr');
    } else {
        return $daysPart . ' ' . $AppUI->_('days') . ' ' . $hoursPart . ' ' . $AppUI->_('hr');
    }
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function w2PwriteDebug($s, $t = '', $f = '?', $l = '?')
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    global $debug;

    $debug_file = W2P_BASE_DIR . '/files/debug.log';
    if ($debug && ($fp = fopen($debug_file, "at"))) {
        fputs($fp, "Debug message from file [$f], line [$l], at: " . strftime('%H:%S'));
        if ($t) {
            fputs($fp, "\n * * $t * *\n");
        }
        fputs($fp, "\n$s\n\n");
        fclose($fp);
    }
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function seconds2HM($sec, $padHours = true)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $HM = "";
    // there are 3600 seconds in an hour, so if we
    // divide total seconds by 3600 and throw away
    // the remainder, we've got the number of hours
    $hours = (int) ($sec / 3600);
    // with the remaining seconds divide them by 60
    // and then round the floating number to get the precise minute
    $minutes = intval(round(($sec - ($hours * 3600)) / 60) ,0);

    if (intval($hours) == 0 && intval($minutes) < 0) {
        $HM .= '-0:';
    } else {
        // add to $hms, with a leading 0 if asked for
        $HM .= ($padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT). ':' : $hours. ':';
    }
    if (intval($hours) < 0 || intval($minutes) < 0) {
        $minutes = $minutes * (-1);
    }
    $HM .= str_pad($minutes, 2, "0", STR_PAD_LEFT);

    return $HM;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function HM2seconds($HM)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    list($h, $m) = explode (":", $HM);
    if (intval($h) > 23 && intval($h) < 0) $h = 0;
    if (intval($m) > 59 && intval($m) < 0) $m = 0;
    $seconds = 0;
    $seconds += (intval($h) * 3600);
    $seconds += (intval($m) * 60);

    return $seconds;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function getTaskLogContacts($obj)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $q = new w2p_Database_Query();
    $q->addTable('task_contacts', 'tc');
    $q->addJoin('contacts', 'c', 'c.contact_id = tc.contact_id', 'inner');
    $q->addWhere('tc.task_id = ' . (int) $obj->task_id);
    $q->addQuery('tc.contact_id');
    $q->addQuery('c.contact_first_name, c.contact_last_name');
    $req = & $q->exec();

    return $req;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function getContactsfromProjects($obj)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $q = new w2p_Database_Query();
    $q->addTable('project_contacts', 'pc');
    $q->addJoin('contacts', 'c', 'c.contact_id = pc.contact_id', 'inner');
    $q->addWhere('pc.project_id = ' . (int) $obj->task_project);
    $q->addQuery('pc.contact_id');
    $q->addQuery('c.contact_first_name, c.contact_last_name');
    $req = & $q->exec();

    return $req;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function __extract_from_ae_depend1($deps)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $q = new w2p_Database_Query;
    $q->addTable('tasks');
    $q->addQuery('task_id, task_name');
    $q->addWhere('task_id IN (' . $deps . ')');
    $taskDep = $q->loadHashList();

    return $taskDep;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function __extract_from_contact_controller($controller)
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    $updatekey = $controller->object->getUpdateKey();
    $notifyasked = w2PgetParam($_POST, 'contact_updateask', 0);
    if ($notifyasked && !strlen($updatekey)) {
        $rnow = new w2p_Utilities_Date();
        $controller->object->contact_updatekey = MD5($rnow->format(FMT_DATEISO));
        $controller->object->contact_updateasked = $rnow->format(FMT_DATETIME_MYSQL);
        $controller->object->contact_lastupdate = '';
        $controller->object->store();
        $controller->object->notify();
    }

    return $controller;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function getReadableModule()
{
    trigger_error(__FUNCTION__ . " has been deprecated in v4.0 and will be removed in v5.0. There is no replacement.", E_USER_NOTICE );

    return 'public';
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function strUTF8Decode($text)
{
    if (extension_loaded('mbstring')) {
        $encoding = mb_detect_encoding($text.' ');
    }
    if (function_exists('iconv')) {
        $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        //iconv($encoding, 'UTF-8', $text);
    } elseif (function_exists('utf8_decode')) {
        $text = utf8_decode($text);
    }
    // mb functions don't seam to work well here for some reason as the output gets corrupted.
    // iconv is doing the job just fine though
    return $text;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function strEzPdf($text)
{
    if (function_exists('iconv') && function_exists('mb_detect_encoding')) {
        $text = iconv(mb_detect_encoding($text." "), 'UTF-8', $text);
    }

    return $text;
}

/**
 * @deprecated @since 4.0
 * @codeCoverageIgnore
 */
function temp_filterArrayForSelectTree($projectData)
{
    unset($projectData['project_id']);
    unset($projectData['project_color_identifier']);
    unset($projectData['project_name']);
    unset($projectData['project_start_date']);
    unset($projectData['project_end_date']);
    unset($projectData['project_company']);
    unset($projectData['project_parent']);

    unset($projectData[1]);
    unset($projectData[3]);
    unset($projectData[4]);
    unset($projectData[5]);
    $projectData[6] = ($projectData[0] == $projectData[6]) ? '' : $projectData[6];

    return array_values($projectData);
}