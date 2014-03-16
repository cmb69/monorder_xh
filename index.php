<?php

/**
 * Front-end functionality of Monorder_XH.
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
 * The current monorder tag.
 *
 * @var string
 */
$_Monorder_tag = null;

/**
 * The current monorder stream.
 *
 * @var resource
 */
$_Monorder_stream = null;

/**
 * Returns the number.
 *
 * @param int $number An amount.
 *
 * @return string
 */
function Monorder_numberSuffix($number)
{
    if ($number == 1) {
        $result = 'singular';
    } elseif ($number >= 2 && $number <= 4) {
        $result = 'paucal';
    } else {
        $result = 'plural';
    }
    return $result;
}

/**
 * Returns the path of the data folder.
 *
 * @return string
 *
 * @global array The paths of system files and folders.
 */
function Monorder_dataFolder()
{
    global $pth;

    return $pth['folder']['plugins'] . 'monorder/data/';
}

/**
 * Returns the path of the current data file.
 *
 * @return string
 *
 * @global string The current monorder tag.
 */
function Monorder_filename()
{
    global $_Monorder_tag;

    $filename = Monorder_dataFolder() . $_Monorder_tag . '.txt';
    return $filename;
}

/**
 * Returns the amount of available items for the current tag.
 *
 * @param bool $keepOpen Whether to keep the file handle open.
 *
 * @return int
 *
 * @global resource The current monorder stream.
 */
function Monorder_free($keepOpen = false)
{
    global $_Monorder_stream;

    $filename = Monorder_filename();
    if (is_readable($filename)
        && ($_Monorder_stream = fopen($filename, 'r')) !== false
    ) {
        flock($_Monorder_stream, $keepOpen ? LOCK_EX : LOCK_SH);
        $contents = stream_get_contents($_Monorder_stream);
        if (!$keepOpen) {
            flock($_Monorder_stream, LOCK_UN);
            fclose($_Monorder_stream);
            $_Monorder_stream = null;
        }
        $free = (int) trim($contents);
    } else {
        $free = 0;
    }
    return $free;
}

/**
 * Stores the amount of available items for the current tag.
 *
 * @param int $free An amount.
 *
 * @return void
 *
 * @global resource The current monorder stream.
 */
function Monorder_write($free)
{
    global $_Monorder_stream;

    $result = false;
    $filename = Monorder_filename();
    if (!isset($_Monorder_stream)
        && ($_Monorder_stream = fopen($filename, 'a+')) !== false
    ) {
        flock($_Monorder_stream, LOCK_EX);
        fseek($_Monorder_stream, 0);
        ftruncate($_Monorder_stream, 0);
    }
    if ($_Monorder_stream) {
        $result = (bool) fwrite($_Monorder_stream, $free);
        flock($_Monorder_stream, LOCK_UN);
        fclose($_Monorder_stream);
        $_Monorder_stream = null;
    }
    return $result;
}

/**
 * Returns an available items view.
 *
 * @param string $tag A tag name.
 *
 * @return string (X)HTML.
 *
 * @global array The localization of the plugins.
 * @global string The current monorder tag.
 */
function monorderfree($tag)
{
    global $plugin_tx, $_Monorder_tag;

    $_Monorder_tag = $tag;
    $free = Monorder_free();
    if ($free > 0) {
        $suffix = Monorder_numberSuffix($free);
        $result = sprintf($plugin_tx['monorder']["free_$suffix"], $free);
    } else {
        $result = $plugin_tx['monorder']['booked_out'];
    }
    return $result;
}

/**
 * Returns a monorder form.
 *
 * @param string $formName The name of an Advancedform_XH form.
 * @param string $tag      A monorder tag name.
 *
 * @return string (X)HTML.
 *
 * @global array  The paths of system files and folders.
 * @global array  The localization of the plugins.
 * @global string The current monorder tag.
 */
function monorderform($formName, $tag)
{
    global $pth, $plugin_tx, $_Monorder_tag;

    $ptx = $plugin_tx['monorder'];
    if (!preg_match('/^[a-z0-9-]+$/', $tag)) {
        return sprintf($ptx['invalid_tag'], $tag);
    }
    $_Monorder_tag = $tag;
    if (($free1 = Monorder_free()) > 0) {
        include_once $pth['folder']['plugins'] . 'monorder/advancedform.php';
        $o = advancedform($formName);
        $free2 = monorder_free();
        if ($free2 == $free1) {
            $o = monorderfree($tag) . $o;
        }
        return $o;
    } else {
        return $ptx['booked_out'];
    }
}

?>
