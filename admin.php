<?php

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

function Monorder_files()
{
    $result = array();
    $dir = opendir(Monorder_dataFolder());
    if ($dir) {
        while (($entry = readdir($dir)) !== false) {
            if ($entry[0] != '.' && pathinfo($entry, PATHINFO_EXTENSION) == 'txt') {
                $result[] = basename($entry, '.txt');
            }
        }
    }
    return $result;
}

function Monorder_fileListItem($file)
{
    global $sn, $plugin_tx;

    $ptx = $plugin_tx['monorder'];
    $href = $sn . '?monorder&amp;admin=&amp;action=plugin_text&amp;monorder_file='
        . $file;
    $action = $sn . '?monorder&amp;admin=&amp;action=delete_event';
    $onsubmit = 'return window.confirm(\'' . $ptx['confirm_delete'] . '\')';
    return <<<EOT
<li>
    <a href="$href">$file</a>
    <form class="monorder_delete" action="$action" method="post" onsubmit="$onsubmit">
        <input type="hidden" name="monorder_file" value="$file">
        <button>$ptx[label_delete]</button>
    </form>
</li>
EOT;
}

function Monorder_fileList()
{
    global $sn, $plugin_tx;

    $ptx = $plugin_tx['monorder'];
    $action = $sn . '?monorder';
    $files = Monorder_files();
    $listItems = array();
    foreach ($files as $file) {
        $listItems[] = Monorder_fileListItem($file);
    }
    $listItems = implode('', $listItems);
    return <<<EOT
<h4>$ptx[events]</h4>
<ul>
$listItems
<li><form action="$action" method="post">
<input type="hidden" name="admin" value="">
<input type="hidden" name="action" value="new_event">
<input type="text" name="monorder_file">
<button>$ptx[new_event]</button>
</form></li>
</ul>
EOT;
}

function Monorder_form()
{
    global $sn, $plugin_tx, $_Monorder_tag;

    $ptx = $plugin_tx['monorder'];
    $file = $_GET['monorder_file'];
    $action = $sn . '?monorder&admin=&action=plugin_textsave&amp;monorder_file=' . $file;
    $_Monorder_tag = $file;
    $free = Monorder_free();
    return <<<EOT
<h4>$file</h4>
<form action="$action" method="post">
    <p>$ptx[free]</p>
    <input type="text" name="monorder_free" value="$free">
    <button>$ptx[save]</button>
</form>
EOT;
}

function Monorder_newEvent()
{
    global $plugin_tx, $_Monorder_tag;

    $o = '';
    if (isset($_POST['monorder_file'])) {
        $_Monorder_tag = $_POST['monorder_file'];
        if (Monorder_write(0)) {
            $o .= '<p>' . $plugin_tx['monorder']['successfully_saved'] . '</p>';
        } else {
            e('cntwriteto', 'file', Monorder_filename());
        }
    }
    $o .= Monorder_fileList();
    return $o;
}

function Monorder_deleteEvent()
{
    global $plugin_tx, $_Monorder_tag;

    $o = '';
    if (isset($_POST['monorder_file'])) {
        $_Monorder_tag = $_POST['monorder_file'];
        $filename = Monorder_filename();
        if (unlink($filename)) {
            $o .= '<p>' . $plugin_tx['monorder']['successfully_deleted'] . '</p>';
        } else {
            e('cntdelete', 'file', $filename);
        }
    }
    $o .= Monorder_fileList();
    return $o;
}

function Monorder_save()
{
    global $plugin_tx, $_Monorder_tag;

    $o = '';
    $_Monorder_tag = $_GET['monorder_file'];
    if (isset($_POST['monorder_free'])) {
        $free = stsl($_POST['monorder_free']);
        if (Monorder_write($free)) {
            $o .= '<p>' . $plugin_tx['monorder']['successfully_saved'] . '</p>';
        } else {
            e('cntwriteto', 'file', Monorder_filename());
        }
    }
    $o .= Monorder_form();
    return $o;
}

if (isset($monorder) && $monorder == 'true') {
    $o .= print_plugin_admin('off');
    switch ($admin) {
    case '':
        switch ($action) {
        case 'plugin_text':
            $o .= Monorder_form();
            break;
        case 'plugin_textsave':
            $o .= Monorder_save();
            break;
        case 'new_event':
            $o .= Monorder_newEvent();
            break;
        case 'delete_event':
            $o .= Monorder_deleteEvent();
            break;
        default:
            $o .= Monorder_fileList();
        }
        break;
    default:
        $o .= plugin_admin_common($action, $admin, $plugin);
    }
}
