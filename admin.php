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
 * Creates a new event file and returns the file list view.
 *
 * @return string (X)HTML.
 *
 * @global array          The localization of the plugins.
 * @global string         The current monorder tag.
 * @global Monorder_Model The model object.
 * @global Monorder_Views The views object.
 */
function Monorder_newEvent()
{
    global $plugin_tx, $_Monorder_tag, $_Monorder_model, $_Monorder_views;

    $o = '';
    if (isset($_POST['monorder_item'])) {
        $_Monorder_tag = $_POST['monorder_item'];
        try {
            $_Monorder_model->setItemAmount($_Monorder_tag, 0);
            $o .= '<p>' . $plugin_tx['monorder']['successfully_saved'] . '</p>';
        } catch (Exception $x) {
            e('cntwriteto', 'file', $_Monorder_model->filename());
        }
    }
    $o .= $_Monorder_views->itemList();
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
 * @global Monorder_Views The views object.
 */
function Monorder_deleteEvent()
{
    global $plugin_tx, $_Monorder_tag, $_Monorder_model, $_Monorder_views;

    $o = '';
    if (isset($_POST['monorder_item'])) {
        $_Monorder_tag = $_POST['monorder_item'];
        try {
            $_Monorder_model->removeItem($_Monorder_tag);
            $o .= '<p>' . $plugin_tx['monorder']['successfully_deleted'] . '</p>';
        } catch (Exception $x) {
            e('cntdelete', 'file', $filename);
        }
    }
    $o .= $_Monorder_views->itemList();
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
 * @global Monorder_Views The views object.
 */
function Monorder_save()
{
    global $plugin_tx, $_Monorder_tag, $_Monorder_model, $_Monorder_views;

    $o = '';
    $_Monorder_tag = $_GET['monorder_item'];
    if (isset($_POST['monorder_free'])) {
        $free = stsl($_POST['monorder_free']);
        try {
            $_Monorder_model->setItemAmount($_Monorder_tag, $free);
            $o .= '<p>' . $plugin_tx['monorder']['successfully_saved'] . '</p>';
        } catch (Exception $ex) {
            e('cntwriteto', 'file', $_Monorder_model->filename());
        }
    }
    $o .= $_Monorder_views->itemForm();
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
            $o .= $_Monorder_views->itemForm();
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
            $o .= $_Monorder_views->itemList();
        }
        break;
    default:
        $o .= plugin_admin_common($action, $admin, $plugin);
    }
}

?>
