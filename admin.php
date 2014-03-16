<?php

/**
 * Administration of Monorder_XH.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Monorder
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Monorder_XH
 */

/*
 * Prevent direct access.
 */
if (!defined('CMSIMPLE_XH_VERSION')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

/**
 * Returns a list item of the file list.
 *
 * @param string $file A file name.
 *
 * @return string (X)HTML.
 *
 * @global string The script name.
 * @global array  The localization of the plugins.
 */
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
    <form class="monorder_delete" action="$action" method="post"
            onsubmit="$onsubmit">
        <input type="hidden" name="monorder_file" value="$file">
        <button>$ptx[label_delete]</button>
    </form>
</li>
EOT;
}

/**
 * Returns the file list view.
 *
 * @return (X)HTML.
 *
 * @global string         The script name.
 * @global array          The localization of the plugins.
 * @global Monorder_Model The model object.
 */
function Monorder_fileList()
{
    global $sn, $plugin_tx, $_Monorder_model;

    $ptx = $plugin_tx['monorder'];
    $action = $sn . '?monorder';
    $files = array_keys($_Monorder_model->items());
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

/**
 * Returns a file edit form.
 *
 * @return string (X)HTML.
 *
 * @global string         The script name.
 * @global array          The localization of the plugins.
 * @global string         The current monorder tag.
 * @global Monorder_Model The model object.
 */
function Monorder_form()
{
    global $sn, $plugin_tx, $_Monorder_tag, $_Monorder_model;

    $ptx = $plugin_tx['monorder'];
    $file = $_GET['monorder_file'];
    $action = $sn . '?monorder&admin=&action=plugin_textsave&amp;monorder_file='
        . $file;
    $_Monorder_tag = $file;
    $free = $_Monorder_model->availableAmountOf($file);
    return <<<EOT
<h4>$file</h4>
<form action="$action" method="post">
    <p>$ptx[free]</p>
    <input type="text" name="monorder_free" value="$free">
    <button>$ptx[save]</button>
</form>
EOT;
}

/**
 * Creates a new event file and returns the file list view.
 *
 * @return string (X)HTML.
 *
 * @global array          The localization of the plugins.
 * @global string         The current monorder tag.
 * @global Monorder_Model The model object.
 */
function Monorder_newEvent()
{
    global $plugin_tx, $_Monorder_tag, $_Monorder_model;

    $o = '';
    if (isset($_POST['monorder_file'])) {
        $_Monorder_tag = $_POST['monorder_file'];
        try {
            $_Monorder_model->setItemAmount($_Monorder_tag, 0);
            $o .= '<p>' . $plugin_tx['monorder']['successfully_saved'] . '</p>';
        } catch (Exception $x) {
            e('cntwriteto', 'file', $_Monorder_model->filename());
        }
    }
    $o .= Monorder_fileList();
    return $o;
}

/**
 * Deletes an event file and returns the file list view.
 *
 * @return string (X)HTML.
 *
 * @global array          The localization of the plugins.
 * @global string         The current monorder tag.
 * @global Monorder_Model The model object.
 */
function Monorder_deleteEvent()
{
    global $plugin_tx, $_Monorder_tag, $_Monorder_model;

    $o = '';
    if (isset($_POST['monorder_file'])) {
        $_Monorder_tag = $_POST['monorder_file'];
        try {
            $_Monorder_model->removeItem($_Monorder_tag);
            $o .= '<p>' . $plugin_tx['monorder']['successfully_deleted'] . '</p>';
        } catch (Exception $x) {
            e('cntdelete', 'file', $filename);
        }
    }
    $o .= Monorder_fileList();
    return $o;
}

/**
 * Saves an event file and return the file list view.
 *
 * @return string (X)HTML.
 *
 * @global array          The localization of the plugins.
 * @global string         The current monorder tag.
 * @global Monorder_Model The model object.
 */
function Monorder_save()
{
    global $plugin_tx, $_Monorder_tag, $_Monorder_model;

    $o = '';
    $_Monorder_tag = $_GET['monorder_file'];
    if (isset($_POST['monorder_free'])) {
        $free = stsl($_POST['monorder_free']);
        try {
            $_Monorder_model->setItemAmount($_Monorder_tag, $free);
            $o .= '<p>' . $plugin_tx['monorder']['successfully_saved'] . '</p>';
        } catch (Exception $ex) {
            e('cntwriteto', 'file', $_Monorder_model->filename());
        }
    }
    $o .= Monorder_form();
    return $o;
}

/*
 * Handle the administration.
 */
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

?>
