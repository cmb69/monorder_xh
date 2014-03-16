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
 * The model class.
 */
require_once $pth['folder']['plugin_classes'] . 'Model.php';

/**
 * The views class.
 */
require_once $pth['folder']['plugin_classes'] . 'Views.php';

/**
 * The model object.
 *
 * @var Monorder_Model
 */
$_Monorder_model = new Monorder_Model();

/**
 * The views object.
 *
 * @var Monorder_Views
 */
$_Monorder_views = new Monorder_Views($_Monorder_model);

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
 * Returns an available items view.
 *
 * @param string $tag A tag name.
 *
 * @return string (X)HTML.
 *
 * @global Monorder_Views The views object.
 */
function monorderfree($tag)
{
    global $_Monorder_views;

    return $_Monorder_views->inventory($tag);
}

/**
 * Returns a monorder form.
 *
 * @param string $formName The name of an Advancedform_XH form.
 * @param string $tag      A monorder tag name.
 *
 * @return string (X)HTML.
 *
 * @global array          The paths of system files and folders.
 * @global array          The localization of the plugins.
 * @global string         The current monorder tag.
 * @global Monorder_Model The model object.
 * @global Monorder_Views The views object.
 */
function monorderform($formName, $tag)
{
    global $pth, $plugin_tx, $_Monorder_tag, $_Monorder_model, $_Monorder_views;

    $ptx = $plugin_tx['monorder'];
    if (!preg_match('/^[a-z0-9-]+$/', $tag)) {
        return sprintf($ptx['invalid_tag'], $tag);
    }
    $_Monorder_tag = $tag;
    if (($free1 = $_Monorder_model->availableAmountOf($tag)) > 0) {
        include_once $pth['folder']['plugins'] . 'monorder/advancedform.php';
        $o = advancedform($formName);
        $_Monorder_model->clearCache();
        $free2 = $_Monorder_model->availableAmountOf($tag);
        if ($free2 == $free1) {
            $o = $_Monorder_views->inventory($tag) . $o;
        }
        return $o;
    } else {
        return $ptx['booked_out'];
    }
}

?>
