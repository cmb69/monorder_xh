<?php

/**
 * Main of Monorder_XH.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Monorder
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2019 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Monorder_XH
 */

/*
 * Prevent direct access and usage from unsupported CMSimple_XH versions.
 */
if (!defined('CMSIMPLE_XH_VERSION')
    || strpos(CMSIMPLE_XH_VERSION, 'CMSimple_XH') !== 0
    || version_compare(CMSIMPLE_XH_VERSION, 'CMSimple_XH 1.5.4', 'lt')
) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: text/plain; charset=UTF-8');
    die(<<<EOT
Monorder_XH detected an unsupported CMSimple_XH version.
Uninstall Monorder_XH or upgrade to a supported CMSimple_XH version!
EOT
    );
}

/**
 * The exception classes.
 */
require_once $pth['folder']['plugin_classes'] . 'Exceptions.php';

/**
 * The model class.
 */
require_once $pth['folder']['plugin_classes'] . 'Model.php';

/**
 * The views class.
 */
require_once $pth['folder']['plugin_classes'] . 'Views.php';

/**
 * The controller class.
 */
require_once $pth['folder']['plugin_classes'] . 'Controller.php';

/**
 * The plugin version.
 */
define('MONORDER_VERSION', '1.0');

try {
    /**
     * The controller object.
     *
     * @var Monorder_Controller
     *
     * @todo Clean that up.
     */
    $_Monorder = new Monorder_Controller();
} catch (Monorder_Exception $ex) {
    die($ex->getMessage());
}

/**
 * Returns an inventory view.
 *
 * @param string $itemName An item name.
 *
 * @return string (X)HTML.
 *
 * @global Monorder_Controller The controller object.
 */
function Monorder_inventory($itemName)
{
    global $_Monorder;

    return $_Monorder->inventory($itemName);
}

/**
 * Returns an order form.
 *
 * @param string $formName A name of an Advancedform_XH form.
 * @param string $itemName An item name.
 *
 * @return string (X)HTML.
 */
function Monorder_form($formName, $itemName)
{
    global $_Monorder;

    return $_Monorder->orderForm($formName, $itemName);
}

?>
