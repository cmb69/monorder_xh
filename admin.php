<?php

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

function Booking_files()
{
    $result = array();
    $dir = opendir(Booking_dataFolder());
    if ($dir) {
        while (($entry = readdir($dir)) !== false) {
            if ($entry[0] != '.' && pathinfo($entry, PATHINFO_EXTENSION) == 'txt') {
                $result[] = basename($entry, '.txt');
            }
        }
    }
    return $result;
}

function Booking_fileListItem($file)
{
    global $sn, $plugin_tx;

    $ptx = $plugin_tx['booking'];
    $href = $sn . '?booking&amp;admin=&amp;action=plugin_text&amp;booking_file='
        . $file;
    $action = $sn . '?booking&amp;admin=&amp;action=delete_event';
    $onsubmit = 'return window.confirm(\'' . $ptx['confirm_delete'] . '\')';
    return <<<EOT
<li>
    <a href="$href">$file</a>
    <form class="booking_delete" action="$action" method="post" onsubmit="$onsubmit">
        <input type="hidden" name="booking_file" value="$file">
        <button>$ptx[label_delete]</button>
    </form>
</li>
EOT;
}

function Booking_fileList()
{
    global $sn, $plugin_tx;

    $ptx = $plugin_tx['booking'];
    $action = $sn . '?booking';
    $files = Booking_files();
    $listItems = array();
    foreach ($files as $file) {
        $listItems[] = Booking_fileListItem($file);
    }
    $listItems = implode('', $listItems);
    return <<<EOT
<h4>$ptx[events]</h4>
<ul>
$listItems
<li><form action="$action" method="post">
<input type="hidden" name="admin" value="">
<input type="hidden" name="action" value="new_event">
<input type="text" name="booking_file">
<button>$ptx[new_event]</button>
</form></li>
</ul>
EOT;
}

function Booking_form()
{
    global $sn, $plugin_tx, $_Booking_tag;

    $ptx = $plugin_tx['booking'];
    $file = $_GET['booking_file'];
    $action = $sn . '?booking&admin=&action=plugin_textsave&amp;booking_file=' . $file;
    $_Booking_tag = $file;
    $free = Booking_free();
    return <<<EOT
<h4>$file</h4>
<form action="$action" method="post">
    <p>$ptx[free]</p>
    <input type="text" name="booking_free" value="$free">
    <button>$ptx[save]</button>
</form>
EOT;
}

function Booking_newEvent()
{
    global $plugin_tx, $_Booking_tag;

    $o = '';
    if (isset($_POST['booking_file'])) {
        $_Booking_tag = $_POST['booking_file'];
        if (Booking_write(0)) {
            $o .= '<p>' . $plugin_tx['booking']['successfully_saved'] . '</p>';
        } else {
            e('cntwriteto', 'file', Booking_filename());
        }
    }
    $o .= Booking_fileList();
    return $o;
}

function Booking_deleteEvent()
{
    global $plugin_tx, $_Booking_tag;

    $o = '';
    if (isset($_POST['booking_file'])) {
        $_Booking_tag = $_POST['booking_file'];
        $filename = Booking_filename();
        if (unlink($filename)) {
            $o .= '<p>' . $plugin_tx['booking']['successfully_deleted'] . '</p>';
        } else {
            e('cntdelete', 'file', $filename);
        }
    }
    $o .= Booking_fileList();
    return $o;
}

function Booking_save()
{
    global $plugin_tx, $_Booking_tag;

    $o = '';
    $_Booking_tag = $_GET['booking_file'];
    if (isset($_POST['booking_free'])) {
        $free = stsl($_POST['booking_free']);
        if (Booking_write($free)) {
            $o .= '<p>' . $plugin_tx['booking']['successfully_saved'] . '</p>';
        } else {
            e('cntwriteto', 'file', Booking_filename());
        }
    }
    $o .= Booking_form();
    return $o;
}

if (isset($booking) && $booking == 'true') {
    $o .= print_plugin_admin('off');
    switch ($admin) {
    case '':
        switch ($action) {
        case 'plugin_text':
            $o .= Booking_form();
            break;
        case 'plugin_textsave':
            $o .= Booking_save();
            break;
        case 'new_event':
            $o .= Booking_newEvent();
            break;
        case 'delete_event':
            $o .= Booking_deleteEvent();
            break;
        default:
            $o .= Booking_fileList();
        }
        break;
    default:
        $o .= plugin_admin_common($action, $admin, $plugin);
    }
}
